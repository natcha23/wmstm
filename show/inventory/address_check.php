<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();

if(isset($_POST['id'])){
	$db->select('add_name')->from('tb_address')->where(array('add_id !='=>$_POST['id'],'add_name'=>$_POST['add_name'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();

	$json = array('row'=>$row);
	echo json_encode($json);
}else{
	$db->select('add_name')->from('tb_address')->where(array('add_name'=>$_POST['add_name'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();
	$json = array('row'=>$row);
	echo json_encode($json);
}
?>