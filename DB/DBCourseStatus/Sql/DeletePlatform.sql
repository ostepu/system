SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP TABLE IF EXISTS `CourseStatus`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

DROP TRIGGER IF EXISTS `CourseStatus_AINS`;
DROP TRIGGER IF EXISTS `CourseStatus_AUPD`;
DROP PROCEDURE IF EXISTS `DBCourseStatusGetMemberRights`;
DROP PROCEDURE IF EXISTS `DBCourseStatusGetCourseRights`;
DROP PROCEDURE IF EXISTS `DBCourseStatusGetExistsPlatform`;
DROP PROCEDURE IF EXISTS `DBCourseStatusGetMemberRight`;