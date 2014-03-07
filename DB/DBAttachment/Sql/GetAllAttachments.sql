/**
 * @file GetAllAttachments.sql
 * gets all attachments from %Attachment table
 * @author Till Uhlig
 * @result 
 * - A, the attachment data
 * - F, the attachment file
 */
 
select 
    A.A_id,
    A.E_id,
    F.F_id,
    F.F_displayName,
    F.F_address,
    F.F_timeStamp,
    F.F_fileSize,
    F.F_comment,
    F.F_hash
from
    Attachment A
        left join
    File F ON F.F_id = A.F_id