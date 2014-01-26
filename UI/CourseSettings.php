<?php
/**
 * @file CourseSettings.php
 * Constructs the page for managing basic settings for a course.
 */

include_once 'include/Authorization.php';
include_once 'include/HTMLWrapper.php';
include_once 'include/Template.php';

if (isset($_GET['cid'])) {
    $cid = $_GET['cid'];
} else {
    die('no course id!\n');
}

if (isset($_SESSION['uid'])) {
    $uid = $_SESSION['uid'];
} else {
    die('no user id!\n');
}

// load user and course data from the database
$databaseURI = "http://141.48.9.92/uebungsplattform/DB/DBControl/coursestatus/course/{$cid}/user/{$uid}";
$user_course_data = http_get($databaseURI, true, $message);
if ($message == "401") {$auth->logoutUser();}
$user_course_data = json_decode($user_course_data, true);

/**
 * @todo check rights
 */

$menu = Template::WithTemplateFile('include/Navigation/NavigationAdmin.template.html');

// construct a new header
$h = Template::WithTemplateFile('include/Header/Header.template.html');
$h->bind($user_course_data);
$h->bind(array("name" => $user_course_data['courses'][0]['course']['name'],
               "backTitle" => "Veranstaltung wechseln",
               "backURL" => "index.php",
               "navigationElement" => $menu,
               "notificationElements" => $notifications));

$t = Template::WithTemplateFile('include/CourseSettings/SetCourseSettings.template.html');


$w = new HTMLWrapper($h, $t);
$w->set_config_file('include/configs/config_default.json');
$w->show();
?>

