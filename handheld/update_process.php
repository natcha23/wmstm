<?php
// require_once('../../config.php');
// require_once('../../class_my.php');
// require_once('../../func.php');
// require_once('../../helper/class_upload.php');

// $db = DB();
$now = date("Y-m-d H:i:s");
$user_id = $_SESSION['userID'];

// _print($_POST);exit;

if($_POST['method'] == 'send-to-checking-out') {
	$where = array('rt_id' => $_POST['rt_id']);
	$update_fields = array('status' => 1,
			'user_id' => $user_id,
			'update_time' => $now);
	$db->update('outbound_rt_status', $update_fields, $where);
}

if($_POST['method'] == 'send-to-transport') {
	$where = array('rt_id' => $_POST['rt_id']);
	$update_fields = array('status' => 3,
			'user_id' => $user_id,
			'update_time' => $now);
	$db->update('outbound_rt_status', $update_fields, $where);
}
// _print($db->last_query());

die();

?>
