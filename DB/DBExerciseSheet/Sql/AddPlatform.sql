SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `ExerciseSheet` (
  `C_id` INT NOT NULL,
  `ES_id` INT NOT NULL AUTO_INCREMENT,
  `F_id_sampleSolution` INT NULL,
  `F_id_file` INT NULL,
  `ES_startDate` BIGINT NULL DEFAULT 0,
  `ES_endDate` BIGINT NULL DEFAULT 0,
  `ES_groupSize` INT NULL DEFAULT 1,
  `ES_name` VARCHAR(120) NULL,
  PRIMARY KEY (`ES_id`),
  UNIQUE INDEX `ES_id_UNIQUE` (`ES_id` ASC),
  CONSTRAINT `fk_ExerciseSheet_Course1`
    FOREIGN KEY (`C_id`)
    REFERENCES `Course` (`C_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ExerciseSheet_File1`
    FOREIGN KEY (`F_id_sampleSolution`)
    REFERENCES `File` (`F_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ExerciseSheet_File2`
    FOREIGN KEY (`F_id_file`)
    REFERENCES `File` (`F_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

DROP TRIGGER IF EXISTS `ExerciseSheet_AINS`;
CREATE TRIGGER `ExerciseSheet_AINS` AFTER INSERT ON `ExerciseSheet` FOR EACH ROW
<?php
/*insert new group for every exerciseSheet
@author Lisa*/
?>
begin
INSERT IGNORE INTO `Group` 
SELECT C.U_id , C.U_id , null , NEW.ES_id 
FROM CourseStatus C
WHERE C.C_id = NEW.C_id and C.CS_status = 0 ;
end;

DROP TRIGGER IF EXISTS `ExerciseSheet_BINS`;
CREATE TRIGGER `ExerciseSheet_BINS` BEFORE INSERT ON `ExerciseSheet` FOR EACH ROW
<?php
/*check if groupsize exists
@if not take default groupsize from course
@author Lisa*/
?>
begin
IF NEW.ES_groupSize is null 
then Set NEW.ES_groupSize = (SELECT C_defaultGroupSize FROM Course WHERE C_id = NEW.C_id limit 1);
end if;

if (NEW.ES_groupSize is NULL) then
SIGNAL sqlstate '23000' set message_text = 'no corresponding course for exercisesheet';
END if;
end;
<?php include $sqlPath.'/procedures/GetCourseExercises.sql'; ?>
<?php include $sqlPath.'/procedures/GetCourseSheets.sql'; ?>
<?php include $sqlPath.'/procedures/GetCourseSheetURLs.sql'; ?>
<?php include $sqlPath.'/procedures/GetExerciseSheet.sql'; ?>
<?php include $sqlPath.'/procedures/GetExerciseSheetURL.sql'; ?>
<?php include $sqlPath.'/procedures/GetSheetExercises.sql'; ?>
<?php include $sqlPath.'/procedures/GetExistsPlatform.sql'; ?>