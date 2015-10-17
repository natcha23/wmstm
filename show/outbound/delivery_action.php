<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');
$db = DB();

$rt_id 			= $_REQUEST['rtID'];
$organize_id 	= $_REQUEST['organize_id'];
$now 			= date("Y-m-d H:i:s");
$user_id 		= $_SESSION['userID'];

// _print($_REQUEST);exit;
/* Update confirm car */
// $DOID = genDOCID("delivery order", "DO");
$db->select("*")->from("outbound_car")->where(array(
		"status_process" => 0,
		'organize_truck_id' => $organize_id
));
$sql = $db->get();
// _print($db->last_query());exit;
$outcararr = $sql->result_array();
// _print($outcararr);exit;
if(!empty($outcararr)) {
	$docID = genDOCID('delivery order', 'DO');
	foreach($outcararr as $car_id) {
		
		$carArr[] = $car_id['out_car_id'];
		$rtArr[] = $car_id['outbound_rt'];

		$carfields = array(
				"status_process" => 1,
				"car_id" => (is_numeric($_POST['car_id']))?$_POST['car_id']:-1,
				"other_car_code" => ($_POST['other_car_code'])?$_POST['other_car_code']:null,
				"user_id" => $user_id,
				"driver_id" => (is_numeric($_POST['driver_id']))?$_POST['driver_id']:-1,
				"driver_name" => $_POST['driver_name'],
				"date_time" => $now
		);
		/* Update outbound rt status */
		$where_update = array('out_car_id' => $car_id['out_car_id']);
		$db->update("outbound_car", $carfields, $where_update);
		
// 		_print($db->last_query());exit;
		$db->select("delivery_order_id")->from("outbound_rt_status");
		$db->where("rt_id = '{$car_id['outbound_rt']}'");
		$db->where("status = '4'");
// 		$db->where("(delivery_order_id = '' OR delivery_order_id = 'NULL')");
		$sql = $db->get();
// 		_print($db->last_query());
		$count = $sql->num_rows();
// 		_print($count);exit;
		
		if( !empty($count) || $count > 0){
			
			/* update rt status */
			$status = array();
			$status = array("status" => 5,
					"user_id" => $user_id,
					"update_time" => $now,
					"delivery_order_id" => $docID,
					"time_out_car" => $now
			);
			$statuswhere = array("rt_id" => $car_id['outbound_rt']);
			$db->update("outbound_rt_status", $status, $statuswhere);
		}
	}
// 	if(empty($carArr)) {
		// 		$db->update("outbound_car", array("delivery_order_id" => $DOID), "out_car_id IN ('".implode("','", $carArr)."')");
// 	}

}
// _print($rtArr);
/* Stock */
foreach($rtArr AS $rtid) {
	$db->select("chk.id, barcode, check_qty");
	$db->from("outbound_rt AS rt");
	$db->join("outbound_check AS chk", "rt.id = chk.outbound_id", "LEFT");
	$db->where(array("rt.rt_refid"=>$rtid));
	$db->where('check_qty > 0');
	
	$sql = $db->get();
// 	_print($db->last_query());
	$barcodeArr = array();
	$barcodeArr = $sql->result_array();
// 	_print($barcodeArr);
	foreach($barcodeArr as $item) {
		
		/* update stock_product*/
		$where = array("product_id" => $item['barcode']);
		$db->select("product_qty")->from("stock_product");
		$db->where($where);
		$sql = $db->get();
// 		_print($db->last_query());
		$result = $sql->row();
		$product_qty = $result->product_qty;
		
// 		_print($product_qty);
		$data=array();
		if(!empty($product_qty) || $product_qty > 0) {
			$data['product_qty'] = $product_qty - $item['check_qty'];
			$data['product_update'] = $now;
			$data['user_id'] = $user_id;
			$db->update("stock_product", $data, $where);
		}
// 		_print($db->last_query());
	}
}
exit;




