DROP PROCEDURE IF EXISTS `DBGateGetGateProfilesByAuth`;
CREATE PROCEDURE `DBGateGetGateProfilesByAuth` (IN profile varchar(30), IN authProfile varchar(30), IN ruleProfile varchar(30), IN authType varchar(120))
READS SQL DATA
begin
SET @s = concat("
select SQL_CACHE
    GP.*,
    GR.GR_id,
    GR.GR_type,
    GR.GR_component,
    GR.GR_content,
    GA.GA_id,
    GA.GA_type,
    GA.GA_params,
    GA.GA_login,
    GA.GA_passwd
FROM
    `GateAuth",authProfile,"` GA
        join
    `GateProfile",profile,"` GP ON (GP.GP_id = GA.GP_id)
        left join
    `GateRule",ruleProfile,"` GR ON (GP.GP_id = GR.GP_id)
WHERE
    GA.GA_type = '",authType,"';");
PREPARE stmt1 FROM @s;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;
end;