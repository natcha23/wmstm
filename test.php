<?php
$myServer = 'thaimart';//IP:172.17.1.11
//$myServer = 'thaimart_real';//IP:172.17.1.1
$myUser = 'auditbplus';
$myPass = 'Audit1234';
$myDb = 'TMC_TEST';

$link2 = mssql_connect($myServer, $myUser, $myPass, true) or die ('error');
mssql_select_db($myDb, $link2);

$z = mssql_query('SELECT TOP 100 * from DOCINFO');

while($row = mssql_fetch_array($z)){

echo $row[2].'<br />';
}
mssql_free_result($z);
?>