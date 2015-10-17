<?php 

$user_id = ($_SESSION['userID'])?$_SESSION['userID']:0;
$now = date("Y-m-d H:i:s");

//  _print($_POST);#exit;
if(isset($_POST['mode']) && $_POST['mode'] == "deletebranchchk") {
	
	$id = $_POST['del_id'];
	
	$db->select("oc.id as chk_id ")->from("outbound_rt AS rt");
	$db->join("outbound_branch_check AS oc", "rt.id = oc.outbound_id", "LEFT");
	$db->where(array(
			"oc.status" => 0,
			"rt.rt_refid" => $id
	));
// 	$db->group_by("barcode");
	$sql = $db->get();
	$results = $sql->result_array();
	
	$where_in = '';
	if ( !empty($results) ) {
		foreach ($results as $val) {
			$where_in .= ($where_in)? ", '" . $val['chk_id'] . "'": "'" . $val['chk_id'] . "'";
		}
	}
	$db->update("outbound_branch_check", array("status" => 1), "id IN ($where_in)" );
	die;
}

// _print($_POST);exit;
/* insert to DB (mySQL) */
if(isset($_POST['mode']) && $_POST['mode'] == "save") {
	
	$data = $_POST['data'];
	
	if(!empty($data)) {
		
		$count_check = 0;
		foreach($data as $key => $field) {

			if( $field['check_status'] == 'on' || !empty($field['chk_id']) ) {

				$row = array();
				$row['user_id'] = $user_id;
				$row['outbound_id'] = $field['outbound_id'];
				$row['branch_chk_qty'] = $field['qty'];
				$row['branch_chk_status'] = (isset($field['check_status']) && $field['check_status'] == on)? 0 : 1;
				$row['branch_chk_remark'] = $field['check_remark'];
				$row['date_create'] = $now;
				$row['date_update'] = $now;
				
// 				if( !empty($field['chk_id']) ) {
				/* Insert & Update outbound_check table */
				if( empty($field['chk_id']) ) {
					$db->insert("outbound_branch_check", $row);
					$last_id = $db->insert_id();
					sleep(0.01); /* เกิดการ insert ไม่เข้าหลายครั้ง จึงหน่วงเวลาไว้ก่อน ????*/
				} else {
					$id = $field['chk_id'];
					unset($row['date_create']);
					$where = array("id" => $id);
					$db->update("outbound_branch_check", $row, $where);
				}
// 				_print($db->last_query());exit;
				/* Save log table */
				$id = ($last_id)? $last_id:$field['chk_id'];
				unset($row['date_update']);
				$row['outbound_branch_chk_id'] = $id;
				$db->insert("outbound_branch_check_log", $row);
				
				if ($field['check_status'] == on) {
					$count_check++;
				}

				/* Missing report  */
				$count_diff = $field['qty_amount'] - $field['qty'];
				if(isset($field['missing']) && $field['check_status'] == 'on') {
					$missing = $field['missing'];
				
					$report = array();
					$report = array("rt_refid" => $field['rt_id'],
							"type" => "BCHK",
							"barcode" => $field['barcode'],
							"add_id" => null,
							"qty_in" => $field['qty_amount'],
							"user_in" => $field['rt_user'],
							"qty_out" => $field['qty'],
							"qty_diff" => ($field['qty_amount'])? $field['qty_amount'] - $field['qty'] : 0,
							"qty_disappear" => $missing['disappear'],
							"qty_expire" => $missing['expire'],
							"qty_wornout" => $missing['wornout'],
							"detail" => $field['check_remark'],
							"user_id" => $user_id,
							"date_create" => $now,
							"date_update" => $now
					);
					
					if( empty($missing['id']) ) {
						$db->insert("report_missing", $report);
						$last_report_id = $db->insert_id();
					} else {
						unset($report['date_create']);
						$where = array("id" => $missing['id']);
						$db->update("report_missing", $report, $where);
					}

					/* Save report_missing_log */
					$report['report_id'] = ($last_report_id)?$last_report_id:$missing['id'];
					unset($report['date_update']);
					$db->insert("report_missing_log", $report);
				}
				/*  missing report  */
			}
		}
		
		/* Update rt status
		 * Query RT checkout */
// 		$db->select_sum("chk.check_qty", "sum_check_qty");
		$db->select("sts.*")->from("outbound_rt_status AS sts");
		
		$db->join("outbound_rt AS rt", "sts.rt_id = rt.rt_refid", "INNER");
		$db->join("outbound_check AS chk", "rt.id = chk.outbound_id", "LEFT");
		
// 		$db->group_by("chk.outbound_id");
		
		$db->where(array("sts.rt_id" => $_POST['rt_no'],
				"rt.status" => 0));
		
		$query = $db->get();
		$count_line = $query->num_rows();
		$status = $query->row();
		$ostatus = $status->status;
		$sum_prod_check = $status->sum_check_qty;
		$sum_items_check = $status->rt_product_amount;

		$rtstatus = $ostatus;
		/* เช็คทุกรายการแล้ว จำนวนสินค้าอาจไม่ครบ */
		if($count_line == $sum_items_check) {
			$rtstatus = 7;
		}
		/* ถ้าเช็คครบ ทุกรายการ แต่อาจ ได้ไม่ครบตามจำนวนสินค้า */
		if($count_check == $sum_prod_check) {
			$rtstatus = 7;
		}
		/* ถ้าเช็คไม่ครบ */
		if($count_check < $sum_prod_check) {
			$rtstatus = 6;
		}

		$data = array();
		$data['status'] = $rtstatus;
		$data['user_id'] = $user_id;
		$data['update_time'] = $now;
		$data['time_branch_confirm'] = $now;
		
		$db->select("rt_id")->from("outbound_rt_status")->where(array("rt_id" => $_POST['rt_no']));
		$sql = $db->get();
		$count = $sql->num_rows();

		if($count > 0) {
			$key_id = $_POST['rt_no'];
			$where = array("rt_id" => $key_id);
			$db->update("outbound_rt_status", $data, $where);
		}
	}
	
	/* Save remark in checking process */
	if ( !empty($_POST['remark_chk']) ) {
		
		$fields = array();
		$fields['rt_refid'] = $_POST['rt_no'];
		$fields['type'] = "BCHK"; // Branch checked
		$fields['detail'] = $_POST['remark_chk'];
		$fields['user_id'] = $user_id;
		$fields['date_create'] = $now;
		$fields['date_update'] = $now;
		
		// insert or update
		if( empty($_POST['remark_id']) ) {
			$db->insert("outbound_remarks", $fields);
// 			$db->insert("outbound_report", $fields);
		} else {
			unset($fields['date_create']);
			$where = array("id" => $_POST['remark_id']);
			$db->update("outbound_remarks", $fields, $where);
// 			$db->update("outbound_report", $fields, $where);
		}	
	}
}

echo 'success';
die;
?>