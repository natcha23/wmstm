<?php
$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$user_id = ($_SESSION['userID'])?$_SESSION['userID']:0;

// _print($_POST);exit;

/* insert to DB (mySQL) */
if(isset($_POST['mode']) && $_POST['mode'] == "save_list") {
	$now = date("Y-m-d H:i:s");
	$temp_arr = $_POST['data'];
	$picking_list = $_POST['picking'];
	
	if( !empty($picking_list) ) {
		foreach ( $picking_list as $row ) {
			
			/* Update RT status */
			$statusFields = array('status' => 2,
					'user_id' => $user_id,
					'picking_time' => $now,
					'update_time' => $now
			);
			$statusWhere = "rt_id = '" .$row. "'";
			$db->update("outbound_rt_status", $statusFields, $statusWhere);
			
			$db->select("inbound_location_id, rt.rt_refid as rt_no, rt.barcode, rt.rt_qty, lo.qty, lo.id as loc_id, lo.outbound_id");
			$db->from("outbound_rt AS rt");
			$db->join("outbound_items_location AS lo", "rt.id = lo.outbound_id", "LEFT");
			$db->where("rt.rt_refid = '$row'");
			$db->where("rt.status = 0");
			
			$pickSQL = $db->get();
// 			_print($db->last_query());
			$pickArr = array();
			$pickArr = $pickSQL->result_array();
			
			foreach($pickArr as $pval) {
				$fields = array(
						'date_out' => $now,
						'user_id' => $user_id,
						'picking_status' => 2,
						'date_update' => $now
				);				
				$where = array("id" => $pval['loc_id']);
				$db->update("outbound_items_location", $fields, $where);

				/* update inbound location */
				$db->select("qty, qty_remain");
				$db->from("inbound_location");
				$db->where(array("inbound_location_id" => $pval['inbound_location_id']));
				
				$inboundSQL = $db->get();
				$inboundResult = $inboundSQL->row();
				
				$pval['qty'] = str_replace(',', '', $pval['qty']); //reduce string , in numberic
				
				$location_arr['qty_remain'] = $inboundResult->qty_remain - $pval['qty'];
				$location_arr['update_time'] = $now;
				$location_arr['user_id_update'] = $user_id;
				$location_arr['action_status'] = 2;
				
				$whereinbound = array("inbound_location_id" => $pval['inbound_location_id']);
				$db->update("inbound_location" , $location_arr , $whereinbound);
			}
			
		}
	}
}
?>