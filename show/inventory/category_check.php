<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();

if(isset($_POST['id'])){
	$db->select('cat_name')->from('tb_cat')->where(array('cat_id !='=>$_POST['id'],'cat_name'=>$_POST['cat_name'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();

	$json = array('row'=>$row);
	echo json_encode($json);
}else{
	$db->select('cat_name')->from('tb_cat')->where(array('cat_name'=>$_POST['cat_name'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();
	$json = array('row'=>$row);
	echo json_encode($json);
}
?>