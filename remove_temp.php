<?php
require_once('config.php');
require_once('class_my.php');
require_once('func.php');
$db = DB();
$db->select('PhysicalName')->from('files_upload')->where(array('ID'=>$_POST['id']));
$sql = $db->get();
$arr = $sql->row_array();

unlink('upload/'.$arr['PhysicalName']);

$db->delete('files_upload',array('id'=>$_POST['id']));
?>