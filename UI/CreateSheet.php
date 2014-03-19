<?php
/**
 * @file CreateSheet.php
 * Constructs a page where a user can create an exercise sheet.
 *
 * @author Felix Schmidt
 * @author Florian Lücke
 * @author Ralf Busch
 *
 * @todo choose correct groupsize for no Group (0 or 1)
 * @todo evaluate correct exercisetype in $subeval
 * @todo dont reset form if an error exists
 */
ini_set('error_reporting', -1);
ini_set('display_errors', 1);
ini_set('html_errors', 1);
include_once 'include/Boilerplate.php';
include_once '../Assistants/Structures.php';
include_once 'include/FormEvaluator.php';

// load user data from the database
$URL = $getSiteURI . "/createsheet/user/{$uid}/course/{$cid}";
$createsheetData = http_get($URL, true);
$createsheetData = json_decode($createsheetData, true);

$noContent = false;

if (isset($createsheetData['exerciseTypes'])) {
    $_SESSION['JSCACHE'] = json_encode($createsheetData['exerciseTypes']);
} else {
    $_SESSION['JSCACHE'] = "";
    $errormsg = "Bitte weisen Sie der Veranstaltung zugelassene Punktearten zu!";
    array_push($notifications, MakeNotification('warning', $errormsg));
    $noContent = true;
}

$errorInSent = false;

if (isset($_POST['action']) && $_POST['action'] == "new") {
    $timestamp = time();

    // validate all sheet data
    $f = new FormEvaluator($_POST);
    $f->checkStringForKey('sheetName',
                          FormEvaluator::REQUIRED,
                          'error',
                          'Ungültiger Blattname.');
    $f->checkStringForKey('startDate',
                          FormEvaluator::REQUIRED,
                          'warning',
                          'Leerer Bearbeitungsanfang.');
    $f->checkStringForKey('endDate',
                          FormEvaluator::REQUIRED,
                          'warning',
                          'Leerer Bearbeitungsende.');
    $f->checkIntegerForKey('groupSize',
                           FormEvaluator::REQUIRED,
                           'warning',
                           'Ungültige Gruppenstärke.',
                           array('min' => 0,'max' => $createsheetData['user']['courses'][0]['course']['defaultGroupSize']));
    $f->checkArrayOfArraysForKey('exercises',
                                 FormEvaluator::REQUIRED,
                                 'warning',
                                 'Bitte erstellen Sie mindestens eine Aufgabe.');
    // check if startDate is not later than endDate and if it matches format
    $correctDates = true;
    if (strtotime(str_replace(" - ", " ", $_POST['startDate'])) > strtotime(str_replace(" - ", " ", $_POST['endDate']))
        || !preg_match("#^\d\d.\d\d.\d\d\d\d - \d\d:\d\d$#", $_POST['startDate'])
        || !preg_match("#^\d\d.\d\d.\d\d\d\d - \d\d:\d\d$#", $_POST['endDate'])) {
        $correctDates = false;
        $errormsg = "Überprüfen Sie Bearbeitungsanfang sowie Bearbeitungsende!";
        array_push($notifications, MakeNotification('warning', $errormsg));
    }
    // check if sheetPDF is given
    $noFile = false;
    if ($_FILES['sheetPDF']['error'] == 4) {
        $noFile = true;
        $errormsg = "Bitte laden Sie ein Übungsblatt (PDF) hoch.";
        array_push($notifications, MakeNotification('warning', $errormsg));
    }
    // validate subtasks
    $correctExercise = true;
    $validatedExercises = array();
    if (isset($_POST['exercises']) == true && empty($_POST['exercises']) == false) {
        foreach ($_POST['exercises'] as $key1 => $exercise) {
            if ($correctExercise == false) {
                break;
            }
            // evaluate if subexercises per exercise isnt empty
            $eval = new FormEvaluator($exercise);
            $eval->checkArrayOfArraysForKey('subexercises',
                                    FormEvaluator::REQUIRED,
                                    'warning',
                                    'Ungültige Anzahl an Teilaufgaben.');
            if ($eval->evaluate(true)) {
                // clean Exercises
                $foundValues = $eval->foundValues;
                array_push($validatedExercises, $foundValues['subexercises']);
                // evaluate subexercises
                foreach ($exercise['subexercises'] as $key2 => $subexercise) {
                    // evaluate given subexercises
                    $subeval = new FormEvaluator($subexercise);
                    $subeval->checkIntegerForKey('maxPoints',
                                                 FormEvaluator::REQUIRED,
                                                 'warning',
                                                 'Ungültige Punkteanzahl angegeben.',
                                                 array('min' => 1));
                    if ($subeval->evaluate() == false) {
                        $notifications = array_merge($notifications, $subeval->notifications);
                        $correctExercise = false;
                        break;
                    }
                    // evaluate ExerciseTypes
                    if (preg_match("#[0-9]+b?$#", $subexercise['exerciseType']) == false) {
                        $errormsg = "Falsche Aufgabentypen.";
                        array_push($notifications, MakeNotification('warning', $errormsg));
                        $correctExercise = false;
                        break;
                    }

                    // evaluate mime-types
                    $mimeTypes = explode(",", $subexercise['mime-type']);
                    foreach ($mimeTypes as &$mimeType) {
                        if (FILE_TYPE::checkSupportedFileType(trim(strtolower($mimeType))) == false) {
                            $errormsg = "Sie haben eine nicht unterstützte Dateiendung verwendet.";
                            array_push($notifications, MakeNotification('warning', $errormsg));
                            $correctExercise = false;
                            break;
                        } else { // if mime-type is supported replace fileending with mimetype
                            $mimeType = FILE_TYPE::getMimeTypeByFileEnding(trim(strtolower($mimeType)));
                        }
                    }
                    // save mimeTypes in validated Exercises
                    $validatedExercises[$key1][$key2]['mime-type'] = $mimeTypes;
                }
            }  else {
                $notifications = array_merge($notifications, $eval->notifications);
                $correctExercise = false;
                break;
            }
        }
    }

    // only if validation was correct
    $ready = $f->evaluate(true);
    if ($ready == true && $noFile == false && $correctExercise == true && $correctDates == true) {
        // get sheetPDF
        $filePath = $_FILES['sheetPDF']['tmp_name'];
        $displayName = $_FILES['sheetPDF']['name'];
        $data = file_get_contents($filePath);
        $data = base64_encode($data);
        $sheetPDFFile = File::createFile(NULL,$displayName,NULL,$timestamp,NULL,NULL,NULL);
        $sheetPDFFile->setBody($data);

        // create exerciseSheet
        $foundValues = $f->foundValues;
        $sheetName = $foundValues['sheetName'];
        $startDate = strtotime(str_replace(" - ", " ", $foundValues['startDate']));
        $endDate = strtotime(str_replace(" - ", " ", $foundValues['endDate']));
        $groupSize = $foundValues['groupSize'];

        $myExerciseSheet = ExerciseSheet::createExerciseSheet(NULL,$cid,$endDate,$startDate,$groupSize,NULL, NULL,$sheetName);
        $myExerciseSheet->setSheetFile($sheetPDFFile);

        // get sheetSolution if it exists
        if ($_FILES['sheetSolution']['error'] != 4) {
            $filePath = $_FILES['sheetSolution']['tmp_name'];
            $displayName = $_FILES['sheetSolution']['name'];
            $data = file_get_contents($filePath);
            $data = base64_encode($data);

            $sheetSolutionFile = File::createFile(NULL,$displayName,NULL,$timestamp,NULL,NULL,NULL);
            $sheetSolutionFile->setBody($data);
            $myExerciseSheet->setSampleSolution($sheetSolutionFile);
        }

        // encode to JSON
        $myExerciseSheetJSON = ExerciseSheet::encodeExerciseSheet($myExerciseSheet);

        // Post ExcercisSheet to logic Controllers to create it and get saved data
        $output= http_post_data($logicURI."/exercisesheet", $myExerciseSheetJSON, true, $message);
        $output = json_decode($output, true);

        // create subtasks as exercise
        if ($message == 201) {
            foreach ($validatedExercises as $key1 => $exercise) {

                // creation
                $exercises = array();
                foreach ($exercise as $key2 => $subexercise) {

                    // create subexercise object
                    if (isset($output['id'])) {
                        $id = $output['id'];
                    }

                    // set bonus
                    if (preg_match("#[0-9]+b$#", $subexercise['exerciseType']) == true) {
                        $bonus = "1";
                    } else {
                        $bonus = "0";
                    }

                    // create exercise
                    $subexerciseObj = Exercise::createExercise(NULL,$cid,$id, $subexercise['maxPoints'],
                                                               $subexercise['exerciseType'],$key1+1,$bonus,$key2+1);
                    // add attachement if given
                    if ($_FILES['exercises']['error'][$key1]['subexercises'][$key2]['attachment'] != 4) {
                        $filePath = $_FILES['exercises']['tmp_name'][$key1]['subexercises'][$key2]['attachment'];
                        $displayName = $_FILES['exercises']['name'][$key1]['subexercises'][$key2]['attachment'];
                        $data = file_get_contents($filePath);
                        $data = base64_encode($data);

                        $attachementFile = File::createFile(NULL,$displayName,NULL,$timestamp,NULL,NULL,NULL);
                        $attachementFile->setBody($data);
                        $subexerciseObj->setAttachments($attachementFile);
                    }
                    // set FileTypes (only as an array with strings in it)
                    $subexerciseObj->setFileTypes($subexercise['mime-type']);
                    // add subexercise to exercises
                    array_push($exercises, $subexerciseObj);
                }

                // Post Excercise to logic Controller to create it
                $exercisesJSON = Exercise::encodeExercise($exercises);
                $output= http_post_data($logicURI."/exercise", $exercisesJSON, true, $message);

                if ($message != 201) {
                    $errorInSent = true;
                    break;
                }
            }
            if ($errorInSent == false) {
                $errormsg = "Die Serie wurde erstellt.";
                array_push($notifications, MakeNotification('success', $errormsg));
            } else {
                $errormsg = "Beim Erstellen ist ein Fehler aufgetreten.";
                array_push($notifications, MakeNotification('error', $errormsg));
            }
        } else {
            $errormsg = "Beim Erstellen ist ein Fehler aufgetreten.";
            array_push($notifications, MakeNotification('error', $errormsg));
        }
    }  else {
        $notifications = array_merge($notifications, $f->notifications);
    }
}

// construct a new header
$h = Template::WithTemplateFile('include/Header/Header.template.html');
$h->bind($createsheetData['user']);
$h->bind(array("name" => $createsheetData['user']['courses'][0]['course']['name'],
               "notificationElements" => $notifications));

Authentication::checkRights(PRIVILEGE_LEVEL::LECTURER, $cid, $uid, $createsheetData['user']);

$sheetSettings = Template::WithTemplateFile('include/CreateSheet/SheetSettings.template.html');
$createExercise = Template::WithTemplateFile('include/CreateSheet/CreateExercise.template.html');

if (isset($_POST['action']) && $_POST['action'] == "new") {
    // if post failed don't reset form
    if ($ready != true || $noFile != false || $correctExercise != true || $correctDates != true) {
        $sheetSettings->bind($createsheetData['user']);
        $sheetSettings->bind(cleanInput($_POST));

        if (isset($_POST['exercises'])) {
            $exerciseSettings = Template::WithTemplateFile('include/CreateSheet/ExerciseSettings.template.php');
            $exerciseSettings->bind(cleanInput($_POST));

            // wrap all the elements in some HTML and show them on the page
            $w = new HTMLWrapper($h, $sheetSettings, $createExercise, $exerciseSettings);
            $w->defineForm(basename(__FILE__)."?cid=".$cid, true, $sheetSettings, $createExercise, $exerciseSettings);
            $w->set_config_file('include/configs/config_createSheet.json');
            $w->show();
        } else {
            // wrap all the elements in some HTML and show them on the page
            $w = new HTMLWrapper($h, $sheetSettings, $createExercise);
            $w->defineForm(basename(__FILE__)."?cid=".$cid, true, $sheetSettings, $createExercise);
            $w->set_config_file('include/configs/config_createSheet.json');
            $w->show();
        }
    }  else { // otherwise show normal page
        $sheetSettings->bind($createsheetData['user']);

        // wrap all the elements in some HTML and show them on the page
        $w = new HTMLWrapper($h, $sheetSettings, $createExercise);
        $w->defineForm(basename(__FILE__)."?cid=".$cid, true, $sheetSettings, $createExercise);
        $w->set_config_file('include/configs/config_createSheet.json');
        $w->show();
    }
} elseif ($noContent == true) { // show only header and errormessages
    // wrap all the elements in some HTML and show them on the page
    $w = new HTMLWrapper($h);
    $w->set_config_file('include/configs/config_createSheet.json');
    $w->show();
} else { // otherwise show normal page
    $sheetSettings->bind($createsheetData['user']);

    // wrap all the elements in some HTML and show them on the page
    $w = new HTMLWrapper($h, $sheetSettings, $createExercise);
    $w->defineForm(basename(__FILE__)."?cid=".$cid, true, $sheetSettings, $createExercise);
    $w->set_config_file('include/configs/config_createSheet.json');
    $w->show();
}
?>
