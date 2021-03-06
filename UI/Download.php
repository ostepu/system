<?php
/**
 * @file Download.php
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 *
 * @package OSTEPU (https://github.com/ostepu/ostepu-core)
 * @since 0.1.0
 *
 * @author Till Uhlig <till.uhlig@student.uni-halle.de>
 * @date 2014-2016
 * @author Ralf Busch <ralfbusch92@gmail.com>
 * @date 2014
 * @author Florian Lücke <florian.luecke@gmail.com>
 * @date 2014
 */

/**
 * @file Dowload.php
 * Contains code that handles download requests.
 *
 * @todo support downloads of csv files
 */
ob_start();

include_once dirname(__FILE__) . '/include/Boilerplate.php';
include_once dirname(__FILE__) . '/../Assistants/Request.php';
include_once dirname(__FILE__) . '/../Assistants/Structures.php';
include_once dirname(__FILE__) . '/../Assistants/Language.php';
include_once dirname(__FILE__) . '/../Assistants/LArraySorter.php';

function checkPermission($permission){
    global $getSiteURI;
    global $cid;
    global $uid;
    $URL = $getSiteURI . "/accountsettings/user/{$uid}/course/{$cid}";
    $user_course_data = http_get($URL, true);
    $user_course_data = json_decode($user_course_data,true);
    Authentication::checkRights($permission, $cid, $uid, $user_course_data);
    return $user_course_data;
}

$_GET=cleanInput($_GET);

$types = Marking::getStatusDefinition();
$status = null;
foreach ($types as $type){
    if (isset($_GET['downloadCSV_'.$type['id']])){
        $status = $type['id'];
        $_GET['downloadCSV']=$_GET['downloadCSV_'.$type['id']];
        break;
    }
}
if (isset($_GET['downloadCSV'])) {
    // es werden alle zugewiesenen Korrekturaufträge eines Kontrolleurs heruntergeladen
    $user_course_data = checkPermission(PRIVILEGE_LEVEL::TUTOR);
    $sid = $_GET['downloadCSV'];
    $location = $logicURI . '/tutor/user/' . $uid . '/exercisesheet/' . $sid.(isset($status) ? '/status/'.$status : '');    
    
    if (Authentication::checkRight(PRIVILEGE_LEVEL::LECTURER, $cid, $uid, $user_course_data)){
        $location = $logicURI . '/tutor/user/' . $uid . '/exercisesheet/' . $sid.(isset($status) ? '/status/'.$status : '').'/withnames';  
    } else
    {
        $obj = Course::decodeCourse(Course::encodeCourse($user_course_data['courses'][0]['course']));
        if (Course::containsSetting($obj,'InsertStudentNamesIntoTutorArchives') !== null){
            if (Course::containsSetting($obj,'InsertStudentNamesIntoTutorArchives') == 1){
                // auch Tutoren sollen die Studentendaten bekommen
                $location = $logicURI . '/tutor/user/' . $uid . '/exercisesheet/' . $sid.(isset($status) ? '/status/'.$status : '').'/withnames'; 
            }
        }
    }
    
    $resultData = http_get($location, true);
    $result = fileUtils::prepareFileObject(json_decode($resultData, true));
    
    echo json_encode($result);
    exit(0);
}

if (isset($_GET['downloadAttachments'])) {
    // hier werden alle Anhänge einer Übungsserie heruntergeladen
    $user_course_data = checkPermission(PRIVILEGE_LEVEL::STUDENT);

    $selectedUser = $uid;
    if (Authentication::checkRight(PRIVILEGE_LEVEL::LECTURER, $cid, $uid, $user_course_data)){
        $selectedUser = isset($_SESSION['selectedUser']) ? $_SESSION['selectedUser'] : $uid;
    }

    $sid = $_GET['downloadAttachments'];
    $URL = "{$logicURI}/DB/attachment/exercisesheet/{$sid}";
    $attachments = http_get($URL, true);
    $attachments = json_decode($attachments, true);

    $files = array();
    foreach ($attachments as $attachment) {
        $files[] = $attachment['file'];
    }

    $fileString = json_encode($files);

    $zipfile = http_post_data($filesystemURI . '/zip',  $fileString, true);
    $zipfile = File::decodeFile($zipfile);
    $zipfile->setDisplayName('attachments.zip');
    $zipfileData = File::encodeFile($zipfile);
    
    $zipfile = fileUtils::prepareFileObject(json_decode($zipfileData, true));
    
    echo json_encode($zipfile);
    exit(0);

}

if (isset($_GET['downloadMarkings'])) {
    // hier werden alle Dateien eines Studenten zu einer Einsendung heruntergeladen
    $user_course_data = checkPermission(PRIVILEGE_LEVEL::STUDENT);
    $sid = $_GET['downloadMarkings'];    

    $selectedUser = $uid;
    if (Authentication::checkRight(PRIVILEGE_LEVEL::LECTURER, $cid, $uid, $user_course_data)){
        $selectedUser = isset($_SESSION['selectedUser']) ? $_SESSION['selectedUser'] : $uid;
    }

    $multiRequestHandle = new Request_MultiRequest();

    //request to database to get the markings
    $handler = Request_CreateRequest::createCustom('GET', "{$logicURI}/DB/marking/exercisesheet/{$sid}/user/{$selectedUser}", array(),'');
    $multiRequestHandle->addRequest($handler);

    $handler = Request_CreateRequest::createCustom('GET', "{$logicURI}/DB/exercisesheet/exercisesheet/{$sid}/exercise", array(),'');
    $multiRequestHandle->addRequest($handler);

    $answer = $multiRequestHandle->run();
    $markings = json_decode($answer[0]['content'], true);

    $sheet = json_decode($answer[1]['content'], true);
    $exercises = $sheet['exercises'];

    //an array to descripe the subtasks
    $alphabet = range('a', 'z');
    $count = 0;
    $namesOfExercises = array();
    $attachments = array();
    
    // die Aufgaben müssen entsprechend sortiert sein, sonst werden die Namen falsch erzeugt,
    // falls eine Aufgabe später hinzugefügt wurde
    $exercises = LArraySorter::orderBy($exercises, 'link', SORT_ASC, 'linkName', SORT_ASC);

    $count=null;
    foreach ($exercises as $key => $exercise){
        $exerciseId = $exercise['id'];

        if (isset($exercise['attachments']))
            $attachments[$exerciseId] = $exercise['attachments'];

        if ($count===null || $exercises[$count]['link'] != $exercise['link']){
            $count=$key;
            $namesOfExercises[$exerciseId] = 'Aufgabe_'.$exercise['link'];
            $subtask = 0;
        }else{
            $subtask++;
            $namesOfExercises[$exerciseId] = 'Aufgabe_'.$exercise['link'].$alphabet[$subtask];
            $namesOfExercises[$exercises[$count]['id']] = 'Aufgabe_'.$exercises[$count]['link'].$alphabet[0];
        }
    }

    $files = array();
    foreach ($markings as $marking) {
        if (isset($marking['submission']['selectedForGroup']) && $marking['submission']['selectedForGroup']){                  
            $exerciseId = $marking['submission']['exerciseId'];

            // marking
            if (isset($marking['file']) && (!isset($marking['hideFile']) || !$marking['hideFile']) ){
                $marking['file']['displayName'] = "{$namesOfExercises[$exerciseId]}/K_{$marking['file']['hash']}_{$marking['file']['displayName']}";
                $files[] = $marking['file'];
            }

            // submission
            if (isset($marking['submission']['file']) && (!isset($marking['submission']['hideFile']) || !$marking['submission']['hideFile'])){
                $marking['submission']['file']['displayName'] = "{$namesOfExercises[$exerciseId]}/{$marking['submission']['file']['hash']}_{$marking['submission']['file']['displayName']}";
                $files[] = $marking['submission']['file'];
            }

            // attachments
            if (isset($attachments[$exerciseId])){
                foreach ($attachments[$exerciseId] as $attachment){
                    if (isset($attachment['file']['address'])){
                        $attachment['file']['displayName'] = "{$namesOfExercises[$exerciseId]}/A_{$attachment['file']['hash']}_{$attachment['file']['displayName']}";
                        $files[] = $attachment['file'];    
                    }
                }
            }
        }
    }
    unset($attachments, $markings, $exercises);

    // sheetFile
    if (isset($sheet['sheetFile']['address'])){
        $sheet['sheetFile']['displayName'] = "{$sheet['sheetFile']['displayName']}";
        $files[] = $sheet['sheetFile'];
    }

    // sampleSolution
    if (isset($sheet['sampleSolution']['address'])){
        $sheet['sampleSolution']['displayName'] = "{$sheet['sampleSolution']['displayName']}";
        $files[] = $sheet['sampleSolution'];
    }

    $fileString = json_encode($files);
    $zipfile = http_post_data($filesystemURI . '/zip',  $fileString, true);
    $zipfile = File::decodeFile($zipfile);
    $zipfile->setDisplayName('markings.zip');
    $zipfileData = File::encodeFile($zipfile);
    $zipfile = fileUtils::prepareFileObject(json_decode($zipfileData, true));
    
    echo json_encode($zipfile);
    exit(0);
}

ob_end_flush();