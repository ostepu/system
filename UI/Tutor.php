<?php
include_once 'include/Header/Header.php';
include_once 'include/HTMLWrapper.php';
include_once 'include/Template.php';

// construct a new header
$h = new Header("Datenstrukturen",
                "",
                "Florian Lücke",
                "Kontrolleur");

// construct some exercise sheets
$sheetString = file_get_contents("http://localhost/Uebungsplattform/UI/Data/SheetData");

// convert the json string into an associative array
$sheets = json_decode($sheetString, true);

$t = Template::WithTemplateFile('include/ExerciseSheet/ExerciseSheetTutor.template.html');

$t->bind($sheets);

$w = new HTMLWrapper($h, $t);
$w->set_config_file('include/configs/config_student_tutor.json');
$w->show();
?>
