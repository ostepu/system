<?php
/**
 * @file GetSite.php
 *
 * contains the LgetSite class.
 */
require '../../Assistants/Slim/Slim.php';
include '../../Assistants/Request.php';
include_once '../../Assistants/CConfig.php';
include_once '../../Assistants/Logger.php';
include_once '../../Assistants/Structures.php';

\Slim\Slim::registerAutoloader();

/**
 * This class gives all information needed to print a Site
 */
class LgetSite
{
    /**
     * Values needed for conversation with other components
     */
    private $_conf=null;

    private static $_prefix = "getSite";

    public static function getPrefix()
    {
        return LgetSite::$_prefix;
    }
    public static function setPrefix($value)
    {
        LgetSite::$_prefix = $value;
    }


    /**
     * Address of the logic controller.
     */
    private $lURL = "";

    private $flag = 0;

    public function __construct($conf)
    {
        // Initialize Slim
        $this->app = new \Slim\Slim();
        $this->app->response->headers->set('Content-Type', 'application/json');

        // Set the logic controller URL
        $this->_conf = $conf;

        $this->query = CConfig::getLink($conf->getLinks(),"controller");
        $this->lURL = $this->query->getAddress();


        //GET TutorAssignmentSiteInfo
        $this->app->get('/tutorassign/user/:userid/course/:courseid/exercisesheet/:sheetid(/)',
                        array($this, 'tutorAssignmentSiteInfo'));

        //GET StudentSiteInfo
        $this->app->get('/student/user/:userid/course/:courseid(/)',
                        array($this, 'studentSiteInfo'));

        //GET AccountSettings
        $this->app->get('/accountsettings/user/:userid(/)',
                        array($this, 'accountsettings'));

        //GET CreateSheet
        $this->app->get('/createsheet/user/:userid/course/:courseid(/)',
                        array($this, 'createSheetInfo'));

        //GET Index
        $this->app->get('/index/user/:userid(/)',
                        array($this, 'userWithAllCourses'));

        //GET CourseManagement
        $this->app->get('/coursemanagement/user/:userid/course/:courseid(/)',
                        array($this, 'courseManagement'));

        //GET MainSettings
        $this->app->get('/mainsettings/user/:userid',
                        array($this, 'mainSettings'));

        //GET Upload
        $this->app->get('/upload/user/:userid/course/:courseid/exercisesheet/:sheetid(/)',
                        array($this, 'upload'));

        //GET TutorUpload
        $this->app->get('/tutorupload/user/:userid/course/:courseid/exercisesheet/:sheetid(/)',
                        array($this, 'upload'));

        //GET MarkingTool
        $this->app->get('/markingtool/user/:userid/course/:courseid/exercisesheet/:sheetid(/)',
                        array($this, 'markingTool'));
        //GET MarkingTool
        $this->app->get('/markingtool/user/:userid/course/:courseid/exercisesheet/:sheetid/tutor/:tutorid',
                        array($this, 'markingToolTutor'));
        //GET MarkingTool
        $this->app->get('/markingtool/user/:userid/course/:courseid/exercisesheet/:sheetid/status/:statusid',
                        array($this, 'markingToolStatus'));
        //GET MarkingTool
        $this->app->get('/markingtool/user/:userid/course/:courseid/exercisesheet/:sheetid/tutor/:tutorid/status/:statusid',
                        array($this, 'markingToolTutorStatus'));

        //GET UploadHistory
        $this->app->get('/uploadhistory/user/:userid/course/:courseid/exercisesheet/:sheetid/uploaduser/:uploaduserid(/)',
                        array($this, 'uploadHistory'));

        //GET UploadHistoryOptions
        $this->app->get('/uploadhistoryoptions/user/:userid/course/:courseid(/)',
                        array($this, 'uploadHistoryOptions'));

        //GET TutorSite
        $this->app->get('/tutor/user/:userid/course/:courseid(/)',
                        array($this, 'tutorDozentAdmin'));

        //GET AdminSite
        $this->app->get('/admin/user/:userid/course/:courseid(/)',
                        array($this, 'tutorDozentAdmin'));

        //GET DozentSite
        $this->app->get('/lecturer/user/:userid/course/:courseid(/)',
                        array($this, 'tutorDozentAdmin'));

        //GET GroupSite
        $this->app->get('/group/user/:userid/course/:courseid/exercisesheet/:sheetid(/)',
                        array($this, 'groupSite'));

        //GET Condition
        $this->app->get('/condition/user/:userid/course/:courseid(/)',
                        array($this, 'checkCondition'));

        //run Slim
        $this->app->run();
    }

    public function tutorAssignmentSiteInfo($userid, $courseid, $sheetid)
    {
        $response = array();
        $assignedSubmissionIDs = array();

        // get tutors
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // get all users with status 1 (tutor)
        $URL = $this->lURL.'/DB/user/course/'.$courseid.'/status/1';
        $answer = Request::custom('GET', $URL, $header, $body);
        $tutors = json_decode($answer['content'], true);

        $response['tutorAssignments'] = array();

        if (!empty($tutors)) {

            foreach ($tutors as &$tutor) {
                unset($tutor['salt']);
                unset($tutor['password']);

                // create an empty marking for each tutor
                $response['tutorAssignments'][] = array('tutor' => $tutor, 'submissions' => array());
            }
        }

        // get markings
        $URL = $this->lURL.'/DB/marking/exercisesheet/'.$sheetid;
        $answer = Request::custom('GET', $URL, $header, $body);

        // assign submissions for the markings to the right tutor
        foreach (json_decode($answer['content'], true) as $marking ) {
            foreach ($response['tutorAssignments'] as &$tutorAssignment ) {
                if ($marking['tutorId'] == $tutorAssignment['tutor']['id']) {

                    // rename 'id' to 'submissionId'
                    $marking['submission']['submissionId'] = $marking['submission']['id'];
                    unset($marking['submission']['id']);

                    // remove unnecessary information
                    unset($marking['submission']['file']);
                    unset($marking['submission']['comment']);
                    unset($marking['submission']['accepted']);
                    unset($marking['submission']['date']);
                    unset($marking['submission']['flag']);
                    unset($marking['submission']['selectedForGroup']);
                    $tutorAssignment['submissions'][] = $marking['submission'];

                    // save ids of all assigned submission
                    $assignedSubmissionIDs[] = $marking['submission']['submissionId'];
                    break;
                }
            }
        }

        // Get SelectedSubmissions
        $URL = $this->lURL.'/DB/selectedsubmission/exercisesheet/'.$sheetid;
        $answer = Request::custom('GET', $URL, $header, $body);

        $virtualTutor = array('id' => null,
                              'userName' => "unassigned",
                              'firstName' => null,
                              'lastName' => null);

        $unassignedSubmissions = array();


        $submissions = json_decode($answer['content'], true);
        foreach ($submissions as &$submission) {
            if (!in_array($submission['submissionId'], $assignedSubmissionIDs)) {
                $submission['unassigned'] = true;
                $unassignedSubmissions[] = $submission;
            }
        }

        $newTutorAssignment = array('tutor' => $virtualTutor,
                                    'submissions' => $unassignedSubmissions);

        $response['tutorAssignments'][] = $newTutorAssignment;


        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    /**
     * Compiles data for the Student page.
     *
     * @todo include markingStatusName
     *
     * @author Florian Lücke
     */
    public function studentSiteInfo($userid, $courseid)
    {
        $response = array('sheets' => array(),
                          'user' => array());
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // load all Requests async
        $URL = $this->lURL . '/exercisesheet/course/' . $courseid . '/exercise';
        $handler1 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = $this->lURL . '/DB/submission/group/user/' . $userid . '/course/' . $courseid . '/selected';
        $handler2 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = $this->lURL . '/DB/marking/course/' . $courseid;
        $handler3 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = $this->lURL . '/DB/group/user/' . $userid;
        $handler4 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = $this->lURL . '/DB/exercisetype';
        $handler5 = Request_CreateRequest::createGet($URL, $header, $body);

        $multiRequestHandle = new Request_MultiRequest();
        $multiRequestHandle->addRequest($handler1);
        $multiRequestHandle->addRequest($handler2);
        $multiRequestHandle->addRequest($handler3);
        $multiRequestHandle->addRequest($handler4);
        $multiRequestHandle->addRequest($handler5);

        $answer = $multiRequestHandle->run();

        //Get neccessary data
        $sheets = json_decode($answer[0]['content'], true);
        $submissions = json_decode($answer[1]['content'], true);

        if (!isset($submissions)) {
            $submissions = array();
        }

        $markings = json_decode($answer[2]['content'], true);

        if (!isset($markings)) {
            $markings = array();
        }

        $groups = json_decode($answer[3]['content'], true);

        $possibleExerciseTypes = json_decode($answer[4]['content'], true);

        $markingStatus = Marking::getStatusDefinition();

        // order submissions by exercise
        $submissionsByExercise = array();
        foreach ($submissions as &$submission) {
            $exerciseId = $submission['exerciseId'];
            $submissionsByExercise[$exerciseId] = &$submission;
        }

        // add markings to the submissions
        foreach ($markings as &$marking) {
            $studentId = $marking['submission']['studentId'];
            $exerciseId = $marking['submission']['exerciseId'];

            if (isset($submissionsByExercise[$exerciseId])) {
                // only check submissions that have the same exercise id
                // as the marking (there should be 1 at most)
                $selectedSubmission = &$submissionsByExercise[$exerciseId];
                $selectedSubmissionStudentId = $selectedSubmission['studentId'];

                if ($selectedSubmissionStudentId == $studentId) {
                    // the student id of the selected submission and the student
                    // id of the marking match

                    // add marking status to the marking
                    $status = $marking['status'];
                    $marking['statusId'] = $status;

                    // add marking status name to the marking
                    $statusName = $markingStatus[$status]['longName'];
                    $marking['status'] = $statusName;

                    unset($marking['submission']);
                    $selectedSubmission['marking'] = $marking;
                }
            }
        }

        // order groups by sheet
        $groupsBySheet = array();
        foreach ($groups as $group) {
            if (isset($group['sheetId'])) {
                $groupsBySheet[$group['sheetId']] = $group;
            }
        }

        // oder exercise types by id
        $exerciseTypes = array();
        foreach ($possibleExerciseTypes as $exerciseType) {
            $exerciseTypes[$exerciseType['id']] = $exerciseType;
        }

        foreach ($sheets as &$sheet) {
            $sheetPoints = 0;
            $maxSheetPoints = 0;

            $hasAttachments = false;
            $hasMarkings = false;

            // add group to the sheet
            if (isset($groupsBySheet[$sheet['id']])) {
                $group = $groupsBySheet[$sheet['id']];
                $sheet['group'] = $group;
            } else {
                $sheet['group'] = array();
            }

            // prepare exercises
            foreach ($sheet['exercises'] as &$exercise) {
                $isBonus = $exercise['bonus'];
                $maxSheetPoints += $isBonus ? $exercise['maxPoints'] : 0;
                $exerciseID = $exercise['id'];

                // add submission to exercise
                if (isset($submissionsByExercise[$exerciseID])) {
                    $submission = &$submissionsByExercise[$exerciseID];

                    if (isset($submission['marking'])) {
                        $marking = $submission['marking'];

                        $sheetPoints += $marking['points'];

                        $hasMarkings = true;
                    }

                    $exercise['submission'] = $submission;
                }

                // add attachments to exercise
                if (count($exercise['attachments']) > 0) {
                    $exercise['attachment'] = $exercise['attachments'][0];
                    $hasAttachments = true;
                }

                unset($exercise['attachments']);

                // add type name to exercise
                $typeID = $exercise['type'];
                if (isset($exerciseTypes[$typeID])) {
                    $exercise['typeName'] = $exerciseTypes[$typeID]['name'];
                } else {
                    $exercise['typeName'] = "unknown type";
                }
            }

            $sheet['hasMarkings'] = $hasMarkings;
            $sheet['hasAttachments'] = $hasAttachments;
            $sheet['maxPoints'] = $maxSheetPoints;
            $sheet['points'] = $sheetPoints;
            if ($maxSheetPoints != 0) {
                $percentage = round($sheetPoints / $maxSheetPoints * 100, 2);
                $sheet['percentage'] = $percentage;
            } else {
                $sheet['percentage'] = 100;
            }
        }

        $this->flag = 1;

        $response['sheets'] = $sheets;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    public function userWithCourse($userid, $courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        $URL = $this->lURL.'/DB/coursestatus/course/'.$courseid.'/user/'.$userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $user = json_decode($answer['content'], true);

        $response = $user;

        if ($this->flag == 0){
            $this->app->response->setBody(json_encode($response));
        } else{
            $this->flag = 0;
            return $response;
        }
    }

    public function userWithAllCourses($userid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        $URL = $this->lURL.'/DB/user/user/'.$userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $user = json_decode($answer['content'], true);

        $response = array('id' =>  $user['id'],
                          'userName'=>  $user['userName'],
                          'firstName'=>  $user['firstName'],
                          'lastName'=>  $user['lastName'],
                          'flag'=>  $user['flag'],
                          'email'=>  $user['email'],
                          'courses'=>  array());

        foreach ($user['courses'] as $course) {
            $newCourse = array('status' => $course['status'],
                               'statusName' => $this->getStatusName($course['status']),
                               'course' => $course['course']);
            $response['courses'][] = $newCourse;
        }

        if ($this->flag == 0) {
            $this->app->response->setBody(json_encode($response));
        } else {
            $this->flag = 0;
            return $response;
        }
    }

    public function accountsettings($userid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        $URL = $this->lURL . '/DB/user/user/' . $userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $user = json_decode($answer['content'], true);

        $this->app->response->setBody(json_encode($user));
    }

    public function userWithCourseAndHash($userid, $courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        $URL = $this->lURL.'/DB/coursestatus/course/'.$courseid.'/user/'.$userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $user = json_decode($answer['content'], true);

        $URL = $this->lURL.'/DB/user/user/'.$userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $response['user'] = json_decode($answer['content'], true);

        unset($response['user']['courses']);

        foreach ($user['courses'] as $course) {
            $newCourse = array('status' => $course['status'],
                               'statusName' => $this->getStatusName($course['status']),
                               'course' => $course['course']);
            $response['courses'][] = $newCourse;
        }
        if ($this->flag == 0){
            $this->app->response->setBody(json_encode($response));
        } else{
            $this->flag = 0;
            return $response;
        }
    }

    /**
    * @todo Receive the names from the database instead of defining it here.
    */
    public function getStatusName($courseStatus)
    {
        $statusNames = CourseStatus::getStatusDefinition();
        return $statusNames[$courseStatus];
    }

    /**
     * Function that handles all requests for marking tool.
     * Used by the functions that are called by Slim when data for the marking
     * tool is requested
     *
     * @author Florian Lücke
     */
    public function markingToolBase($userid,
                                    $courseid,
                                    $sheetid,
                                    $tutorId,
                                    $statusid,
                                    $shouldfilter,
                                    $selector)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        $response = array();

        //Get neccessary data
        $URL = "{$this->lURL}/DB/exercisesheet/course/{$courseid}/exercise";
        $handler1 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = "{$this->lURL}/DB/marking/exercisesheet/{$sheetid}";
        $handler2 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = "{$this->lURL}/DB/user/course/{$courseid}/status/1";
        $handler3 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = "{$this->lURL}/DB/group/exercisesheet/{$sheetid}";
        $handler4 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = "{$this->lURL}/DB/submission/group/user/{$userid}/course/{$courseid}/selected";
        $handler5 = Request_CreateRequest::createGet($URL, $header, $body);

        $URL = $this->lURL.'/DB/exercisetype';
        $handler6 = Request_CreateRequest::createGet($URL, $header, $body);

        $multiRequestHandle = new Request_MultiRequest();
        $multiRequestHandle->addRequest($handler1);
        $multiRequestHandle->addRequest($handler2);
        $multiRequestHandle->addRequest($handler3);
        $multiRequestHandle->addRequest($handler4);
        $multiRequestHandle->addRequest($handler5);
        $multiRequestHandle->addRequest($handler6);

        $answer = $multiRequestHandle->run();

        $sheets = json_decode($answer[0]['content'], true);
        $markings = json_decode($answer[1]['content'], true);
        $tutors = json_decode($answer[2]['content'], true);
        $groups = json_decode($answer[3]['content'], true);
        $submissions = json_decode($answer[4]['content'], true);
        $possibleExerciseTypes = json_decode($answer[5]['content'], true);

        // order exercise types by id
        $exerciseTypes = array();
        foreach ($possibleExerciseTypes as $exerciseType) {
            $exerciseTypes[$exerciseType['id']] = $exerciseType;
        }

        // find the current sheet and it's exercises
        foreach ($sheets as &$sheet) {
            $thisSheetId = $sheet['id'];

            if ($thisSheetId == $sheetid) {
                $thisExerciseSheet = $sheet;
            }

            unset($sheet['exercises']);
        }

        if (isset($thisExerciseSheet) == false) {
            $this->app->halt(404, '{"code":404,reason":"invalid sheet id"}');
        }

        // save the index of each exercise and add exercise type name
        $exercises = array();
        $exerciseIndices = array();
        foreach ($thisExerciseSheet['exercises'] as $idx => $exercise) {
            $exerciseId = $exercise['id'];
            $typeId = $exercise['type'];

            if (isset($exerciseTypes[$typeId])) {
                $type = $exerciseTypes[$typeId];
                $exercise['typeName'] = $type['name'];
            } else {
                $exercise['typeName'] = "unknown";
            }

            $exerciseIndices[$exerciseId] = $idx;
            $exercises[] = $exercise;
        }

        // save a reference to each user's group and add exercises to each group
        $userGroups = array();
        foreach ($groups as &$group) {
            $leaderId = $group['leader']['id'];
            $userGroups[$leaderId] = &$group;

            foreach ($group['members'] as $member) {
                $memberId = $member['id'];
                $userGroups[$memberId] = &$group;
            }

            $group['exercises'] = $exercises;
        }

        foreach ($submissions as $submission) {
            $studentId = $submission['studentId'];
            $exerciseId = $submission['exerciseId'];

            $exerciseIndex = $exerciseIndices[$exerciseId];

            $group = &$userGroups[$studentId];
            $group['exercises'][$exerciseIndex]['submission'] = $submission;
        }

        $filteredGroups = array();
        foreach ($markings as $marking) {

            // reverse marking and submission
            $submission = $marking['submission'];
            unset($marking['submission']);
            $submission['marking'] = $marking;

            // filter out markings by the tutor with id $tutorid
            if (($shouldfilter == false) || $selector($marking, $tutor, $statusid)) {
                $exerciseId = $submission['exerciseId'];
                $exerciseIndex = $exerciseIndices[$exerciseId];
                $studentId = $submission['studentId'];

                // assign the submission to its group
                $group = &$userGroups[$studentId];
                $groupExercises = &$group['exercises'];
                $groupExercises[$exerciseIndex]['submission'] = $submission;

                $leaderId = &$group['leader']['id'];
                $filteredGroups[$leaderId] = &$group;
            }
        }

        $response['groups'] = $shouldfilter == true ? array_values($filteredGroups) : $groups;
        $response['tutors'] = $tutors;
        $response['exerciseSheets'] = $sheets;
        $response['markingStatus'] = Marking::getStatusDefinition();

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    /**
     * Compiles data for the marking tool page.
     * This version is used when no additional parameters are given.
     *
     * @author Florian Lücke
     */
    public function markingTool($userid, $courseid, $sheetid)
    {
        $this->markingToolBase($userid,
                               $courseid,
                               $sheetid,
                               NULL,
                               NULL,
                               false,
                               NULL);
    }

    /**
     * Compiles data for the marking tool page.
     * This version is used when we want markings from a specific tutor.
     *
     * @author Florian Lücke
     */
    public function markingToolTutor($userid, $courseid, $sheetid, $tutorid)
    {
        $selector = function ($marking, $tutor, $statusid) {
            if ($marking['tutorId'] == $tutorid) {
                return true;
            }

            return false;
        };

        $this->markingToolBase($userid,
                               $courseid,
                               $sheetid,
                               $tutorid,
                               NULL,
                               true,
                               $selector);
    }

    /**
     * Compiles data for the marking tool page.
     * This version is used when we want markings with a specific status.
     *
     * @todo male it possible to request unsubmitted exercises.
     *
     * @author Florian Lücke
     */
    public function markingToolStatus($userid, $courseid, $sheetid, $statusid)
    {
        $selector = function ($marking, $tutor, $statusid) {
            if ($marking['status'] == $statusid) {
                return true;
            }

            return false;
        };

        $this->markingToolBase($userid,
                               $courseid,
                               $sheetid,
                               NULL,
                               $statusid,
                               true,
                               $selector);
    }

    /**
     * Compiles data for the marking tool page.
     * This version is used when we want markings from a specific tutor and
     * with a specific status.
     *
     * @author Florian Lücke
     */
    public function markingToolTutorStatus($userid, $courseid, $sheetid, $tutorid, $statusid)
    {
        $selector = function ($marking, $tutor, $statusid) {
            if (($marking['status'] == $statusid)
                && ($marking['tutorId'] == $tutorid)) {
                return true;
            }

            return false;
        };

        $this->markingToolBase($userid,
                               $courseid,
                               $sheetid,
                               $tutorid,
                               $statusid,
                               true,
                               $selector);
    }

    public function uploadHistory($userid, $courseid, $sheetid, $uploaduserid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // load all exercises of an exercise sheet
        $URL = $this->lURL.'/exercisesheet/exercisesheet/'.$sheetid.'/exercise/';
        $answer = Request::custom('GET', $URL, $header, $body);
        $exercisesheet = json_decode($answer['content'], true);

        if(!empty($exercisesheet)) {
            $exercises = $exercisesheet['exercises'];
        }

        // load all submissions for every exercise of the exerciseSheet
        if(!empty($exercises)) {
            //$exercises = $exercisesheet['exercises'];
            foreach ($exercises as $exercise) {
                $URL = $this->lURL.'/DB/submission/user/'.$uploaduserid.'/exercise/'.$exercise['id'];
                $answer = Request::custom('GET', $URL, $header, $body);
                $submissions[] = json_decode($answer['content'], true);
            }
        }

        // add every submission to the response
        if(!empty($submissions)) {
            foreach ($submissions as $submission) {
                $response['submissionHistory'][] = $submission;
            }
        }

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }


    public function uploadHistoryOptions($userid, $courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // load all users of the course
        $URL = $this->lURL.'/DB/user/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $response['users'] = json_decode($answer['content'], true);

        // load all exercisesheets of the course
        $URL = $this->lURL.'/exercisesheet/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $response['sheets'] = json_decode($answer['content'], true);

        if(!empty($exercisesheet)) {
            $exercises = $exercisesheet['exercises'];
        }

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    /**
     * Compiles data for the upload page.
     * called whe the component receives an HTTP GET request to
     * /upload/user/$userid/course/$courseid/exercisesheet/$sheetid
     *
     * @author Florian Lücke.
     */
    public function upload($userid, $courseid, $sheetid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // loads all exercises of an exercise sheet
        $URL = "{$this->lURL}/exercisesheet/exercisesheet/{$sheetid}/exercise/";
        $answer = Request::custom('GET', $URL, $header, $body);
        $exercisesheet = json_decode($answer['content'], true);

        $URL = "{$this->lURL}/DB/submission/group/user/{$userid}/exercisesheet/{$sheetid}/selected";
        $answer = Request::custom('GET', $URL, $header, $body);
        $submissions = json_decode($answer['content'], true);

        if (isset($submissions) == false) {
            $submissions = array();
        }

        $exercises = &$exercisesheet['exercises'];

        $submissionsByExercise = array();
        foreach ($submissions as &$submission) {
            $exerciseId = $submission['exerciseId'];
            $submissionsByExercise[$exerciseId] = &$submission;
        }

        // loads all submissions for every exercise of the exerciseSheet
        if (!empty($exercises)) {
            foreach ($exercises as &$exercise) {
                $exerciseId = $exercise['id'];

                if (isset($submissionsByExercise[$exerciseId])) {
                    $submission = &$submissionsByExercise[$exerciseId];
                    $exercise['selectedSubmission'] = &$submission;
                }
            }
        }

        $response['exercises'] = $exercises;
        if (isset($exercisesheet['sheetName'])) {
            $response['sheetName'] = $exercisesheet['sheetName'];
        }

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    public function mainSettings($userid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // returns all courses
        $URL = $this->lURL . '/DB/course';
        $courses = Request::custom('GET', $URL, $header, $body);
        $courses = json_decode($courses['content'], true);

        // returns all possible exercisetypes
        $URL = $this->lURL . '/DB/exercisetype';
        $exerciseTypes = Request::custom('GET', $URL, $header, $body);
        $response['exerciseTypes'] = json_decode($exerciseTypes['content'], true);

        // returns the user
        $URL = $this->lURL . '/DB/user/user/' . $userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $user = json_decode($answer['content'], true);

        unset($user['courses']);

        // sorts courses by name
        function compare_courseName($a, $b) {
             return strnatcmp($a['name'], $b['name']);
        }
        usort($courses, 'compare_courseName');

        $this->flag = 1;

        $response['courses'] = $courses;
        $response['user'] = $user;

        $this->app->response->setBody(json_encode($response));
    }

    public function tutorDozentAdmin($userid, $courseid)
    {

        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        // load first pack of Requests
        $multiRequestHandle2 = new Request_MultiRequest();

        $URL = $this->lURL . '/DB/exercisetype';
        $handler1 = Request_CreateRequest::createGet($URL, $header, $body);
        $URL = $this->lURL . '/exercisesheet/course/' . $courseid . '/exercise';
        $handler2 = Request_CreateRequest::createGet($URL, $header, $body);
        $URL = $this->lURL . '/DB/user/course/' . $courseid;
        $handler3 = Request_CreateRequest::createGet($URL, $header, $body);

        $multiRequestHandle2->addRequest($handler1);
        $multiRequestHandle2->addRequest($handler2);
        $multiRequestHandle2->addRequest($handler3);

        $answer2 = $multiRequestHandle2->run();

        // decode answers (given in the order which they've been declared)
        $exerciseTypes = json_decode($answer2[0]['content'], true);
        $sheets = json_decode($answer2[1]['content'], true);
        $courseUser = json_decode($answer2[2]['content'], true);

        // load alls selectedsubmission in one pack
        $multiRequestHandle = new Request_MultiRequest();

        foreach ($sheets as $sheet) {
            $URL = $this->lURL.'/DB/selectedsubmission/exercisesheet/'.$sheet['id'];
            $handler = Request_CreateRequest::createGet($URL, $header, $body);

            $multiRequestHandle->addRequest($handler);
        }

        $answer = $multiRequestHandle->run();

        foreach ($sheets as $key => &$sheet) {

            $hasAttachments = false;

            // returns all selected submissions for the sheet
            $selectedSubmissions = json_decode($answer[$key]['content'], true);

            foreach ($sheet['exercises'] as &$exercise) {
                // add attachments to exercise
                if (count($exercise['attachments']) > 0) {
                    $exercise['attachment'] = $exercise['attachments'][0];
                    $hasAttachments = true;
                    break;
                }
            }

            $sheet['hasAttachments'] = $hasAttachments;

            // adds counts for the additional information in the footer
            $sheet['courseUserCount'] = count($courseUser);
            $sheet['studentsWithSubmissionCount'] = count($selectedSubmissions);
            $sheet['studentsWithoutSubmissionCount'] = $sheet['courseUserCount'] - $sheet['studentsWithSubmissionCount'];

            foreach ($sheet['exercises'] as &$exercise) {
                foreach ($exerciseTypes as $exerciseType) {
                    if ($exerciseType['id'] == $exercise['type']) {
                        $exercise['typeName'] = $exerciseType['name'];
                    }
                }
            }
        }

        $response['sheets'] = $sheets;

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    /**
     * Compiles data for group site.
     * Called when this component receives an HTTP GET request to
     * /upload/user/$userid/course/$courseid/exercisesheet/$sheetid
     *
     * @author Florian Lücke.
     */
    public function groupSite($userid, $courseid, $sheetid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        $response = array();

        //Get the Group of the User for the given sheet
        $URL = "{$this->lURL}/DB/group/user/{$userid}/exercisesheet/{$sheetid}";
        $answer = Request::custom('GET', $URL, $header, $body);
        $group = json_decode($answer['content'], true);

        //Get the maximum Groupsize of the sheet
        $URL = "{$this->lURL}/exercisesheet/exercisesheet/{$sheetid}/exercise";
        $answer = Request::custom('GET', $URL, $header, $body);
        $sheet = json_decode($answer['content'], true);

        $exercises = &$sheet['exercises'];

        $URL = "{$this->lURL}/DB/submission/group/user/{$userid}/exercisesheet/{$sheetid}";
        $answer = Request::custom('GET', $URL, $header, $body);
        $submissions = json_decode($answer['content'], true);

        $URL = "{$this->lURL}/DB/invitation/leader/exercisesheet/{$sheetid}/user/{$userid}";
        $answer = Request::custom('GET', $URL, $header, $body);
        $invited = json_decode($answer['content'], true);

        $URL = "{$this->lURL}/DB/invitation/member/exercisesheet/{$sheetid}/user/{$userid}";
        $answer = Request::custom('GET', $URL, $header, $body);
        $invitations = json_decode($answer['content'], true);

        // oder users by id
        $usersById = array();
        $leaderId = $group['leader']['id'];
        $usersById[$leaderId] = &$group['leader'];
        foreach ($group['members'] as &$member) {
            $userId = $member['id'];
            $usersById[$userId] = &$member;
        }

        // order submissions by exercise and user, only take latest
        $exerciseUserSubmissions = array();
        foreach ($submissions as $submission) {
            $userId = $submission['studentId'];
            $exerciseId = $submission['exerciseId'];

            if (isset($exerciseUserSubmissions[$exerciseId]) == false) {
                $exerciseUserSubmissions[$exerciseId] = array();
            }

            if (isset($exerciseUserSubmissions[$exerciseId][$userId]) == false) {
                $user = &$usersById[$userId];
                $userSubmission = array('user' => $user,
                                        'submission' => $submission);
                $exerciseUserSubmissions[$exerciseId][$userId] = $userSubmission;
            } else {
                $lastUserSubmission = $exerciseUserSubmissions[$exerciseId][$userId];
                if ($lastUserSubmission['submission']['date'] < $submission['date']) {

                    // smaller date means less seconds since refrence date
                    // so $lastSubmission is older
                    $user = &$usersById[$userId];
                    $userSubmission = array('user' => $user,
                                            'submission' => $submission);
                    $exerciseUserSubmissions[$exerciseId][$userId] = $userSubmission;
                }
            }
        }

        // insert submissions into the exercises
        foreach ($exercises as &$exercise) {
            $exerciseId = &$exercise['id'];
            if (isset($exerciseUserSubmissions[$exerciseId])) {
                $groupSubmissions = array_values($exerciseUserSubmissions[$exerciseId]);
                $exercise['groupSubmissions'] = $groupSubmissions;
            } else {
                $exercise['groupSubmissions'] = array();
            }

        }

        $response['invitationsFromGroup'] = $invited;
        $response['invitationsToGroup'] = $invitations;
        $response['exercises'] = $exercises;
        $response['group'] = $group;
        $response['groupSize'] = $sheet['groupSize'];

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }


    /**
     * Compiles data for the Condition site.
     *
     * @warning If there is more than one condition assigned to the same
     * exercise type it is undefined which condition will be evaluated. This
     * might even change per user!.
     *
     * @author Florian Lücke
     */
    public function checkCondition($userid, $courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // load all the data
        $URL = $this->lURL.'/DB/exercisetype';
        $answer = Request::custom('GET', $URL, $header, $body);
        $possibleExerciseTypes = json_decode($answer['content'], true);

        $URL = $this->lURL.'/DB/exercise/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $exercises = json_decode($answer['content'], true);

        $URL = $this->lURL.'/DB/approvalcondition/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $approvalconditions = json_decode($answer['content'], true);

        $URL = $this->lURL.'/DB/user/course/'.$courseid.'/status/0';
        $answer = Request::custom('GET', $URL, $header, $body);
        $students = json_decode($answer['content'], true);

        // preprocess the data to make it quicker to get specific values
        $exerciseTypes = array();
        foreach ($possibleExerciseTypes as $exerciseType) {
            $exerciseTypes[$exerciseType['id']] = $exerciseType;
        }

        $exercisesById = array();
        foreach ($exercises as $exercise) {
            $exercisesById[$exercise['id']] = $exercise;
        }

        $exercisesByType = array();
        foreach ($exercises as $exercise) {
            if (!isset($exercisesByType[$exercise['type']])) {
                $exercisesByType[$exercise['type']] = array();
            }
            unset($exercise['submissions']);
            $exercisesByType[$exercise['type']][] = $exercise;
        }

        // calculate the maximum number of points that a user could get
        // for each exercise type
        $maxPointsByType = array();
        foreach ($exercisesByType as $type => $exercises) {
            $maxPointsByType[$type] = array_reduce($exercises,
                                                   function ($value, $exercise) {

                if ($exercise['bonus'] == 0) {
                    // only count the
                    $value += $exercise['maxPoints'];
                }

                return $value;
            }, 0);
        }

        $approvalconditionsByType = array();
        foreach ($approvalconditions as &$condition){
            // add the name of the exercise type to the approvalcondition
            $typeID = $condition['exerciseTypeId'];
            $condition['exerciseType'] = $exerciseTypes[$typeID]['name'];

            // prepare percenteages for the UI
            $condition['minimumPercentage'] = $condition['percentage'] * 100;

            $condition['approvalConditionId'] = $condition['id'];
            unset($condition['id']);
            // sort approvalconditions by exercise type
            /**
              * @warning this implies that there is *only one* approval
              * condition per exercise type!
              */
            $exerciseTypeID = $condition['exerciseTypeId'];
            $condition['maxPoints'] = $maxPointsByType[$exerciseTypeID];
            $approvalconditionsByType[$exerciseTypeID] = $condition;

        }

        // get all markings
        /**
         * @todo Could get course markings here instead. '/marking/course/:cid'
         */
        $allMarkings = array();
        foreach ($exercises as $exercise){
            $URL = $this->lURL.'/DB/marking/exercise/'.$exercise['id'];
            $answer = Request::custom('GET', $URL, $header, $body);
            $markings = json_decode($answer['content'], true);

            foreach($markings as $marking){
                $allMarkings[] = $marking;
            }
        }

        // done preprocessing
        // actual computation starts here

        // add up points that each student reached in a specific exercise type
        $studentMarkings = array();
        foreach ($allMarkings as $marking) {
            $studentID = $marking['submission']['studentId'];
            if (!isset($studentMarkings[$studentID])) {
                $studentMarkings[$studentID] = array();
            }

            $exerciseID = $marking['submission']['exerciseId'];
            $exerciseType = $exercisesById[$exerciseID]['type'];

            if (!isset($studentMarkings[$studentID][$exerciseType])) {
                $studentMarkings[$studentID][$exerciseType] = 0;
            }

            $studentMarkings[$studentID][$exerciseType] += $marking['points'];
        }

        foreach ($students as &$student) {
            unset($student['courses']);
            unset($student['attachments']);
            $student['percentages'] = array();

            $allApproved = true;
            // iteraterate over all conditions, this will also filter out the
            // exercisetypes that are not needed for this course
            foreach ($approvalconditionsByType as $typeID => $condition) {

                    $thisPercentage = array();

                    $thisPercentage['exerciseTypeID'] = $typeID;
                    $thisPercentage['exerciseType'] = $exerciseTypes[$typeID]['name'];

                    // check if it was possible to get points for this exercisetype
                    if (!isset($maxPointsByType[$typeID])) {
                        Logger::Log("Unmatchable condition: "
                                    . $condition['approvalConditionId']
                                    . "in course: "
                                    . $courseid, LogLeve::WARNING);

                        $maxPointsByType[$typeID] = 0;
                    }

                    if ($maxPointsByType[$typeID] == 0) {
                        $thisPercentage['percentage'] = '100';
                        $thisPercentage['isApproved'] = true;
                        $thisPercentage['maxPoints'] = 0;

                        if (isset($studentMarkings[$student['id']])
                            && isset($studentMarkings[$student['id']][$typeID])) {
                            $points = $studentMarkings[$student['id']][$typeID];
                            $thisPercentage['points'] = $points;
                        } else {
                            $thisPercentage['points'] = 0;
                        }
                    } else {

                        // check if there are points for this
                        // student-exerciseType combination
                        if (isset($studentMarkings[$student['id']])
                            && isset($studentMarkings[$student['id']][$typeID])) {
                            // the user has points for this exercise type

                            $points = $studentMarkings[$student['id']][$typeID];
                            $maxPoints = $maxPointsByType[$typeID];
                            $percentage = $points / $maxPoints;

                            $percentageNeeded = $condition['percentage'];

                            $thisPercentage['points'] = $points;
                            $thisPercentage['maxPoints'] = $maxPoints;

                            $typeApproved = ($percentage > $percentageNeeded);
                            $allApproved = $allApproved && $typeApproved;

                            $thisPercentage['isApproved'] = $typeApproved;
                            $thisPercentage['percentage'] = round($percentage * 100, 2);
                        } else {

                            // there are no points for the user for this
                            // exercise type
                            $thisPercentage['percentage'] = 0;
                            $thisPercentage['points'] = 0;

                            $maxPoints = $maxPointsByType[$typeID];
                            $thisPercentage['maxPoints'] = $maxPoints;

                            $typeApproved = ($maxPoints == 0);
                            $thisPercentage['isApproved'] = $typeApproved;

                            $allApproved = $allApproved && $typeApproved;
                        }

                    }

                    $student['percentages'][] = $thisPercentage;
            }

            $student['isApproved'] = $allApproved;
        }

        $this->flag = 1;
        $response['user'] = $this->userWithCourse($userid, $courseid);
        $response['users'] = $students;

        $response['minimumPercentages'] = array_values($approvalconditionsByType);

        $this->app->response->setBody(json_encode($response));
    }

    public function courseManagement($userid, $courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // returns basic course information
        $URL = $this->lURL.'/DB/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $response['course'] = json_decode($answer['content'], true);

        // returns all exerciseTypes
        $URL = $this->lURL.'/DB/exercisetype';
        $answer = Request::custom('GET', $URL, $header, $body);
        $response['exerciseTypes'] = json_decode($answer['content'], true);

        // returns all possible exerciseTypes of the course
        $URL = $this->lURL.'/DB/approvalcondition/course/' . $courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $approvalConditions = json_decode($answer['content'], true);

        // returns all users of the given course
        $URL = $this->lURL.'/DB/user/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $allUsers = json_decode($answer['content'], true);

        // adds an 'inCourse' flag to the exerciseType if there is
        // an approvalCondition with the same id in the same course

        /**
         * @todo Improve running time.
         */
        if(!empty($approvalConditions)) {
            foreach ($approvalConditions as &$approvalCondition) {
                foreach ($response['exerciseTypes'] as &$exerciseType) {
                    if ($approvalCondition['exerciseTypeId'] == $exerciseType['id']) {
                        $exerciseType['inCourse'] = true;
                    }
                }
            }
        }

        // only selects the users whose course-status is student, tutor, lecturer or admin
        if(!empty($allUsers)) {
            foreach($allUsers as $user) {
                if ($user['courses'][0]['status'] >= 0 && $user['courses'][0]['status'] < 4) {

                    // adds the course-status to the user objects in the response
                    $user['statusName'] = $this->getStatusName($user['courses'][0]['status']);

                    // removes unnecessary data from the user object
                    unset($user['password']);
                    unset($user['salt']);
                    unset($user['failedLogins']);
                    unset($user['courses']);

                    // adds the user to the response
                    $response['users'][] = $user;
                }
            }
        }

        $this->flag = 1;

        // adds the user_course_data to the response
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }

    public function createSheetInfo($userid, $courseid) {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();

        // returns all possible exerciseTypes of the course
        $URL = $this->lURL.'/DB/approvalcondition/course/' . $courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $response['exerciseTypes'] = json_decode($answer['content'], true);

        // returns all exerciseTypes
        $URL = $this->lURL.'/DB/exercisetype';
        $answer = Request::custom('GET', $URL, $header, $body);
        $allexerciseTypes = json_decode($answer['content'], true);

        if(!empty($response['exerciseTypes'])) {
            foreach ($response['exerciseTypes'] as &$exerciseType) {
                foreach ($allexerciseTypes as &$allexerciseType) {
                    if ($exerciseType['exerciseTypeId'] == $allexerciseType['id']) {
                        $exerciseType['name'] = $allexerciseType['name'];
                    }
                }
            }
        } else {
            unset($response['exerciseTypes']);
        }

        $this->flag = 1;

        // adds the user_course_data to the response
        $response['user'] = $this->userWithCourse($userid, $courseid);

        $this->app->response->setBody(json_encode($response));
    }
}

// get new componenent configuartion from the database
$com = new CConfig(LgetSite::getPrefix());

// start the component with the newly received configuration
if (!$com->used())
    new LgetSite($com->loadConfig());
?>
