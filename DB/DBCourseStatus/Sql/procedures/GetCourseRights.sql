DROP PROCEDURE IF EXISTS `DBCourseStatusGetCourseRights`;
CREATE PROCEDURE `DBCourseStatusGetCourseRights` (IN courseid INT)
READS SQL DATA
begin
SET @s = concat("
select SQL_CACHE
    U.U_id,
    U.U_username,
    U.U_firstName,
    U.U_lastName,
    U.U_email,
    U.U_title,
    U.U_flag,
    U.U_studentNumber,
    U.U_isSuperAdmin,
    U.U_comment,
    CS.CS_status,
    C.C_id,
    C.C_name,
    C.C_semester,
    C.C_defaultGroupSize,
    S.*
from
    CourseStatus CS 
        join
    Course C ON (CS.C_id = C.C_id)
 join
    User U
       ON (U.U_id = CS.U_id)
        left join 
    Setting_",courseid," S ON (1)
WHERE
    CS.C_id = '",courseid,"';");
PREPARE stmt1 FROM @s;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;
end;