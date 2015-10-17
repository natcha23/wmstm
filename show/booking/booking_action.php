<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();

$action = $_POST['method'];
$now = date("Y-m-d H:i:s");
$user_id = $_SESSION['userID'];

/* Delivery Update */
if($action == 'delivery_update') {

	$data = $_POST['data'][0];
	if(isset($data['checked'])) {
		$fields['delivery_date'] = $now;
		$fields['user_id'] = $user_id;
		$fields['date_update'] = $now;
		if($data['checked'] == 1) {
			$fields['delivery_status'] = 1;

		} else {
			$fields['delivery_status'] = 0;
		}
	}
	$key_id = ($last_id)? $last_id : $data['book_id'];
	$where = array("book_id" => $key_id);
	$db->update("inbound_booking", $fields, $where);
	echo ($last_id)? $last_id : $data['book_id'];
	unset($data);
}

/* Booking from Supplier */
if($action == 'bookingact') {
	$data = $_POST['data'][0];
	$fields = array();
	$fields['po_id'] = $data['po_id'];
	$fields['booking_status'] = 0;
	$fields['booking_date'] = $data['booking'];
	$fields['user_id'] = $user_id;
	$fields['date_create'] = $now;
	$fields['date_update'] = $now;

	if(empty($data['book_id']) ) {
		$db->insert("inbound_booking", $fields);
		$last_id = $db->insert_id();
	} else {
		$key_id = ($last_id)? $last_id : $data['book_id'];
		$where = array("book_id" => $key_id);
		unset($fields['date_create']);
		$db->update("inbound_booking", $fields, $where);
	}
	echo ($last_id)? $last_id : $data['book_id'];
}

/* Booking confirm */
if($action == 'booking_confirm_status'){
    $poID = $_POST['poID'];
    $status = $_POST['confirm'];
    $db->update('inbound_booking',array('booking_status'=>$status,'user_confirm'=>$user_id,'booking_status_update'=>_DATE_TIME_),array('po_id'=>$poID));
    if($db->affected_rows() > 0){
        $data['status'] = 1;
    }else{
        $data['status'] = 0;
    }
    echo json_encode($data);
}

?>
