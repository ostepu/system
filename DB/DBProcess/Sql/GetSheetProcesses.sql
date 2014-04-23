/**
 * @file GetSheetProcesses.sql
 * gets processes from %Process table
 * @author Till Uhlig
 * @param int \$esid an %Sheet identifier
 * @result 
 * - PRO, the process data
 * - CO, the component data
 */
 
SET @course = (select E.C_id from `Exercise` E where E.ES_id = {$esid} limit 1);
SET @statement = 
concat(
\"select 
    concat('\", @course ,\"','_',PRO.PRO_id) as PRO_id,
    PRO.E_id,
    PRO.PRO_parameter,
    PRO.CO_id_target,
    CO.CO_id,
    CO.CO_name,
    CO.CO_address
from
    `Process_\", @course, \"` PRO
        left join
    `Component` CO ON PRO.CO_id_target = CO.CO_id
where
    PRO.ES_id = '{$esid}'\");

PREPARE stmt1 FROM @statement;
EXECUTE stmt1;