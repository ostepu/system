<?php
/**
 * @file GetIncreaseUserFailedLogin.sql
 * increases the failed logins counter and returns the user
 * @author Till Uhlig
 * @param int \$userid a %User identifier
 * @result 
 * - U, the user data
 * - CS, the course status data
 * - C, the course data
 */
?>

UPDATE `User` SET U_failed_logins='<?php echo time(); ?>'
where U_id = '<?php echo $userid; ?>' or U_username = '<?php echo $userid; ?>' or U_externalId = '<?php echo $userid; ?>';

SELECT 
    U.U_id,
    U.U_username,
    U.U_firstName,
    U.U_lastName,
    U.U_email,
    U.U_title,
    U.U_flag,
    U.U_password,
    U.U_salt,
    U.U_failed_logins,
    U.U_externalId,
    U.U_studentNumber,
    CS.CS_status,
    C.C_id,
    C.C_name,
    C.C_semester,
    C.C_defaultGroupSize
FROM
    User U
        left join
    CourseStatus CS ON (U.U_id = CS.U_id)
        left join
    Course C ON (CS.C_id = C.C_id)
WHERE
    U.U_id like '<?php echo $userid; ?>' or U_username = '<?php echo $userid; ?>' or U_externalId = '<?php echo $userid; ?>';