<?php
/*
 * 1.หาจำนวนสินค้าเสียที่เกิดจาก RT เป้าหมาย  report_missing ด้วยการเซ็ต  status = 1 บันทึก เวลา,ผู้เปลี่ยนแปลง ด้วย
 * 2.เอาจำนวนที่เหลือกับสินค้าเสียกับสินค้าที่หยิบมาแล้วมารวมแล้ว ใส่กลับไปที่ inbound_location
 * 3.เปลี่ยน outbound_items_location status = 1 บันทึก เวลา,ผู้เปลี่ยนแปลง ด้วย
 * 4.เปลี่ยน outbound_rt status = 1 บันทึก เวลา,ผู้เปลี่ยนแปลง ด้วย
 */

$rt = $_POST['rt_id'];
// $rt = "DBN255807/0012";
$now = date("Y-m-d H:i:s");
$user_id = $_SESSION['userID'];

/* OUTBOUND RT */
$db->select_sum('item.qty', 'sum_qty');
$db->select("rt.rt_refid, rt.barcode, item.location_id, item.inbound_location_id")->from("outbound_rt AS rt");
$db->join("outbound_items_location AS item", "rt.id = item.outbound_id", "LEFT");
$db->where("rt.rt_refid = '" . $rt . "'");
$db->where(array("rt.status" => 0, "item.status" => 0));

$db->group_by("item.location_id");
$db->group_by("rt.barcode");

$sql = $db->get();
$rows = $sql->result_array();

foreach($rows as $index => $row) {
	/* REPORT MISSING */
	$db->select_sum('qty_disappear', 'sum_disappear');
	$db->select_sum('qty_wornout', 'sum_wornout');
	$db->select_sum('qty_expire', 'sum_expire');

	// 	$db->select("rt_refid, add_id");
	$db->from("report_missing")->where(array(
			"rt_refid" => $row['rt_refid'],
			"add_id" => $row['location_id'],
			"type" => "RT",
			"status" => 0
	));

	$db->group_by("barcode");

	$sql = $db->get();
	$missing_arr = $sql->result_array();
	$rows[$index]['missing'] = $missing_arr;
	$qty_missing = 0;
	
	foreach($rows[$index]['missing'] as $val) {
		$qty_missing = $val['sum_disappear'] + $val['sum_wornout'] + $val['sum_expire'];
	}
	$rows[$index]['qty_missing'] = $qty_missing;
	
	/* Rollback product qty */
	$where_update = array("inbound_location_id" => $row['inbound_location_id']);
	
	$sql = $db->select("qty_remain")->from("inbound_location")->where($where_update)->get();
	$result = $sql->row_array();
	$remain = $result['qty_remain'] + $row['sum_qty'] + $qty_missing;
	$update_fields = array("qty_remain" => $remain,
			"user_id" => $user_id,
			"update_time" => $now,
			"note" => "Canceled from RT ". $rt
	);
// 	$db->update("inbound_location", $update_fields, $where_update);
	$db->edit("inbound_location", $update_fields, $where_update);
	$where_missing = array(
		"add_id" => $row['location_id'], 
		"rt_refid" => $row['rt_refid'], 
		"inbound_location_id" => $row['inbound_location_id']
	);
	$db->update("report_missing", array("status" => 1), $where_missing);
	
}

/* Delete RT outbound */
// if(isset($_POST['mode']) && $_POST['mode'] == "deletechk") {

	$id = $_POST['del_id'];

	$db->select("oil.id as chk_id, oil.inbound_location_id ")->from("outbound_rt AS rt");
	$db->join("outbound_items_location AS oil", "rt.id = oil.outbound_id", "LEFT");
	$db->where(array(
			"oil.status" => 0,
			"rt.rt_refid" => $rt
	));

	$sql = $db->get();
	$results = $sql->result_array();

	$where_in = '';
	if ( !empty($results) ) {
		foreach ($results as $val) {
			$where_in .= ($where_in)? ", '" . $val['chk_id'] . "'": "'" . $val['chk_id'] . "'";
		}
	}
	$db->update("outbound_items_location", array("status" => 1), "id IN ($where_in)" );
	$db->update("outbound_rt", array("status" => 1), "rt_refid = '". $rt ."'" );
	
// 	_print($db->last_query());exit;
	
	die;

// }

	



?>