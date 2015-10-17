<?php 
require_once('config.php');
require_once('class_my.php');
require_once('func.php');
$db = DB();

$user_id = $_SESSION['userID'];
$now = date("Y-m-d H:i:s");

_print($_POST);exit;

// insert to DB (mySQL)
if(isset($_POST['mode']) && $_POST['mode'] == "save") {
	
	$data = $_POST['data'];
	
	if(!empty($data)) {
		
		foreach($data as $key => $field) {
			$row = array();
			$row['user_id'] = $user_id;
			$row['rt_id'] = $field['rt_id'];
			$row['barcode'] = $field['barcode'];
			$row['goods_name'] = $field['goods_name'];
			$row['rt_qty'] = $field['rt_qty'];
			$row['unit'] = $field['unit'];
			$row['qty_amount'] = $field['qty_amount'];
			$row['check_status'] = $field['check_status'];
			$row['remark_chk'] = $field['remark_chk'];
			$row['date_create'] = $now;
			$row['date_update'] = $now;
			
			/* Insert & Update outbound_check table */
			if( empty($field['id']) ) {
				$db->insert("outbound_check", $row);
				$last_id = $db->insert_id();
			} else {
				$id = ($last_id)? $last_id:$field['id'];
				unset($row['date_create']);
				$where = array("id" => $id);
				$db->update("outbound_check", $row, $where);
			}
		}
		
	}
}

echo 'success';
die;
?>