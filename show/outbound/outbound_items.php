<?php
// $connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
// $conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
// sqlsrv_query("SET NAMES UTF8");


$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

function convertDate($date=null) {
	if(!empty($date)) {
		$data = explode(" ", $date);
		
		$_date = explode("-", $data[0]);
		$thYear = $_date[0] + 543;
		if(count($data) > 1) {
			$_result = $_date[2] . "/" . $_date[1] . "/" . $thYear . " " . $data[1];
		} else {
			$_result = $_date[2] . "/" . $_date[1] . "/" . $thYear;
		}
		return $_result;
	} else {
		return;
	}
}

function convertBackDate($date=null) {
	if(!empty($date)) {
		$data = explode(" ", $date);

		$_date = explode("/", $data[0]);
		$enYear = $_date[2] - 543;
		if(count($data) > 1) {
			$_result = $enYear . "-" . $_date[1] . "-" . $_date[0] . " " . $data[1];
		} else {
			$_result = $enYear . "-" . $_date[1] . "-" . $_date[0] . $thYear;
		}
		return $_result;
	} else {
		return;
	}
}

?>

<style>
.input-td {
	color: #009933;
	font-weight: bold;
	margin-left: 40px;
}
</style>

<button class="btn btn-warning" onclick="window.location.href='?page=outbound_list&finddate=<?php echo $today; ?>'">กลับสู่หน้ารายการ RT</button>

<div class="ibox-title">

<form method="POST" action="?page=outbound_process" id="rt_outbound">

	<input type="hidden" id="mode" name="mode">
	<table id="table-inbound" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>RT Number</th>
				<th>RT Date</th>
				<th>Barcode</th>
				<th>Goods Name</th>
				<th>RT Qty</th>
				<th>Unit</th>
				<th>Location</th>
				<th>Qty</th>
				<th>Taken</th>
				<th style="display: none;">Diff</th>
				<th>Remark</th>
				<th style="display: none">Missing</th>
				<th style="display: none">Blank</th>
			</tr>
		</thead>
		<tbody>

<?php 

	/* new rt
	 * select on thaimart database
	 */ 
	
	$condition = '';
	$cond_date = " AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '{$yesterday}', 121) AND CONVERT(datetime, '{$eoftoday}', 121)) ";
	
	$arr_cond = array(
			"DT.DT_PROPERTIES IN ('211','304')",
			"DT.DT_KEY <> '2'",
			"WL.WL_KEY in( '1','111','165')",
			"WL_TO.WL_CODE <> '0499'",
	// 		"DI.DI_ACTIVE=0",
	// 		"DT.DT_ENABLE='Y'"
	);
	
	foreach ($arr_cond as $where) {
		$cond .= ($cond)?  " AND " . $where : " WHERE " . $where;
	}
	$condition = $cond . $cond_date;
	$condition = $cond . " AND DI.DI_REF = '{$refid}' ";
	
	$sql_fields = "DI.DI_KEY, DI.DI_CRE_DATE, DI.DI_REF, DI.DI_REMARK,
				TD.TRD_SH_CODE, TD.TRD_QTY, TD.TRD_UTQNAME, GDM.GOODS_ALIAS";
	
	$sql	= "SELECT ". $sql_fields . "
							FROM  DOCINFO AS DI
							LEFT JOIN  TRANSTKH AS TH ON DI.DI_KEY = TH.TRH_DI
							INNER JOIN TRANSTKD AS TD ON TH.TRH_KEY = TD.TRD_TRH
							INNER JOIN WARELOCATION AS WL ON WL.WL_KEY = TD.TRD_WL
							INNER JOIN WARELOCATION as WL_TO on TD.TRD_TO_WL = WL_TO.WL_KEY
							INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
							INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY
							LEFT JOIN GOODSMASTER AS GDM ON TD.TRD_SH_CODE = GDM.GOODS_CODE
	                      ".$condition;
	
	$sql .= ' ORDER BY DI.DI_REF, TD.TRD_SEQ ';
	// echo $sql;exit;
	$params = array();
// 	$stmt = sqlsrv_query( $conn, $sql, $params );
// 	if( $stmt === false) {
// 		die( print_r( sqlsrv_errors(), true) );
// 	}
	
	$allproductinrt = 0;
// 	while( $rows = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
// 		$rows['DI_CRE_DATE'] = $rows['DI_CRE_DATE']->format('Y-m-d H:i:s');
// 		$srv_arr[] = $rows;
// 		$rt_date = $rows['DI_CRE_DATE'];
// 		$allproductinrt += $rows['TRD_QTY'];
// 	}
	
	
	/* Free the statement and connection resources. */
// 	sqlsrv_free_stmt( $stmt);
// 	sqlsrv_close( $conn);
	
	
	$db->select("*, rt.id AS id, lo.id AS loc_id, rt.barcode AS barcode")->from("outbound_rt AS rt");
	$db->join("outbound_items_location AS lo", "rt.id = lo.outbound_id", "LEFT");
	
	$db->where("rt.status = 0");
	$db->where("rt.rt_refid = '$refid'");
	
	$sql = $db->get();
// 	_print($db->last_query());
	$outs = $sql->result_array();
	 // delSo
	foreach( $outs as $rows ) {
		$rows['DI_CRE_DATE'] = $rows['rt_date'];
		$srv_arr[] = $rows;
		$rt_date = $rows['rt_date'];
		$allproductinrt += $rows['rt_qty'];
	}
	//delSo
	
	$product_amount = count($srv_arr);
// 	_print($srv_arr);
	foreach ($srv_arr as $key => $val) {
// 		$result_arr[$val['TRD_SH_CODE']] = $val;
		$result_arr[$val['barcode']] = $val;//delSo
		
	}
	/* map rt */
	$db->select("rt_refid");
	$db->from("outbound_rt");
	$db->where(array("rt_refid" => $refid));
	
	$sql = $db->get();
	$check_rt = $sql->num_rows();
	
	if($check_rt > 0) { // edit mode
		$db->select("*,ort.id AS rt_id");
		$db->from("outbound_rt AS ort");
		$db->join("outbound_items_location AS oil", "ort.id = oil.outbound_id AND oil.status = 0", "LEFT" );
		$db->where ( array (
				"ort.rt_refid" => $refid,
				"ort.status" => 0,
				// 			"rt_success" => 1
					
		));
		$db->group_by("ort.barcode");
		
		$sql = $db->get();
		$outbound_arr = $sql->result_array();
	}
	$i=$j=0;
	$rt_row = 0;

	$db->select("status")->from("outbound_rt_status")->where("rt_id", $refid);
	$sql = $db->get();
	$rt_status = $sql->row('status');
	
	// while( $result = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
	foreach($result_arr as $key => $result) {
		/////////// FIFO & FEFO ///////////////////
		$result['TRD_SH_CODE'] = $result['barcode'];// del So
		$item = $result['TRD_SH_CODE'];
// 		$item = "9000101126785";

// 		$result['rt_success'] = 1;
		if(!empty($outbound_arr)) {
			foreach($outbound_arr as $outbval) {
				if($result['TRD_SH_CODE'] == $outbval['barcode']) {
					$result['rt_id'] = $outbval['rt_id'];
					$result['rt_success'] = $outbval['rt_success'];
					$result['qty_amount'] = $outbval['qty_amount'];
				}
			}
		}
			// check have product in stock
			$db->select("*");
			$db->from("stock_product");
			$db->where("product_id = '$item'");
			$db->where("product_qty > 0");
		
			$sql_chkstock = $db->get();
			
			$chkstock = $sql_chkstock->num_rows();
			$stock_prod = $sql_chkstock->row_array();

// 	 		if(!empty($chkstock) && ($result['rt_success'] == 1) ) {
	 		if( $result['rt_success'] == 1 ) {

				// filter category
				$db->select("ip.product_fefo");
				$db->from("inbound_po AS ip");
				$db->where(array("ip.product_no" => $item));
				
				$sql_fefo = $db->get();
				$isFEFO = $sql_fefo->row_array();
				$where = "ip.product_no = '$item'";
				$where .= " AND (il.qty_remain > '0' )";
	
				if($isFEFO['product_fefo'] != 0) {
					// FEFO
					$order = "product_fefo_date ASC ";
						
					/* $db->select("ip.po_id, ip.product_no, ip.product_unit, ip.product_date_in, ip.product_fefo, ip.product_fefo_date, il.inbound_id, il.qty, il.qty_remain, ad.add_id, il.user_id AS inbound_user, ad.add_name");
					$db->select("il.inbound_location_id");
					$db->from("inbound_po AS ip");
					$db->join("inbound_location AS il", "ip.inbound_id = il.inbound_id");
					$db->join("tb_address AS ad", "il.location_id = ad.add_id", "LEFT");
					$db->where($where);
					$db->order_by($order); */
					$db->select("ip.po_id, ip.product_no, ip.product_unit, ip.product_date_in, ip.product_fefo, ip.product_fefo_date, ".
							"il.inbound_id, il.qty, il.qty_remain, ad.add_id, il.user_id AS inbound_user, ad.add_name");
					$db->select("il.inbound_location_id");
					$db->from("inbound_location AS il");
					$db->join("inbound_po AS ip", "il.inbound_id = ip.inbound_id", "LEFT");
					$db->join("tb_address AS ad", "il.location_id = ad.add_id", "LEFT");
					$db->where($where);
					$db->order_by($order);
		
				} else {
					// FIFO
					$order = "product_date_in ASC ";
		
					/* $db->select("ip.po_id, ip.product_no, ip.product_unit, ip.product_date_in, ip.product_fefo, ip.product_fefo_date, il.inbound_id, il.qty, il.qty_remain, ad.add_id, il.user_id AS inbound_user, ad.add_name");
					$db->select("il.inbound_location_id");
					$db->from("inbound_po AS ip");
					$db->join("inbound_location AS il", "ip.inbound_id = il.inbound_id");
					$db->join("tb_address AS ad", "il.location_id = ad.add_id", "LEFT");
					$db->where($where);
					$db->order_by($order); */
					$db->select("ip.po_id, ip.product_no, ip.product_unit, ip.product_date_in, ip.product_fefo, ip.product_fefo_date, ".
							"il.inbound_id, il.qty, il.qty_remain, ad.add_id, il.user_id AS inbound_user, ad.add_name");
					$db->select("il.inbound_location_id");
					$db->from("inbound_location AS il");
					$db->join("inbound_po AS ip", "il.inbound_id = ip.inbound_id", "LEFT");
					$db->join("tb_address AS ad", "il.location_id = ad.add_id", "LEFT");
					$db->where($where);
					$db->order_by($order);
						
				}
				$sql = $db->get();
// 				_print($db->last_query());
				$result[$item] = $sql->result_array();
				
	 		} else {
	 			/* ถ้ามีการเลือกสินค้าออกใบ pick slip แล้ว
	 			 * Display location outbound.
	 			 */
	 			$location_results = array();
	 			$db->select("rt.barcode as barcode, loca.qty AS in_qty, qty_remain, goods_name, unit, loca.remark AS remark, add_name, inlo.qty AS loc_qty")->from("outbound_rt AS rt");
	 			$db->join("outbound_items_location AS loca", "rt.id = loca.outbound_id", "LEFT");
	 			$db->join("inbound_location AS inlo", "loca.inbound_location_id = inlo.inbound_location_id", "INNER");
	 			$db->join("tb_address AS addr", "loca.location_id = add_id", "INNER");
	 			
	 			$db->where(array("rt_refid" => $result['rt_refid'], 'rt.barcode' => $result['barcode']));
	 			$db->where("rt.qty_amount > 0");
	 			$location_sql = $db->get();

	 			$location_results = $location_sql->result_array();
	 			$count_location = count($location_results);

	 			$location_name = array();
	 			if(empty($count_location) || $count_location > 1) {
		 			$db->select("add_name")->from("outbound_rt AS rt");
		 			$db->join("outbound_items_location AS loc", "rt.id = loc.outbound_id", "LEFT");
		 			$db->join("tb_address AS adrs", "loc.location_id = adrs.add_id", "LEFT");
		 			$db->where(array('rt.rt_refid' => $result['rt_refid']));
		 			
		 			$sql = $db->get();
		 			$location_name = $sql->result_array();
	 			}
	 			
	 		}
	 		
	 		/* Location demo data */
// 	 		$item="1225465465";
// 	 		$result[$item][0] = array("add_id" => "241", "add_name" => "AA-01-A", "qty_remain" => 10, "inbound_user" => 21, "inbound_location_id" => 5);
// 	 		$result[$item][1] = array("add_id" => "256", "add_name" => "AA-01-B", "qty_remain" => 30, "inbound_user" => 21, "inbound_location_id" => 6);
	 		
	 		/* Location demo data */
	 		$qtyCheck = true;
	 		$qtyAmount = 0;
	 		$countLocation = count($result[$item]);
	 		$remain = $result['rt_qty'] - $result['qty_amount'];
	 		
	 		for($l=0;$l<$countLocation;$l++) {
	 			
		 		$decision_qty = 0;
		 		$isChecked = 0;
		 		
		 		if( ($qtyCheck) && ($result['rt_qty'] > $result['qty_amount']) ) {
		 			
		 			if( $remain >= $result[$item][$l]['qty_remain'] ) {
		 				$decision_qty = $result[$item][$l]['qty_remain'];
		 			}
		 			if( $remain < $result[$item][$l]['qty_remain'] ) {
		 				$decision_qty = $remain;
		 			}
		 			
		 			$isChecked = 1;
		 			$qtyAmount+=$decision_qty;
		 			$remain = $result['rt_qty'] - ($result['qty_amount'] + $qtyAmount);
		 			if($remain == 0) {
		 				$qtyCheck = false;
		 			}
		 		}
		 		
		 		$result[$item][$l]['offer'] = $isChecked;
		 		$result[$item][$l]['offer_qty'] = $decision_qty;
		 		
	 		}
		
			////////// END FIFO & FEFO ////////////////////
			$i++;
	// 		$qty_exp = explode(".", $result['TRD_QTY']);
			if(count($qty_exp) > 0) {
				$qty = $qty_exp[0];
			}else{
	// 			$qty = $result['TRD_QTY'];
				$qty = $result['rt_qty']; // del So
			}
			$taken = ($result['qty_amount'])? $result['qty_amount']: 0;
			
			// SO DELETE
			$result['DI_REF'] = $result['rt_refid'];
			$result['DI_CRE_DATE'] = $result['rt_date'];
			$result['TRD_SH_CODE'] = $result['barcode'];
			$result['GOODS_ALIAS'] = $result['goods_name'];
			$result['TRD_UTQNAME'] = $result['unit'];
			// SO DELETE
	?>
				<tr>
					<td><?php echo $i; ?></td>
					<td><?php echo $result['DI_REF']; ?></td>
					<td><?php echo $result['DI_CRE_DATE']; ?></td>
					<td><?php echo $result['TRD_SH_CODE']; ?></td>
					<td><?php echo $result['GOODS_ALIAS']; ?></td>
					<td><?php echo $qty; //number_format($qty); ?></td>
					<td><?php echo $result['TRD_UTQNAME']; ?></td>
					<td class="input-td"><?php if ( empty($count_location) || $count_location <= 1) { echo $location_name[$rt_row]['add_name']; } else {}?></td>
					<td class="input-td"></td>
					<td class="input-td"><span id="cal-amt_<?php echo $result['TRD_SH_CODE']; ?>"><?php echo $taken; ?></span></td>
					<td style="display: none;"></td>
					<td></td>
					<td style="display: none"></td>
					<td style="display: none"></td>
	
					<input type="hidden" name="data[<?php echo $i; ?>][id]"	value="<?php echo $result['id']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][rt_refid]" value="<?php echo $result['DI_REF']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][rt_qty]"	value="<?php echo number_format($qty); ?>" id="rt_qty_<?php echo $result['TRD_SH_CODE']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][rt_date]" value="<?php echo $result['DI_CRE_DATE']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][barcode]" value="<?php echo $result['TRD_SH_CODE']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][goods_name]"	value="<?php echo $result['GOODS_ALIAS']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][unit]" value="<?php echo $result['TRD_UTQNAME']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][qty_amount]" value="<?php echo $result['qty_amount']; ?>">
					
					<input type="hidden" id="qty_amount_<?php echo $result['TRD_SH_CODE']; ?>" value="<?php echo $taken; ?>">
			
					<?php $stock_prod['product_qty'] = $qty; ?>
					<input type="hidden" name="data[<?php echo $i; ?>][allproduct]"	value="<?php echo $stock_prod['product_qty']; ?>">
				</tr>
		<?php
		/* ถ้าจัดสินค้าครบแล้ว ไม่ต้องแสดงรายการสินค้าในคลังให้เลือก */
		$isSuccess = ($result['rt_qty'] == $result['qty_amount']);
		if(!empty($result[$item]) && !$isSuccess) {
			$j=0;
			foreach($result[$item] as $val) {
				
				/* Select report missing */
// 				$db->select("*")->from("report_missing");
// 				$db->where(array("rt_refid" => $result['DI_REF'],
// 						"barcode" => $result['TRD_SH_CODE'],
// 						"type" => "RT",
// 						"add_id" => $val['add_id']
// 				));
// 				$sql = $db->get();
// 				$missing_arr = $sql->row();
		?>
				<!-- offer location -->
				<tr id="locations">
					<?php 
						$offer_qty = $val['qty_remain'];
						if($val['offer_qty'] <> 0) {
							$offer_qty = $val['offer_qty'];	
						}
					?>
					<td><input type="checkbox" name="chk_list[]" id="chkbox_<?php echo $result['TRD_SH_CODE']."_".$val['add_id']; ?>" value="<?php echo $result['TRD_SH_CODE']."_".$val['add_id']; ?>" onchange="$.cal_qty($('#take_<?php echo $result['TRD_SH_CODE']."_".$val['add_id']; ?>'), '<?php echo $result['TRD_SH_CODE'];?>', '<?php echo $val['add_id']; ?>');" <?php if($val['offer']==1) { ?> checked <?php } ?>></td>
					
					<td></td>
					<td></td>
					<td><?php echo $result['TRD_SH_CODE']; ?></td>
					<td><?php echo $result['GOODS_ALIAS']; ?></td>
					<td></td>
					<td><?php echo $result['TRD_UTQNAME']; ?></td>
					<td class="input-td"><?php echo $val['add_name']; ?></td>
					<td class="input-td" id="inStore_<?php echo $result['TRD_SH_CODE']."_".$val['add_id'];?>"><?php echo number_format($val['qty_remain']); ?></td>
					<td class="input-td"><input type="text" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][qty]" size="3" id="take_<?php echo $result['TRD_SH_CODE']."_".$val['add_id']; ?>" value="<?php echo number_format($offer_qty); ?>" onfocus="$.chkauto('<?php echo $result['TRD_SH_CODE']; ?>', '<?php echo $val['add_id'] ?>');" onKeyUp="$.CheckNum(this, this.value, '<?php echo $result['TRD_SH_CODE']?>', '<?php echo $val['add_id'] ?>')"></td>
					<td style="display: none;"><span id="cal-diff_<?php echo $result['TRD_SH_CODE']."_".$val['add_id']; ?>"><?php echo $val['qty_remain'] - $offer_qty; ?></span></td>
					<td><input type="text" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][remark]" size="7" onfocus="$.chkauto('<?php echo $result['TRD_SH_CODE']?>', '<?php echo $val['add_id'] ?>');" value="<?php echo $missing_arr->detail; ?>"></td>
					
					<td style="display: none" nowrap>
			        	<label>ไม่ครบ</label>&nbsp;<input type="text" size="1" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][disappear]" id="disappear_<?php echo $result['TRD_SH_CODE']; ?>_<?php echo $val['add_id'] ?>" value="<?php echo $missing_arr->qty_disappear;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $result['TRD_SH_CODE']; ?>', '<?php echo $val['add_id'] ?>')">
			        	<label>ชำรุด</label>&nbsp;<input type="text" size="1" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][wornout]" id="wornout_<?php echo $result['TRD_SH_CODE']; ?>_<?php echo $val['add_id'] ?>" value="<?php echo $missing_arr->qty_wornout;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $result['TRD_SH_CODE']; ?>', '<?php echo $val['add_id'] ?>')">
			        	<label>หมดอายุ</label>&nbsp;<input type="text" size="1" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][expire]" id="expire_<?php echo $result['TRD_SH_CODE']; ?>_<?php echo $val['add_id'] ?>" value="<?php echo $missing_arr->qty_expire;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $result['TRD_SH_CODE']; ?>', '<?php echo $val['add_id'] ?>')">
			        	<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][rep_id]" value="<?php echo $missing_arr->id; ?>"/>
			        </td>
					
					<td style="display: none"><input type="checkbox" name="data[blank_address][]" value="<?php echo $val['add_id'] ?>"></td>
					
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][outbound_id]" value="<?php echo $val['rt_id']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][rt_id]" value="<?php echo $result['DI_REF']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][chk_id]"	value="<?php echo $val['chk_id']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][check_qty]"	value="<?php echo $val['qty_amount']; ?>">
					
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][barcode]"	value="<?php echo $result['TRD_SH_CODE']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][inbound_user]"	value="<?php echo $val['inbound_user']; ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][qty_amount]"	value="<?php echo $val['qty_remain']; ?>">
					
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][add_id]" value="<?php echo $val['add_id'] ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][id]" value="<?php echo $val['id'] ?>">
					
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][location_qty]" value="<?php echo $val['qty_remain'] ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][qty_remain]" value="<?php echo $val['qty_remain'] ?>">
					<input type="hidden" name="data[<?php echo $i; ?>][location][<?php echo $j; ?>][inbound_location_id]" value="<?php echo $val['inbound_location_id'] ?>">
					
					<input type="hidden" name="rt_branch" value="<?php echo $result['shipto_id']; ?>">
				</tr>
				<!-- end offer location -->
		<?php 
				$j++;
			}
		}
		
		/* แสดงรายละเอียดที่มีการเลือกสินค้าแล้ว */
		else {

			foreach ( $location_results as $val ) {
?>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td><?php echo $val['barcode']; ?></td>
					<td><?php echo $val['goods_name']; ?></td>
					<td></td>
					<td><?php echo $val['unit']; ?></td>
					<td class="input-td"><?php echo $val['add_name'] ?></td>
					<td class="input-td"><?php echo number_format($val['loc_qty']); ?></td>
					<td class="input-td"><?php echo number_format($val['in_qty']); ?></td>
					<td style="display: none;"><span id="cal-diff_<?php echo $result['TRD_SH_CODE']."_".$val['add_id']; ?>"><?php echo $val['qty_remain'] - $offer_qty; ?></span></td>
					<td><?php echo $val['remark']; ?></td>
					
					<td style="display: none" nowrap>
			        	<label>ไม่ครบ</label>&nbsp;<?php echo $missing_arr->qty_disappear;?>
			        	<label>ชำรุด</label>&nbsp;<?php echo $missing_arr->qty_wornout;?>
			        	<label>หมดอายุ</label>&nbsp;<?php echo $missing_arr->qty_expire;?>
			        </td>
					
					<td style="display: none"><input type="checkbox" name="data[blank_address][]" value="<?php echo $val['add_id'] ?>"></td>
				</tr>			
			
<?php 
			}
		}
		
		$rt_row++;
	}
	// Remark RT
	$db->select("id, detail");
	$db->from("outbound_remarks");
	$db->where(array("rt_refid" => $refid));
	
	$sql = $db->get();
	$remark = $sql->row_array();

?>
	</tbody>
	</table>

	<textarea rows="5" cols="80" name="remark" id="remark"><?php echo $remark['detail']; ?></textarea>
	<input type="hidden" name="remark_id" value="<?php echo $remark['id']; ?>"> 
	<input type="hidden" name="rt_no" id="rt_no" value="<?php echo $refid; ?>"> 
	
	<input type="hidden" name="product_amount" value="<?php echo $product_amount; ?>">
	<input type="hidden" name="rt_date" value="<?php echo $rt_date; ?>">
	<input type="hidden" name="allproductinrt" value="<?php echo $allproductinrt; ?>">
	
</form>
</div>

<!-- Modal -->
<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated bounceInRight"></div>
	</div>
</div>
<!-- Modal -->


<script>
$(function(){

// 	$('#nav-outbound').attr('aria-expanded', 'true');
	$('#nav-outbound').parent().addClass('active');
	$('#nav-outbound').addClass('in');
	$('#outbound_list').addClass('active');	
	
	$('body').on('hidden.bs.modal', '.modal', function () {
		$(this).removeData('bs.modal');
		window.location.reload();
	})
	
	$("[id^='chkbox_']").each(function( index ) {
		var point = this.id.split('_');	
		var code = point[1];
		var loca = point[2];

		if($(this).prop('checked') == false) {
			$('#take_'+code+'_'+loca).prop('disabled', true);
		}
	});
	$("[id^='chkbox_']").change(function( index ) {
		var point = this.id.split('_');	
		var code = point[1];
		var loca = point[2];

		if($(this).prop('checked') == false) {
			$('#take_'+code+'_'+loca).prop('disabled', true);
		} else {
			$('#take_'+code+'_'+loca).prop('disabled', false);
		}
	});
	
	//id="cal-amt_9000101341409"
	$("[id^='cal-amt_']").each(function( index ) {	
		var code = this.id.split('_')[1];
		var qtyspan = $('#cal-amt_'+code);
		var qty_amount = $('#qty_amount_'+code).val();
		var total = qty_amount;
		$("[id^='take_"+code+"']").each(function( index ) {
			var chk_id = this.id.split("_")[2];
			if($("[id^='chkbox_"+code+"_"+chk_id+"']").is(':checked')) {
				
				var	cal_id = this.id;
				var value = $('#'+cal_id).val();
				total = (total*1)+(value*1);
			}
		});
		
		qtyspan.html(total);
	});
	
	var oTable = $('#table-inbound').dataTable({
		"pageLength": 100,
		"ordering": false
	});
	var search = $('#table-inbound_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

	/* Display Check all */
	var locationcount = $("tr[id='locations']").length;
	if(locationcount > 0) {
		$('#chk_all').toggle();
	}

	
	var buttonPrint = $('<button />',{ class:'btn btn-success' }).html('พิมพ์').css({ 'margin':'0 30px 0 130px' });
	$(buttonPrint).click(function (e) {
		var rt_id = $('#rt_no').val();
//			var car_id = $('#car_id_'+row).val();
		$('#myModal').removeData('bs.modal');
		$('#myModal').modal({remote: 'show/outbound/picking_form.php?rtID='+rt_id});
		$('#myModal').modal('show');

		event.preventDefault();
	});
	<?php if(!empty($rt_status) && $rt_status >= 2) { ?>
	
	$('#table-inbound_filter').append(buttonPrint);

	<?php } ?>
	
	var button = $('<button />',{ class:'btn btn-primary' }).html('บันทึก').css({ 'margin':'0 30px 0 130px' });
	$(button).click(function (e) {

			e.preventDefault();
			var msg_comment = $('#remark').val();
			if( $('input[type="checkbox"]:checked').length <=0 && msg_comment=='') {
		        alert('กรุณาเลือกรายการเพื่อบันทึก ');
		        event.preventDefault();
		    } else {

		    	 if (confirm('ต้องการบันทึกข้อมูลใช่หรือไม่') == true) {
			    	 var data = new Array();
			    	 var is_close = 'N';
			    	 
		    		$('#mode').val('save');

					// Save outbound //
					$.ajax({             
						type: 'post',
						url: 'handheld/?page=rt_process',
						data: $('#rt_outbound').serialize(),
						success: function (html) {
// 							console.log(html);
// 							alert('บันทึกข้อมูลแล้ว');
// 							location.reload();
							/* บันทึกเสร็จ ให้พิมพ์ Picking List */
//			 				$('#myModal').modal('hide');
							
							
							if($('#closeRT').prop('checked') == true) {
					    		is_close = 'Y';
				    		} else {
				    			is_close = 'N';
				    		}
		
				    		// Close this RT. //
							if(is_close == "Y") {
					    		$.ajax({             
									type: 'post',
									url: 'handheld/?page=update_process',
									data: {	method : 'send-to-checking-out', rt_id : $('#rt_no').val()},
									success: function (html) {
										// Do nothing. //
		// 								console.log(html);return false;
									}
							    });
							}
							
							
							
//			 				window.location.reload();
					    	var rt_id = $('#rt_no').val();
							$('#myModal').removeData('bs.modal');
							$('#myModal').modal({remote: 'show/outbound/picking_form.php?rtID='+rt_id});
							$('#myModal').modal('show');
						}
				    });

					
					
					event.preventDefault();
				    return false;
				} else {
					event.preventDefault();
				}
		    }
	})
// 	var btnEndProcess = $('<button />',{ class:'btn btn-danger' }).html('ปิดรายการ').css({ 'margin':'0 30px 0 130px' });
// 	$(btnEndProcess).click(function (e) {

// 		e.preventDefault();
// 		var msg_comment = $('#remark').val();
// 		if( $('input[type="checkbox"]:checked').length <=0 && msg_comment=='') {
// 	        alert('กรุณาเลือกรายการเพื่อบันทึก ');
// 	        event.preventDefault();
// 	    } else {

// 	    	 if (confirm('ต้องการปิดรายการเพื่อส่งเช็คสินค้าใช่หรือไม่') == true) {
// 	    		$('#mode').val('save');

// 				$.ajax({             
// 					type: 'post',
// 					url: 'handheld/?page=rt_process',
// 					data: $('#rt_outbound').serialize(),
// 					success: function (html) {

// 						$.ajax({             
// 							type: 'post',
// 							url: 'show/outbound/update_process.php',
// 							data: {	method : 'send-to-checking-out',	rt_id : $('#rt_no').val()},
// 							success: function (html) {
// 								window.location.href = '?page=outbound_list'; 
// 							}
// 					    });
// //							console.log(html);
// //							alert('บันทึกข้อมูลแล้ว');
// 						location.reload();
// 					}
// 			    });
// 				event.preventDefault();
// 			    return false;
// 			} else {
// 				event.preventDefault();
// 			}
// 	    }
// 	})
	
	var chkCloseProcess = $('<label class="checkbox-inline"><input type="checkbox" value="" name="isCloseRT" id="closeRT">ปิดรายการ</label>');
// 	$('#table-inbound_filter').append(btnEndProcess);
	$('#table-inbound_filter').append(button);
	<?php if( $result['rt_success'] >= 1 ) { ?>
		$('#table-inbound_filter').append(chkCloseProcess);
	<?php } ?>


	$.chkauto = function(code, loca) {
		var chkbox = $('#chkbox_'+code+'_'+loca);
			chkbox.prop("checked", "checked");
		var obj = $('#take_'+code+'_'+loca);
		$.cal_qty(obj, code, loca);
	}

	$.CheckNum = function(obj, val, code, loca){
		var num = val*1;
		var amount = $('#inStore_' + code + '_' + loca ).html();
		if (isNaN(num)) {
			e.preventDefault();
			alert('กรุณากรอกตัวเลข'); 
			$(obj).val(null).focus();
			
			$.cal_diff(obj, code, loca);
			$.cal_qty(obj, code,loca);
		}else{
			
			if(num <= amount) {
				$.cal_diff(obj, code, loca);
				$.cal_qty(obj, code,loca);
			} else {
				alert('เกินจำนวนที่มีอยู่!');
				$(obj).val(null).focus();
				$.cal_diff(obj, code, loca);
				$.cal_qty(obj, code,loca);
				return false;
			}
		}
		
	}

	$.cal_diff = function(obj, code, loca) {
		var showdiff = $('#cal-diff_' + code + '_' + loca);
		
		var amount = $('#inStore_' + code + '_' + loca ).html();
		var inpval = $(obj).val();
		var total = amount - inpval;

		showdiff.html(total);
	}

	$.cal_qty = function(obj, code, loca) {
		var qtyspan = $('#cal-amt_'+code);
		var amount = $('#rt_qty_'+code).val();
		var qty_amount = $('#qty_amount_'+code).val();
		var total = qty_amount;
		var i = 0;
		$("[id^='take_"+code+"']").each(function( index ) {
			var chk_id = this.id.split("_")[2];
			if($("[id^='chkbox_"+code+"_"+chk_id+"']").is(':checked')) {
				
				var	cal_id = this.id;
				var value = $('#'+cal_id).val();
				total = (total*1)+(value*1);
			}
		});
		
		if(total > amount) {
			alert('เกินจำนวนสินค้าที่ต้องการ!');
			$(obj).val(null).focus();
			
			total = 0;
			$("[id^='take_"+code+"']").each(function( index ) {
				var chk_id = this.id.split("_")[2];
				var value = this.value;
				if($("[id^='chkbox_"+code+"_"+chk_id+"']").prop('checked')) {
					total = (total*1)+(value*1);
				}
				
			});
			return false;
		}
		qtyspan.html(total);
	}

	$.missingNum = function(obj, val, code, loca){
		var num = val*1;
		var maxval = $('#cal-diff_' + code + '_' +loca ).html();
		if (isNaN(num)) {
			event.returnValue = false;
			alert('กรุณากรอกตัวเลข'); 
			$(obj).val(null).focus();
		}else{
			var disa = $('#disappear_' + code + '_' +loca).val();
			var worn = $('#wornout_' + code + '_' +loca).val();
			var expire = $('#expire_' + code + '_' +loca).val();
			var chknum = disa*1 + worn*1 + expire*1;
			
			if(chknum == maxval) {
				event.returnValue = true;
			} else {
				/* Input 2 value then check */
				if(disa != '' && worn != '' && expire != ''){
					if(chknum > maxval) {
						alert('เกินจำนวนที่มีอยู่!');
						$(obj).val(null).focus();
						event.returnValue = false;
					} else {
						alert('กรุณาใส่จำนวนให้ครบ ' + maxval + ' !');
						$(obj).val(null).focus();
						event.returnValue = false;
					}
					
				} else {
					if(chknum > maxval) {
						alert('เกินจำนวนที่มีอยู่!');
						$(obj).val(null).focus();
						event.returnValue = false;
					}
				}
				
			}
		}
	}
	
	$.printDiv = function(divID) {
        //Get the HTML of div
        var divElements = document.getElementById(divID).innerHTML;
        //Get the HTML of whole page
        var oldPage = document.body.innerHTML;
        //Reset the page's HTML with div's HTML only
        document.body.innerHTML = 
          "<html><head><title></title></head><body>" + 
          divElements + "</body>";

        //Print Page
        window.print();

        //Restore orignal HTML
//         document.body.innerHTML = oldPage;
//         $('#myModal').modal('hide');
        window.location.reload();
    }
	

});

	
</script>