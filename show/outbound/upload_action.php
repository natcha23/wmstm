<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');

$db = DB();
$filesid = $_POST['temp_id'];
$rt_id = $_POST['rt_id'];
$now = date("Y-m-d H:i:s");
$user_id = $_SESSION['userID'];
$mode = $_POST['action'];

// _print($_POST);
// exit;

/* Delete File */
if($mode == "del_file") {
	$where = array("id" => $_POST['id']);
	$updated = array("status" => 1);
	$db->update($_POST['table'], $updated, $where);
} else {
	if( empty($filesid) ) {
		/* Update File Download */	
		foreach($filesid as $file_id) {
			$fields = array(
					"rt_id" => $rt_id,
					"file_id" => $file_id,
					"status_process" => 5,
					"creater_time" => $now,
					"user_id" => $user_id
			);
			
			$db->insert("outbound_upload", $fields);
			$last_id = $db->insert_id();
		}
	}
	

	
}
?>

