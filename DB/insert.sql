-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Course`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Course` (`C_id`, `C_name`, `C_semester`, `C_defaultGroupSize`) VALUES (1, '?Bengalisch II', 'SS 11', 1);
INSERT INTO `uebungsplattform2`.`Course` (`C_id`, `C_name`, `C_semester`, `C_defaultGroupSize`) VALUES (2, 'Fachschaftsseminar fuer Mathematik', 'WS 11/12', 2);
INSERT INTO `uebungsplattform2`.`Course` (`C_id`, `C_name`, `C_semester`, `C_defaultGroupSize`) VALUES (3, 'Kulturgeschichte durch den Magen: Essen in Italien', 'SS 12', 3);
INSERT INTO `uebungsplattform2`.`Course` (`C_id`, `C_name`, `C_semester`, `C_defaultGroupSize`) VALUES (4, 'Maerkte im vor- und nachgelagerten Bereich der Landwirtschaft', 'WS 12/13', 4);
INSERT INTO `uebungsplattform2`.`Course` (`C_id`, `C_name`, `C_semester`, `C_defaultGroupSize`) VALUES (5, 'Portugiesische Literatur nach 2000', 'SS 13', 5);
INSERT INTO `uebungsplattform2`.`Course` (`C_id`, `C_name`, `C_semester`, `C_defaultGroupSize`) VALUES (6, 'Umwelt- und Planungsrecht', 'WS 13/14', 6);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`File`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (1, 'a.pdf', 'file/abcdef', '2013-12-08 00:00:01', 100, 'abcdef');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (2, 'b.pdf', 'file/abcde', '2013-12-07 00:00:01', 200, 'abcde');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (3, 'c.pdf', 'file/abcd', '2013-12-06 00:00:01', 300, 'abcd');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (4, 'd.pdf', 'file/abc', '2013-12-05 00:00:01', 400, 'abc');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (5, 'e.pdf', 'file/ab', '2013-12-04 00:00:01', 500, 'ab');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (6, 'f.pdf', 'file/a', '2013-12-03 00:00:01', 600, 'a');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (7, 'g.pdf', 'file/abcdefg', '2013-12-02 00:00:01', 700, 'abcdefg');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (8, 'h.pdf', 'file/abcdefgh', '2013-12-01 00:00:01', 800, 'abcdefgh');
INSERT INTO `uebungsplattform2`.`File` (`F_id`, `F_displayName`, `F_address`, `F_timeStamp`, `F_fileSize`, `F_hash`) VALUES (9, 'i.pdf', 'file/delete', '2013-12-01 00:00:01', 900, 'delete');

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`User`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`User` (`U_id`, `U_username`, `U_email`, `U_lastName`, `U_firstName`, `U_title`, `U_password`, `U_flag`, `U_oldUsername`) VALUES (4, 'till', 'till@email.de', 'Uhlig', 'Till', NULL, 'test', 1, NULL);
INSERT INTO `uebungsplattform2`.`User` (`U_id`, `U_username`, `U_email`, `U_lastName`, `U_firstName`, `U_title`, `U_password`, `U_flag`, `U_oldUsername`) VALUES (2, 'lisa', 'lisa@email.de', 'Dietrich', 'Lisa', NULL, 'test', 1, NULL);
INSERT INTO `uebungsplattform2`.`User` (`U_id`, `U_username`, `U_email`, `U_lastName`, `U_firstName`, `U_title`, `U_password`, `U_flag`, `U_oldUsername`) VALUES (3, 'joerg', 'joerg@email.de', 'Baumgarten', 'Joerg', NULL, 'test', 1, NULL);
INSERT INTO `uebungsplattform2`.`User` (`U_id`, `U_username`, `U_email`, `U_lastName`, `U_firstName`, `U_title`, `U_password`, `U_flag`, `U_oldUsername`) VALUES (1, 'super-admin', NULL, NULL, NULL, NULL, 'test', 1, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`ExerciseSheet`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (1, 1, 1, 8, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, NULL);
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (1, 2, 2, 1, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, 'Bonus');
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (2, 3, 3, 2, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, NULL);
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (2, 4, 4, 3, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, 'Zusatz');
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (4, 5, 5, 4, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, NULL);
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (5, 6, 6, 5, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, 'Weihnachtsaufgabe');
INSERT INTO `uebungsplattform2`.`ExerciseSheet` (`C_id`, `ES_id`, `F_id_sampleSolution`, `F_id_file`, `ES_startDate`, `ES_endDate`, `ES_groupSize`, `ES_name`) VALUES (5, 7, 7, 6, '2013-12-02 00:00:01', '2013-12-31 23:59:59', 1, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Group`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Group` (`U_id_leader`, `U_id_member`, `C_id`, `ES_id`) VALUES (1, 1, NULL, 1);
INSERT INTO `uebungsplattform2`.`Group` (`U_id_leader`, `U_id_member`, `C_id`, `ES_id`) VALUES (2, 1, NULL, 1);
INSERT INTO `uebungsplattform2`.`Group` (`U_id_leader`, `U_id_member`, `C_id`, `ES_id`) VALUES (3, 1, NULL, 1);
INSERT INTO `uebungsplattform2`.`Group` (`U_id_leader`, `U_id_member`, `C_id`, `ES_id`) VALUES (1, 1, NULL, 2);
INSERT INTO `uebungsplattform2`.`Group` (`U_id_leader`, `U_id_member`, `C_id`, `ES_id`) VALUES (2, 2, NULL, 2);
INSERT INTO `uebungsplattform2`.`Group` (`U_id_leader`, `U_id_member`, `C_id`, `ES_id`) VALUES (3, 3, NULL, 2);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Invitation`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Invitation` (`U_id_leader`, `U_id_member`, `ES_id`) VALUES (1, 2, 1);
INSERT INTO `uebungsplattform2`.`Invitation` (`U_id_leader`, `U_id_member`, `ES_id`) VALUES (1, 3, 1);
INSERT INTO `uebungsplattform2`.`Invitation` (`U_id_leader`, `U_id_member`, `ES_id`) VALUES (2, 1, 2);
INSERT INTO `uebungsplattform2`.`Invitation` (`U_id_leader`, `U_id_member`, `ES_id`) VALUES (3, 2, 2);
INSERT INTO `uebungsplattform2`.`Invitation` (`U_id_leader`, `U_id_member`, `ES_id`) VALUES (4, 2, 1);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`CourseStatus`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (1, 2, 0);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (1, 3, 1);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (1, 4, 3);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (2, 2, 3);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (2, 3, 0);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (2, 4, 1);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (3, 2, 0);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (3, 3, 3);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (4, 2, 3);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (4, 3, 1);
INSERT INTO `uebungsplattform2`.`CourseStatus` (`C_id`, `U_id`, `CS_status`) VALUES (4, 4, 0);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`ExerciseType`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`ExerciseType` (`ET_id`, `ET_name`) VALUES (1, 'Theorie');
INSERT INTO `uebungsplattform2`.`ExerciseType` (`ET_id`, `ET_name`) VALUES (2, 'Praxis');
INSERT INTO `uebungsplattform2`.`ExerciseType` (`ET_id`, `ET_name`) VALUES (3, 'Klausur');

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Exercise`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Exercise` (`C_id`, `E_id`, `ES_id`, `ET_id`, `E_maxPoints`, `E_bonus`, `E_id_link`) VALUES (NULL, 1, 1, 1, 5, true, 1);
INSERT INTO `uebungsplattform2`.`Exercise` (`C_id`, `E_id`, `ES_id`, `ET_id`, `E_maxPoints`, `E_bonus`, `E_id_link`) VALUES (NULL, 2, 1, 2, 10, false, 1);
INSERT INTO `uebungsplattform2`.`Exercise` (`C_id`, `E_id`, `ES_id`, `ET_id`, `E_maxPoints`, `E_bonus`, `E_id_link`) VALUES (NULL, 3, 2, 3, 12, false, 3);
INSERT INTO `uebungsplattform2`.`Exercise` (`C_id`, `E_id`, `ES_id`, `ET_id`, `E_maxPoints`, `E_bonus`, `E_id_link`) VALUES (NULL, 4, 3, 1, 15, true, 4);
INSERT INTO `uebungsplattform2`.`Exercise` (`C_id`, `E_id`, `ES_id`, `ET_id`, `E_maxPoints`, `E_bonus`, `E_id_link`) VALUES (NULL, 5, 4, 2, 18, false, 5);
INSERT INTO `uebungsplattform2`.`Exercise` (`C_id`, `E_id`, `ES_id`, `ET_id`, `E_maxPoints`, `E_bonus`, `E_id_link`) VALUES (NULL, 6, 4, 3, 20, true, 6);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Submission`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (1, 1, 1, 'eins', '2013-12-03 00:00:01', true, 1, NULL);
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (1, 2, 2, 'zwei', '2013-12-04 00:00:01', true, 1, NULL);
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (2, 3, 3, 'drei', '2013-12-01 00:00:01', true, 1, NULL);
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (2, 4, 4, 'vier', '2013-11-02 00:00:01', true, 2, NULL);
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (3, 5, 5, 'fuenf', '2013-12-02 00:00:01', true, 2, NULL);
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (4, 6, 6, 'sechs', '2013-12-02 00:00:01', true, 2, NULL);
INSERT INTO `uebungsplattform2`.`Submission` (`U_id`, `S_id`, `F_id_file`, `S_comment`, `S_date`, `S_accepted`, `E_id`, `ES_id`) VALUES (4, 7, 9, 'sieben', '2013-12-02 00:00:01', true, 2, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Marking`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Marking` (`M_id`, `U_id_tutor`, `F_id_file`, `S_id`, `M_tutorComment`, `M_outstanding`, `M_status`, `M_points`, `M_date`, `E_id`, `ES_id`) VALUES (1, 2, 8, 1, 'nichts', false, 0, 10, '2013-12-08 00:00:01', NULL, NULL);
INSERT INTO `uebungsplattform2`.`Marking` (`M_id`, `U_id_tutor`, `F_id_file`, `S_id`, `M_tutorComment`, `M_outstanding`, `M_status`, `M_points`, `M_date`, `E_id`, `ES_id`) VALUES (2, 2, 7, 2, 'nichts', true, 0, 12, '2013-12-08 00:00:01', NULL, NULL);
INSERT INTO `uebungsplattform2`.`Marking` (`M_id`, `U_id_tutor`, `F_id_file`, `S_id`, `M_tutorComment`, `M_outstanding`, `M_status`, `M_points`, `M_date`, `E_id`, `ES_id`) VALUES (3, 1, 6, 3, 'nichts', false, 0, 13, '2013-12-08 00:00:01', NULL, NULL);
INSERT INTO `uebungsplattform2`.`Marking` (`M_id`, `U_id_tutor`, `F_id_file`, `S_id`, `M_tutorComment`, `M_outstanding`, `M_status`, `M_points`, `M_date`, `E_id`, `ES_id`) VALUES (4, 1, 5, 4, 'nichts', true, 0, 14, '2013-12-08 00:00:01', NULL, NULL);
INSERT INTO `uebungsplattform2`.`Marking` (`M_id`, `U_id_tutor`, `F_id_file`, `S_id`, `M_tutorComment`, `M_outstanding`, `M_status`, `M_points`, `M_date`, `E_id`, `ES_id`) VALUES (5, 4, 4, 5, 'nichts', false, 0, 15, '2013-12-08 00:00:01', NULL, NULL);
INSERT INTO `uebungsplattform2`.`Marking` (`M_id`, `U_id_tutor`, `F_id_file`, `S_id`, `M_tutorComment`, `M_outstanding`, `M_status`, `M_points`, `M_date`, `E_id`, `ES_id`) VALUES (6, 4, 3, 6, 'nichts', true, 0, 16, '2013-12-08 00:00:01', NULL, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Attachment`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Attachment` (`A_id`, `E_id`, `F_id`, `ES_id`) VALUES (1, 1, 1, NULL);
INSERT INTO `uebungsplattform2`.`Attachment` (`A_id`, `E_id`, `F_id`, `ES_id`) VALUES (2, 2, 2, NULL);
INSERT INTO `uebungsplattform2`.`Attachment` (`A_id`, `E_id`, `F_id`, `ES_id`) VALUES (3, 3, 3, NULL);
INSERT INTO `uebungsplattform2`.`Attachment` (`A_id`, `E_id`, `F_id`, `ES_id`) VALUES (4, 4, 4, NULL);
INSERT INTO `uebungsplattform2`.`Attachment` (`A_id`, `E_id`, `F_id`, `ES_id`) VALUES (5, 1, 5, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`ApprovalCondition`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`ApprovalCondition` (`AC_id`, `C_id`, `ET_id`, `AC_percentage`) VALUES (1, 1, 1, 0.5);
INSERT INTO `uebungsplattform2`.`ApprovalCondition` (`AC_id`, `C_id`, `ET_id`, `AC_percentage`) VALUES (2, 2, 2, 0.5);
INSERT INTO `uebungsplattform2`.`ApprovalCondition` (`AC_id`, `C_id`, `ET_id`, `AC_percentage`) VALUES (3, 3, 3, 0.6);
INSERT INTO `uebungsplattform2`.`ApprovalCondition` (`AC_id`, `C_id`, `ET_id`, `AC_percentage`) VALUES (4, 4, 1, 0.6);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`SelectedSubmission`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`SelectedSubmission` (`U_id_leader`, `S_id_selected`, `E_id`, `ES_id`) VALUES (1, 2, 1, NULL);
INSERT INTO `uebungsplattform2`.`SelectedSubmission` (`U_id_leader`, `S_id_selected`, `E_id`, `ES_id`) VALUES (2, 3, 3, NULL);
INSERT INTO `uebungsplattform2`.`SelectedSubmission` (`U_id_leader`, `S_id_selected`, `E_id`, `ES_id`) VALUES (3, 6, 2, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Component`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (1, 'FSControl', 'localhost/uebungsplattform/FS/FSControl', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (2, 'FSFile', 'localhost/uebungsplattform/FS/FSFile', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (3, 'FSZip', 'localhost/uebungsplattform/FS/FSZip', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (4, 'DBControl', 'localhost/uebungsplattform/DB/DBControl', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (5, 'FSBinder', 'localhost/uebungsplattform/FS/FSBinder', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (6, 'DBCourse', 'localhost/uebungsplattform/DB/DBCourse', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (7, 'DBUser', 'localhost/uebungsplattform/DB/DBUser', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (8, 'DBAttachment', 'localhost/uebungsplattform/DB/DBAttachment', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (9, 'DBExercise', 'localhost/uebungsplattform/DB/DBExercise', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (10, 'DBExerciseSheet', 'localhost/uebungsplattform/DB/DBExerciseSheet', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (11, 'DBExerciseType', 'localhost/uebungsplattform/DB/DBExerciseType', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (12, 'DBFile', 'localhost/uebungsplattform/DB/DBFile', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (13, 'DBQuery', 'localhost/uebungsplattform/DB/DBQuery', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (14, 'LController', 'localhost/uebungsplattform/logic/Controller/LController.php', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (15, 'LCourse', 'localhost/uebungsplattform/logic/Course/course.php', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (16, 'LGroup', 'localhost/uebungsplattform/logic/Group/Group.php', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (17, 'LUser', 'localhost/uebungsplattform/logic/User/user.php', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (18, 'DBCourseStatus', 'localhost/uebungsplattform/DB/DBCourseStatus', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (19, 'FSPdf', 'localhost/uebungsplattform/FS/FSPdf', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (20, 'DBExternalId', 'localhost/uebungsplattform/DB/DBExternalId', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (21, 'DBApprovalCondition', 'localhost/uebungsplattform/DB/DBApprovalCondition', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (22, 'DBGroup', 'localhost/uebungsplattform/DB/DBGroup', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (23, 'DBInvitation', 'localhost/uebungsplattform/DB/DBInvitation', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (24, 'DBMarking', 'localhost/uebungsplattform/DB/DBMarking', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (25, 'DBSession', 'localhost/uebungsplattform/DB/DBSession', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (26, 'DBSubmission', 'localhost/uebungsplattform/DB/DBSubmission', '');
INSERT INTO `uebungsplattform2`.`Component` (`CO_id`, `CO_name`, `CO_address`, `CO_option`) VALUES (27, 'DBSelectedSubmission', 'localhost/uebungsplattform/DB/DBSelectedSubmission', '');

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`ComponentLinkage`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (1, 3, 'getFile', '', 1);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (2, 1, 'out1', '', 2);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (3, 1, 'out', '', 3);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (4, 3, 'out', '0-f', 5);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (5, 2, 'out', '0-f', 5);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (6, 6, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (7, 7, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (8, 8, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (9, 9, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (10, 10, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (11, 11, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (12, 12, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (13, 4, 'out', '', 6);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (14, 4, 'out', '', 7);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (15, 4, 'out', '', 8);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (16, 4, 'out', '', 9);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (17, 4, 'out', '', 10);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (18, 4, 'out', '', 11);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (19, 4, 'out', '', 12);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (20, 4, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (21, 14, 'course', '', 15);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (22, 14, 'group', '', 16);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (23, 14, 'user', '', 17);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (24, 14, 'DB', '', 4);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (25, 14, 'FS', '', 1);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (26, 15, 'out', '', 14);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (27, 16, 'out', '', 14);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (28, 17, 'out', '', 14);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (29, 19, 'out', '0-f', 5);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (30, 1, 'out3', '', 19);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (31, 4, 'out', '', 25);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (32, 4, 'out', '', 20);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (33, 25, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (40, 20, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (34, 4, 'out', '', 21);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (35, 4, 'out', '', 22);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (36, 4, 'out', '', 23);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (37, 21, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (38, 22, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (39, 23, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (41, 4, 'out', '', 24);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (42, 24, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (43, 4, 'out', '', 18);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (44, 18, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (45, 4, 'out', '', 27);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (46, 27, 'out', '', 13);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (47, 4, 'out', '', 26);
INSERT INTO `uebungsplattform2`.`ComponentLinkage` (`CL_id`, `CO_id_owner`, `CL_name`, `CL_relevanz`, `CO_id_target`) VALUES (48, 26, 'out', '', 13);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`ExternalId`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`ExternalId` (`EX_id`, `C_id`) VALUES ('Ver1', 1);
INSERT INTO `uebungsplattform2`.`ExternalId` (`EX_id`, `C_id`) VALUES ('Ver12', 1);
INSERT INTO `uebungsplattform2`.`ExternalId` (`EX_id`, `C_id`) VALUES ('Ver2', 2);
INSERT INTO `uebungsplattform2`.`ExternalId` (`EX_id`, `C_id`) VALUES ('Ver3', 3);
INSERT INTO `uebungsplattform2`.`ExternalId` (`EX_id`, `C_id`) VALUES ('Ver4', 4);
INSERT INTO `uebungsplattform2`.`ExternalId` (`EX_id`, `C_id`) VALUES ('Ver5', 5);

COMMIT;


-- -----------------------------------------------------
-- Data for table `uebungsplattform2`.`Session`
-- -----------------------------------------------------
START TRANSACTION;
USE `uebungsplattform2`;
INSERT INTO `uebungsplattform2`.`Session` (`U_id`, `SE_sessionID`) VALUES (1, 'abcd');
INSERT INTO `uebungsplattform2`.`Session` (`U_id`, `SE_sessionID`) VALUES (3, 'abc');
INSERT INTO `uebungsplattform2`.`Session` (`U_id`, `SE_sessionID`) VALUES (4, 'ab');

COMMIT;