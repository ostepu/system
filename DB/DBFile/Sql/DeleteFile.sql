/** 
 * @file DeleteFile.sql
 * deletes a specified file from %File table
 * @author  Till Uhlig
 * @param int \$fileid a %File identifier
 * @result -
 */
call deleteFile('$fileid'); 