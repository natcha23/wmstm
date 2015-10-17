<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();

if(isset($_POST['id'])){
	$db->select('user_username')->from('user')->where(array('user_id !='=>$_POST['id'],'user_username'=>$_POST['regist_username'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();

	$json = array('row'=>$row);
	echo json_encode($json);
}else{
	$db->select('user_username')->from('user')->where(array('user_username'=>$_POST['regist_username'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();
	$json = array('row'=>$row);
	echo json_encode($json);
}
?>