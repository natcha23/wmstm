<?php

$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$user_id = ($_SESSION['userID'])?$_SESSION['userID']:0;

// if( !empty($_POST) ) {
// 	_print($_POST);exit;
// }

/* Delete RT outbound */
if(isset($_POST['mode']) && $_POST['mode'] == "deletechk") {

	$id = $_POST['del_id'];

	$db->select("oil.id as chk_id, oil.inbound_location_id ")->from("outbound_rt AS rt");
	$db->join("outbound_items_location AS oil", "rt.id = oil.outbound_id", "LEFT");
	$db->where(array(
			"oil.status" => 0,
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
	$db->update("outbound_items_location", array("status" => 1), "id IN ($where_in)" );
	die;
}

/* Save remark only */
if(isset($_POST['mode']) && $_POST['mode'] == "saveremark") {

	$now = date("Y-m-d H:i:s");

	$fields = array();
	$fields['rt_refid'] = $_POST['rt_no'];
	$fields['type'] = "RT";
	$fields['detail'] = $_POST['remark'];
	$fields['user_id'] = $user_id;
	$fields['date_create'] = $now;
	$fields['date_update'] = $now;

	// insert or update
	if( empty($_POST['remark_id']) ) {
		$db->insert("outbound_remarks", $fields);
	} else {
		unset($fields['date_create']);
		$where = array("id" => $_POST['remark_id']);
		$db->update("outbound_remarks", $fields, $where);
	}

}

// /* insert to DB (mySQL) */
if(isset($_POST['mode']) && $_POST['mode'] == "save") {
// 	_print($_POST);// 	exit;
	$now = date("Y-m-d H:i:s");
	$temp_arr = $_POST['data'];
	$chk_list = $_POST['chk_list'];
	foreach($chk_list as $list){
		$temp[] = explode("_", $list);
	}
	foreach($temp as $key => $val){
		$newk = $val[0];
		$chkbox_arr[$newk]['barcode'] = $val[0];
		$chkbox_arr[$newk]['location'][$val[1]] = $val[1];
	}

	foreach($temp_arr as $val) {
		$data_arr[$val['barcode']] = $val;
	}
	if(!empty($temp_arr)) {

		foreach($data_arr as $key => $items) {

			$items['rt_qty'] = str_replace(',', '', $items['rt_qty']);
#			if($key == $chkbox_arr[$key]['barcode']) {

				/* table outbound */
				$qty_amount = $items['qty_amount'];
				$outrows['user_id'] = $user_id;
				$outrows['rt_refid'] = $items['rt_refid'];
				$outrows['rt_date'] = $items['rt_date'];
				$outrows['rt_qty'] = $items['rt_qty'];
				$outrows['barcode'] = $items['barcode'];
				$outrows['goods_name'] = $items['goods_name'];
				$outrows['unit'] = $items['unit'];
				$outrows['qty_amount'] = ($items['qty_amount'])?$items['qty_amount']:0;
// 				$outrows['remark'] = $items['remark'];
				$outrows['date_create'] = $now;
				$outrows['date_update'] = $now;

				// update data
				if( empty($items['id']) ) {
					// insert data
					$db->insert("outbound_rt", $outrows);
					$last_id = $db->insert_id();

				} else {
					unset($outrows['date_create']);
					$where = array("id" => $items['id']);
					$db->update("outbound_rt", $outrows, $where);
					$last_id = $items['id'];
				}

				/* table outbound_items_location */
				$sum_qty = $items['qty_amount'];
				$sum_loc_qty = 0;


				if($items['location']) {
					$sum_loc_qty = 0;
					foreach($items['location'] as $index=>$locitem) {
						if($locitem['add_id'] == $chkbox_arr[$key]['location'][$locitem['add_id']]) {

							$locrows['qty'] = $locitem['qty'];
							$locrows['user_id'] = $user_id;
							$locrows['date_out'] = $now;
							
							/* natcha on 23-09-2558 */
							#$locrows['product_id'] = $locitem['barcode'];
							
							$locrows['outbound_id'] = $last_id;
							$locrows['inbound_location_id'] = $locitem['inbound_location_id'];
							$locrows['qty'] = $locitem['qty'];
							$locrows['picking_status'] = 1;
							$locrows['location_id'] = $locitem['add_id'];
							$locrows['before_out_qty'] = $locitem['location_qty'];
							$locrows['after_out_qty'] = $locitem['location_qty']-$locitem['qty'];
							$locrows['remark'] = $locitem['remark'];
							$locrows['date_create'] = $now;
							$locrows['date_update'] = $now;

							$sum_loc_qty += $locitem['qty'];
							$sum_qty += $locitem['qty'];
							if( empty($locitem['id']) ) {
								// insert data
								$db->insert("outbound_items_location", $locrows);
								$last_loc_id = $db->insert_id();

							} else { // update data
								unset($locrows['date_create']);
								$where = array("id" => $locitem['id']);
								$db->update("outbound_items_location", $locrows, $where);

							}
// 							$locrows['remark'] = $locitem['remark'];

							// log table
							$log=array();
							$log['outbound_items_id'] = ($last_loc_id)?$last_loc_id:$locitem['id'];
							$log['qty'] = $locitem['qty'];
							$log['user_id'] = $user_id;
							$log['date_out'] = $now;
							/* natcha on 23-09-2558 */
							#$log['product_id'] = $locitem['barcode'];
							
							$log['outbound_id'] = $last_id;
							$log['qty'] = $locitem['qty'];
							$log['location_id'] = $locitem['add_id'];
							$log['inbound_location_id'] = $locitem['inbound_location_id'];
							$log['remark'] = $locitem['remark'];
							$log['date_create'] = $now;

							$db->insert("outbound_items_location_log", $log);

							/* update inbound location */
							$location_arr['qty_remain'] = $locitem['qty_remain'] - ($locitem['qty']+$locitem['disappear']+$locitem['wornout']+$locitem['expire']);
							$location_arr['update_time'] = $now;
							$location_arr['user_id_update'] = $user_id;
                            $location_arr['action_status'] = 2;

// 							$db->update("inbound_location" , $location_arr , array("inbound_location_id" => $locitem['inbound_location_id']));

                             // select zone id
                            $db->join('tb_zone zone','zone.zone_id = address.zone_id');
                            $sqlZone = $db->get_where('tb_address address','address.add_id = '.$locitem['add_id']);
                            $rsZone = $sqlZone->row_array();
                            $dataMovement = array(
                                'barcode' => $locitem['barcode'],
                                'location_id' => $locitem['add_id'],
                                'zone_id' => $rsZone['zone_id'],
                                'doc_no' => $locitem['rt_id'],
                                'qty' => $locitem['qty'],
                                'action_status' => 2,
                                'create_time' => _DATE_TIME_,
                                'user_id' => $user_id
                            );
                            $db->insert('product_movement',$dataMovement);

							/* Missing report  */
							$count_diff = $locitem['location_qty'] - $locitem['qty'];
							$missing = ($locitem['disappear'] || $locitem['expire'] || $locitem['wornout'] || $locitem['remark']);

							$missing_qty = $locitem['disappear'] + $locitem['expire'] + $locitem['wornout'];

							if($missing && $count_diff > 0) {
// 								$missing = $field['missing'];

								$report = array();
								$report = array(
										"rt_refid" => $locitem['rt_id'],
										"type" => "RT",
										"barcode" => $locitem['barcode'],
										"add_id" => $locitem['add_id'],
										"inbound_location_id" => $locitem['inbound_location_id'],
										"qty_in" => $locitem['location_qty'],
										"user_in" => $locitem['inbound_user'],
										"qty_out" => $locitem['qty'],
										"qty_diff" => ($locitem['location_qty'])? $locitem['location_qty'] - $locitem['qty'] : 0,
										"qty_disappear" => $locitem['disappear'],
										"qty_expire" => $locitem['expire'],
										"qty_wornout" => $locitem['wornout'],
										"detail" => $locitem['remark'],
										"user_id" => $user_id,
										"date_create" => $now,
										"date_update" => $now
								);

								if( empty($locitem['rep_id']) ) {
									$db->insert("report_missing", $report);
									$last_report_id = $db->insert_id();
								} else {
									unset($report['date_create']);
									$where = array("id" => $locitem['rep_id']);
									$db->update("report_missing", $report, $where);
								}

								/* Save report_missing_log */
								$report['report_id'] = ($last_report_id)?$last_report_id:$locitem['rep_id'];
								unset($report['date_update']);
								$db->insert("report_missing_log", $report);
							}
							/*  missing report  */

						}

					}

					/* Update qty amount */
					$row['qty_amount'] = $sum_qty;
					$row['rt_success'] = 1;
					if($sum_qty == $items['rt_qty']) {
						$row['rt_success'] = 0;
					}
					$edit_key = ($last_id)?$last_id:$items['id'];
					$db->update("outbound_rt" , $row , array("id" => $edit_key));

					// log table
					$log=array();
					$log['outbound_rt_id'] = ($last_id)?$last_id:$items['id'];
					$log['user_id'] = $user_id;
					$log['rt_refid'] = $items['rt_refid'];
					$log['rt_date'] = $items['rt_date'];
					$log['rt_qty'] = $items['rt_qty'];
					$log['barcode'] = $items['barcode'];
					$log['goods_name'] = $items['goods_name'];
					$log['unit'] = $items['unit'];
					$log['qty_amount'] = $sum_qty;
					$log['remark'] = $items['remark'];
					$log['date_create'] = $now;

					$db->insert("outbound_rt_log", $log);
				}

				/*
				 * update stock_product
				 * เปลี่ยนไปตัดขั้นตอน ออกรถส่งสินค้า แทน
				 */
				$where = array("product_id" => $items['barcode']);
				$db->select("product_qty")->from("stock_product");
				$db->where($where);
				$sql = $db->get();
				$result = $sql->row();
				$product_qty = $result->product_qty;

				$data=array();
				$data['product_qty'] = $product_qty - $sum_loc_qty;

// 				$db->update("stock_product", $data, $where);

#			}

			/* Insert rt status */
			$data = array();
			$data['rt_id'] = $_POST['rt_no'];
			$data['rt_branch'] = $_POST['rt_branch'];
			$data['rt_date'] = $_POST['rt_date'];
			$data['rt_product_amount'] = $_POST['product_amount'];
			$data['sum_product'] = ($_POST['allproductinrt'])?$_POST['allproductinrt']:0;
			$data['status'] = 1;
			$data['user_id'] = $user_id;
			$data['update_time'] = $now;

			if($_POST['product_amount'] != 0) {

				$db->select("rt_id")->from("outbound_rt_status")->where(array("rt_id" => $_POST['rt_no']));
				$sql = $db->get();
				$count = $sql->num_rows();

				if( empty($count) || $count == 0) {
					$db->insert("outbound_rt_status", $data);
				} else {
					unset($data['rt_product_amount']);
					unset($data['sum_product']);
					$key_id = $_POST['rt_no'];
					$where = array("rt_id" => $key_id);
					$db->update("outbound_rt_status", $data, $where);
				}
			}

			/* SELECT RT STATUS */
			$table = "outbound_rt";
			$db->select("id")->from($table)->where(array("rt_refid" => $_POST['rt_no']));
			$sql = $db->get();
			$num_RT = $sql->num_rows();

			if($num_RT > 0){
				$db->select("barcode")->from($table)->where(array("rt_refid" => $_POST['rt_no']))->group_by("barcode");
				$sql = $db->get();
				$checkstatus['product_list'] = $sql->num_rows();

				$db->select_sum('qty_amount')->where(array("rt_refid" => $_POST['rt_no']));
				$query = $db->get($table);

				$sum = $query->row();
				$checkstatus['product_qty'] = $sum->qty_amount;

			}
			/* Query RT status */
			$db->select("*")->from("outbound_rt_status")->where(array("rt_id" => $_POST['rt_no']));
			$query = $db->get();
			$status = $query->row();

			if( !empty($status) ) {
				$checkstatus['all_product_list'] = $status->rt_product_amount;
				$checkstatus['all_product_qty'] = $status->sum_product;
				$checkstatus['status'] = $status->status;
			}

			$rtstatus = 1;
			if(!empty($checkstatus)) {
				if ($checkstatus['product_list'] == $checkstatus['all_product_list']) {
					if($checkstatus['product_qty'] == $checkstatus['all_product_qty']) {
						$rtstatus = 2;
					} else {
						$rtstatus = 1;
					}
				} else {

					if($checkstatus['product_list'] > 0) {
						$rtstatus = 1;
					}
				}
			}
			if ( $rtstatus != 1) {

				/* Insert rt status */
				$data = array();
// 				$data['status'] = $rtstatus;
				$data['status'] = 1; //ตอนแรกให้ออกpickinglist แล้วตัดสินค้าที่ location เลย แต่ตอนนี้ไม่แล้ว
				$data['user_id'] = $user_id;
				$data['update_time'] = $now;

				$key_id = $_POST['rt_no'];
				$where = array("rt_id" => $key_id);
				$db->update("outbound_rt_status", $data, $where);
			}

		}

		/* insert remark for rt order */
		if($_POST['remark']) {
			$remark = array();
			$remark['rt_refid'] = $_POST['rt_no'];
			$remark['type'] = "RT";
			$remark['detail'] = $_POST['remark'];
			$remark['user_id'] = $user_id;
			$remark['date_create'] = $now;
			$remark['date_update'] = $now;
			if( empty($_POST['remark_id']) ) {
				$db->insert("outbound_remarks", $remark);
			}else{
				unset($remark['date_create']);
				$db->update("outbound_remarks", $remark, array("id" => $_POST['remark_id']));
			}
		}
	}

	/* Update blank store */
	$blank_address = $_POST['data']['blank_address'];
	if($blank_address) {
		foreach($blank_address as $iadd => $additem) {
			$rows['blank_status'] = 0;
			$db->update("tb_address", $rows, array("add_id" => $additem));
		}
	}

	unset($_POST['mode']);
}
// echo 'success';
// return;
// exit;
// die;
?>
