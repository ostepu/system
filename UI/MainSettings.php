<?php
/**
 * @file MainSettings.php
 * Constructs the page that is used to create and delete users and
 * to create new courses.
 *
 * @author Felix Schmidt
 * @author Florian Lücke
 * @author Ralf Busch
 *
 * @todo POST Request to logic instead of DB
 * @todo create a navigation bar for super admins
 * @todo unset $_POST on success
 */
ob_start();

include_once dirname(__FILE__) . '/include/Boilerplate.php';
include_once dirname(__FILE__) . '/../Assistants/Structures.php';
include_once dirname(__FILE__) . '/../Assistants/vendor/Validation/Validation.php';

global $globalUserData;
Authentication::checkRights(PRIVILEGE_LEVEL::SUPER_ADMIN, null, $uid, $globalUserData);

$langTemplate='MainSettings_Controller';Language::loadLanguageFile('de', $langTemplate, 'json', dirname(__FILE__).'/');

// load Plugins data from LogicController
$URI = $serverURI . "/logic/LExtension/link/extension";
$temp = http_get($URI, true);
$plugins_data = json_decode($temp, true);

$postValidation = Validation::open($_POST, array('preRules'=>array('sanitize')))
  ->addSet('action',
           ['set_default'=>'noAction',
            'satisfy_in_list'=>['noAction', 'CreateCourse', 'SetAdmin', 'CreateUser', 'DeleteUser', ''],
            'on_error'=>['type'=>'error',
                         'text'=>Language::Get('main','invalidAction', $langTemplate)]]);
$postResults = $postValidation->validate();
$notifications = array_merge($notifications,$postValidation->getPrintableNotifications('MakeNotification'));
$postValidation->resetNotifications()->resetErrors();

if ($postValidation->isValid() && $postResults['action'] !== 'noAction') {
    // creates a new course
    if ($postResults['action'] === 'CreateCourse') {
        $postCreateCourseValidation = Validation::open($_POST, array('preRules'=>array('sanitize')))
          ->addSet('courseName',
                   ['satisfy_exists',
                    'valid_alpha_numeric',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidCourseName', $langTemplate)]])
          ->addSet('semester',
                   ['satisfy_exists',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidSemester', $langTemplate)]])
          ->addSet('defaultGroupSize',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidGroupSize', $langTemplate)]])
          ->addSet('defaultGroupSize',
                   ['valid_integer',
                    'satisfy_min_numeric' => 0,
                    'to_integer',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidGroupSize', $langTemplate)]])
          ->addSet('exerciseTypes',
                   ['is_array',
                    'perform_this_array'=>[[['key_all'],
                                       ['valid_identifier']]],
                    'set_default'=>array(),
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidExerciseType', $langTemplate)]])
          ->addSet('plugins',
                   ['is_array',
                    'set_default'=>null,
                    'perform_this_array'=>[['key_all'],
                                      ['valid_identifier']],
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidExtensionId', $langTemplate)]]);

        $foundValues = $postCreateCourseValidation->validate();
        $notifications = array_merge($notifications,$postCreateCourseValidation->getPrintableNotifications('MakeNotification'));
        $postCreateCourseValidation->resetNotifications()->resetErrors();

        if($postCreateCourseValidation->isValid()) {
            // bool which is true if any error occured
            $RequestError = false;

            // extracts the php POST data
            $courseName = $foundValues['courseName'];
            $semester = $foundValues['semester'];
            $defaultGroupSize = $foundValues['defaultGroupSize'];
            $plugins = $foundValues['plugins'];
            $exerciseTypes = $foundValues['exerciseTypes'];

            // creates a new course
            $newCourse = Course::createCourse(null, $courseName, $semester, $defaultGroupSize);
            $newCourseSettings = Course::encodeCourse($newCourse);
            $URI = $logicURI . '/course';
            $newCourse = http_post_data($URI, $newCourseSettings, true, $messageNewCourse);

            if ($messageNewCourse !== 201){
                $RequestError = true;
            }

            if ($RequestError === false){
                // extracts the id of the new course
                $newCourse = json_decode($newCourse, true);
                $newCourseId = $newCourse['id'];
            }

            // creates a new approvalCondition for every selected exerciseType
            if ($RequestError === false && isset($exerciseTypes) && !empty($exerciseTypes)){
                foreach ($exerciseTypes as $exerciseType) {
                    $newApprovalCondition = ApprovalCondition::createApprovalCondition(null,
                                                                                       $newCourseId,
                                                                                       $exerciseType,
                                                                                       0);
                    $newApprovalConditionSettings = ApprovalCondition::encodeApprovalCondition($newApprovalCondition);
                    $URI = $databaseURI . '/approvalcondition';
                    http_post_data($URI, $newApprovalConditionSettings, true, $messageNewAc);

                    if ($messageNewAc !== 201) {
                        $RequestError = true;
                        break;
                    }

                }
            }

            // create Plugins
            if ($RequestError === false && isset($plugins) && !empty($plugins)){
                foreach ($plugins as $plugin) {
                    $URI = $serverURI . "/logic/LExtension/link/course/{$newCourseId}/extension/{$plugin}";
                    http_post_data($URI, '', true, $messageNewAc);
                    if ($messageNewAc !== 201) {
                        $RequestError = true;
                        break;
                    }
                }
            }

            // creates a notification depending on RequestError
            if ($RequestError === false) {
                $notifications[] = MakeNotification('success',
                                                    Language::Get('main','successCreateCourse', $langTemplate));
            } else {
                $notifications[] = MakeNotification('error',
                                                    Language::Get('main','errorCreateCourse', $langTemplate));
            }
        }
    }

    if ($postResults['action'] === 'SetAdmin') {
        $postSetAdminValidation = Validation::open($_POST, array('preRules'=>array('sanitize')))
          ->addSet('courseID',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_identifier',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidCourseId', $langTemplate)]])
          ->addSet('userName',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_userName',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidUserName', $langTemplate)]]);

        $foundValues = $postSetAdminValidation->validate();
        $notifications = array_merge($notifications,$postSetAdminValidation->getPrintableNotifications('MakeNotification'));
        $postSetAdminValidation->resetNotifications()->resetErrors();

        if ($postSetAdminValidation->isValid()){
            // clean Input
            $courseID = $foundValues['courseID'];
            $userName = $foundValues['userName'];

            // extracts the userID
            $URI = $databaseURI . "/user/user/{$userName}";
            $user_data = http_get($URI, true);
            $user_data = json_decode($user_data, true);

            // sets admin rights for the user
            if (empty($user_data)) {
                $notifications[] = MakeNotification('error', Language::Get('main','invalidUserId', $langTemplate));
            } else {
                $userID = $user_data['id'];
                $status = 3;

                $data = User::encodeUser(User::createCourseStatus($userID, $courseID, $status));
                $url = $databaseURI . '/coursestatus';
                http_post_data($url, $data, true, $message);

                if ($message !== 201) {
                    $data = User::encodeUser(User::createCourseStatus($userID, $courseID, $status));
                    $url = $databaseURI . "/coursestatus/course/{$courseID}/user/{$userID}";
                    http_put_data($url, $data, true, $message);

                    if ($message === 201) {
                        $notifications[] = MakeNotification('success', Language::Get('main','successSetAdmin', $langTemplate));
                    } else {
                        $notifications[] = MakeNotification('error', Language::Get('main','errorSetAdmin', $langTemplate));
                    }
                } else {
                    $notifications[] = MakeNotification('success', Language::Get('main','successSetAdmin', $langTemplate));
                }
            }
        }
    }

    // creates a new user
    if ($postResults['action'] === 'CreateUser') {
        $postCreateUserValidation = Validation::open($_POST, array('preRules'=>array('sanitize')))
          ->addSet('lastName',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_alpha_numeric',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidLastName', $langTemplate)]])
          ->addSet('firstName',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_alpha_numeric',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidFirstName', $langTemplate)]])
          ->addSet('userName',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_userName',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidUserName', $langTemplate)]])
          ->addSet('email',
                   ['valid_email',
                   'set_default'=>null,
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidMail', $langTemplate)]])
          ->addSet('password',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_userName',
                         'satisfy_min_len'=>6,
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidPassword', $langTemplate)]])
          ->addSet('passwordRepeat',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_userName',
                    'satisfy_min_len'=>6,
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidPasswordRepeat', $langTemplate)]])
          ->addSet('passwordRepeat',
                   array('satisfy_equals_field'=>'password',
                         'on_error'=>array('type'=>'error',
                                           'text'=>Language::Get('main','differentPasswords', $langTemplate))));

        $foundValues = $postCreateUserValidation->validate();
        $notifications = array_merge($notifications,$postCreateUserValidation->getPrintableNotifications('MakeNotification'));
        $postCreateUserValidation->resetNotifications()->resetErrors();

        if($postCreateUserValidation->isValid()) {
            $salt = $auth->generateSalt();
            $passwordHash = $auth->hashPassword($foundValues['password'], $salt);

            $newUser = User::createUser(null,
                                        $foundValues['userName'],
                                        $foundValues['email'],
                                        $foundValues['firstName'],
                                        $foundValues['lastName'],
                                        null,
                                        1,
                                        $passwordHash,
                                        $salt,
                                        0);

            $newUserSettings = User::encodeUser($newUser);

            $URI = $databaseURI . '/user';
            $answer=http_post_data($URI, $newUserSettings, true, $message);

            if ($message === 201) {
                $user = User::decodeUser($answer);
                if ($user->getStatus() == '201'){
                    $notifications[] = MakeNotification('success', Language::Get('main','successCreateUser', $langTemplate));
                } else
                    $notifications[] = MakeNotification('error', Language::Get('main','errorCreateUser', $langTemplate));
            } else {
                $notifications[] = MakeNotification('error', Language::Get('main','errorCreateUser', $langTemplate));
            }
        } else {
            $notifications = $notifications + $postValidation->notifications;
        }
    }

    // deletes an user
    if ($postResults['action'] === 'DeleteUser') {
        $postDeleteUserValidation = Validation::open($_POST, array('preRules'=>array('sanitize')))
          ->addSet('userName',
                   ['satisfy_exists',
                    'satisfy_not_empty',
                    'valid_userName',
                    'on_error'=>['type'=>'error',
                                 'text'=>Language::Get('main','invalidUserName', $langTemplate)]]);

        $foundValues = $postDeleteUserValidation->validate();
        $notifications = array_merge($notifications,$postDeleteUserValidation->getPrintableNotifications('MakeNotification'));
        $postDeleteUserValidation->resetNotifications()->resetErrors();

        if($postDeleteUserValidation->isValid()) {
            // clean Input
            $userName = $foundValues['userName'];

            // extracts the userID
            $URI = $databaseURI . "/user/user/{$userName}";
            $user_data = http_get($URI, true);
            $user_data = json_decode($user_data, true);

            if (empty($user_data)) {
                $notifications[] = MakeNotification('error', Language::Get('main','invalidUserId', $langTemplate));
            } else {
                $userID = $user_data['id'];

                // deletes the user
                $url = $databaseURI . "/user/{$userID}";
                http_delete($url, true, $message);

                if ($message === 201) {
                    $notifications[] = MakeNotification('success',Language::Get('main','successDeleteUser', $langTemplate));
                } else {
                    $notifications[] = MakeNotification('error', Language::Get('main','errorDeleteUser', $langTemplate));
                }
            }
        }
    }
}

// load mainSettings data from GetSite
$databaseURI = $getSiteURI . "/mainsettings/user/{$uid}";
$mainSettings_data = http_get($databaseURI, true);
$mainSettings_data = json_decode($mainSettings_data, true);

$mainSettings_data['plugins'] = $plugins_data;

$user_course_data = $mainSettings_data['user'];

$menu = MakeNavigationElement($user_course_data,
                              PRIVILEGE_LEVEL::SUPER_ADMIN,true);

// construct a new header
$h = Template::WithTemplateFile('include/Header/Header.template.html');
$h->bind($user_course_data);
$h->bind(array('name' => Language::Get('main','settings', $langTemplate),
               'backTitle' => Language::Get('main','courses', $langTemplate),
               'backURL' => 'index.php',
               'notificationElements' => $notifications,
               'navigationElement' => $menu));

// construct a content element for creating new courses
$createCourse = Template::WithTemplateFile('include/MainSettings/CreateCourse.template.html');
$createCourse->bind($mainSettings_data);
if (count($notifications) > 0) {
    ///$createCourse->bind($_POST); /// !!! das geht so nicht ??? ///
}

// construct a content element for setting admins
$setAdmin = Template::WithTemplateFile('include/MainSettings/SetAdmin.template.html');
$setAdmin->bind($mainSettings_data);

// construct a content element for creating new users
$createUser = Template::WithTemplateFile('include/MainSettings/CreateUser.template.html');
if (count($notifications) > 0) {
    ///$createUser->bind($_POST); /// !!! das geht so nicht ??? ///
}

// construct a content element for deleting users
$deleteUser = Template::WithTemplateFile('include/MainSettings/DeleteUser.template.html');

// wrap all the elements in some HTML and show them on the page
$w = new HTMLWrapper($h, $createCourse, $setAdmin, $createUser, $deleteUser);
$w->defineForm(basename(__FILE__), false, $createCourse);
$w->defineForm(basename(__FILE__), false, $setAdmin);
$w->defineForm(basename(__FILE__), false, $createUser);
$w->defineForm(basename(__FILE__), false, $deleteUser);
$w->set_config_file('include/configs/config_default.json');
$w->show();

ob_end_flush();