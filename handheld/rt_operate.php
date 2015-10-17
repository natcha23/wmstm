<?php
// $connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
// $conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
// sqlsrv_query("SET NAMES UTF8");

$rtID = isset($_REQUEST['refid'])? $_REQUEST['refid'] : '';
$prodID = isset($_REQUEST['prod'])? $_REQUEST['prod'] : '';
$todate = isset($_REQUEST['todate'])? $_REQUEST['todate'] : '';
$product_amount = isset($_REQUEST['amt'])? $_REQUEST['amt'] : '';
$allproductinrt = isset($_REQUEST['allamt'])? $_REQUEST['allamt'] : '';

$user_id = $_SESSION['userID'];

$condition = '';
	$cond_date = " AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '{$yesterday}', 121) AND CONVERT(datetime, '{$eoftoday}', 121)) ";

	$arr_cond = array(
// 			"DT.DT_PROPERTIES IN ('211','304')",
// 			"DT.DT_KEY <> '2'",
// 			"WL.WL_KEY in( '1','111','165')",
// 			"WL_TO.WL_CODE <> '0499'",
			"DI.DI_ACTIVE=0",
			"DT.DT_ENABLE='Y'"
	);

	foreach ($arr_cond as $where) {
		$cond .= ($cond)?  " AND " . $where : " WHERE " . $where;
	}

	$condition = $cond . $cond_date;

	$condition = $cond . " AND DI.DI_REF = '{$rtID}' ";
	$condition .= " AND TD.TRD_SH_CODE = '{$prodID}'";

	$sql_fields = "DI.DI_KEY, DI.DI_CRE_DATE, DI.DI_REF, DI.DI_REMARK,
				TD.TRD_SH_CODE, TD.TRD_QTY, TD.TRD_UTQNAME, GDM.GOODS_ALIAS";

	$sql	= "SELECT ". $sql_fields . "
							FROM  DOCINFO AS DI
							LEFT JOIN  TRANSTKH AS TH ON DI.DI_KEY = TH.TRH_DI
							INNER JOIN TRANSTKD AS TD ON TH.TRH_KEY = TD.TRD_TRH
							INNER JOIN WARELOCATION AS WL ON WL.WL_KEY = TD.TRD_WL
							INNER JOIN WARELOCATION AS WL_TO on TD.TRD_TO_WL = WL_TO.WL_KEY
							INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
							INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY
							LEFT JOIN GOODSMASTER AS GDM ON TD.TRD_SH_CODE = GDM.GOODS_CODE
	                      ".$condition;

// 	$sql .= ' ORDER BY DI.DI_REF, TD.TRD_SEQ ';
// 	echo $sql;exit;
	$params = array();
// 	$stmt = sqlsrv_query( $conn, $sql, $params );
// 	if( $stmt === false) {
// 		die( print_r( sqlsrv_errors(), true) );
// 	}

// 	while( $rows = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
// 		$rows['DI_CRE_DATE'] = $rows['DI_CRE_DATE']->format('Y-m-d H:i:s');
// 		$srv_row = $rows;
// 	}

	/* Free the statement and connection resources. */
// 	sqlsrv_free_stmt( $stmt);
// 	sqlsrv_close( $conn);

	
	$rtID = isset($_REQUEST['refid'])? $_REQUEST['refid'] : '';
	
	// del //
	$db->select("*, rt.id AS rt_id")->from("outbound_rt AS rt");
	$db->join("outbound_items_location AS lo", "rt.id = lo.outbound_id", "LEFT");
	$db->where("rt.rt_refid = '$rtID'");
	$db->where("rt.barcode = '$prodID'");
	$db->where("rt.status = 0");
	
	$sql = $db->get();
// 	_print($db->last_query());exit;
	$outs = $sql->result_array();
	
	foreach( $outs as $srv_row ) {
	
		$srv_row['DI_REF'] = $srv_row['rt_refid'];
		$srv_row['GOODS_ALIAS'] = $srv_row['goods_name'];
		$srv_row['TRD_UTQNAME'] = $srv_row['unit'];
		$srv_row['TRD_SH_CODE'] = $srv_row['barcode'];
		$srv_row['DI_CRE_DATE'] = $srv_row['rt_date'];
		$srv_row['TRD_QTY'] = $srv_row['rt_qty'];
		
	
		$results[] = $srv_row;
		$rt_date = $srv_row['rt_date'];
		
// 		$allproductinrt += $srv_row['TRD_QTY']; // don't delete
	}
	// del //

	$qty = explode(".", $srv_row['TRD_QTY']);

	$result_arr[$srv_row['TRD_SH_CODE']] = $srv_row;
	
	
	// check have product in stock
	$db->select("*");
	$db->from("stock_product");
	$db->where("product_id = {$srv_row['TRD_SH_CODE']}");
	$db->where("product_qty > 0");
	
	$sql_chkstock = $db->get();
	$chkstock = $sql_chkstock->num_rows();
	$stock_prod = $sql_chkstock->row_array();
	
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
				// 			"rt_success" => 1
				'ort.status' => 0
		));
		$db->group_by("ort.barcode");

		$sql = $db->get();
		$outbound_arr = $sql->result_array();
	}
// 	_print($srv_row);
?>

<div id="menu-name">นำออก</div>
<div id="menu-back"><a href="" onclick="$.backItems('<?php echo $rtID; ?>', '<?php echo $todate; ?>');">ย้อนกลับ</a></div>
<div style="clear:left"></div>

<form action="?page=rt_process" method="POST" id="rt_outbound" style="width:220px;">
		<div>RT NO: <span><?php echo $rtID; ?></span></div>
		<div>Barcode: <span><?php echo $srv_row['TRD_SH_CODE']; ?></span></div>
		<div>Goods Name: <span><?php echo $srv_row['GOODS_ALIAS'];?></span></div>
		<div>RT Qty: <span><b><?php echo $qty[0]; ?></b><?php echo "  " . $srv_row['TRD_UTQNAME']?></span>&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;Already: <span><font color="#006633"><b><?php echo (!empty($srv_row['qty']))?$srv_row['qty']:0; ?></b></font><?php echo "  " . $srv_row['TRD_UTQNAME']?></span></div>
	<p></p>
	<div>

		<table border="1" bordercolor="#EEEDED" cellpadding="0" cellspacing="0" >
			<thead>
				<tr height="20px;" bgcolor="#0099FF">
					<td></td>
					<td>ที่เก็บ</td>
					<td align="center">จำนวนที่มี</td>
					<td align="center">นำออก</td>
					<!-- <td>ผลต่าง</td>
					<td align="center">หมายเหตุ</td>
					<td align="center">สินค้าที่หาย</td> -->
					<td style="display: none">Blank</td>
					<td>Lose</td>
				</tr>
			</thead>
			<tbody>
<?php

// foreach($result_arr as $key => $result) {
	/////////// FIFO & FEFO ///////////////////
	$item = $_REQUEST['prod'];

// 	$srv_row['rt_success'] = 1;
	if(!empty($outbound_arr)) {
		foreach($outbound_arr as $outbval) {
			if($srv_row['TRD_SH_CODE'] == $outbval['barcode']) {
				$srv_row['rt_success'] = $outbval['rt_success'];
				$srv_row['qty_amount'] = $outbval['qty_amount'];
				$srv_row['rt_id'] = $outbval['rt_id'];
			}
		}
	}
	// check have product in stock
	$db->select("*");
	$db->from("stock_product");
	$db->where("product_id = $item");
	$db->where("product_qty > 0");

	$sql_chkstock = $db->get();
	$chkstock = $sql_chkstock->num_rows();
	$stock_prod = $sql_chkstock->row_array();

	if( !empty($chkstock) ) {

		// filter category
		$db->select("ip.product_fefo");
		$db->from("inbound_po AS ip");
		$db->where(array("ip.product_no" => $item));

		$sql_fefo = $db->get();
		// echo $db->last_query().'<br>';
		$isFEFO = $sql_fefo->row_array();
		$where = "ip.product_no = '$item'";
		$where .= " AND (il.qty_remain > '0' )";

		if($isFEFO['product_fefo'] != 0) {
			// FEFO
			$order = "product_fefo_date ASC ";

			$db->select("ip.po_id, ip.product_no, ip.product_unit, ip.product_date_in, ip.product_fefo, ip.product_fefo_date, il.inbound_id, il.qty, il.qty_remain, il.user_id AS inbound_user, ad.add_id, ad.add_name");
			$db->select("il.inbound_location_id");
			$db->from("inbound_po AS ip");
			$db->join("inbound_location AS il", "ip.inbound_id = il.inbound_id", "LEFT");
//			$db->join("outbound_transaction AS ots", "il.inbound_location_id = ots.inbound_location_id", "LEFT");
			$db->join("tb_address AS ad", "il.location_id = ad.add_id", "INNER");
			$db->where($where);
			$db->order_by($order);

		} else {
			// FIFO
			$order = "product_date_in ASC ";

			$db->select("ip.po_id, ip.product_no, ip.product_unit, ip.product_date_in, ip.product_fefo, ip.product_fefo_date, il.inbound_id, il.qty, il.qty_remain, il.user_id AS inbound_user, ad.add_id, ad.add_name");
			$db->select("il.inbound_location_id");
			$db->from("inbound_po AS ip");
			$db->join("inbound_location AS il", "ip.inbound_id = il.inbound_id", "LEFT");
// 			$db->join("outbound_transaction AS ots", "il.inbound_location_id = ots.inbound_location_id", "LEFT");
			$db->join("tb_address AS ad", "il.location_id = ad.add_id", "INNER");
			$db->where($where);
			$db->order_by($order);

		}
		$sql = $db->get();
		// 			echo $db->last_query() . '</br>';exit;
		$result = $sql->result_array();

		/* $result[0] = array("add_id" => "241", "add_name" => "AA-01-A",
				"qty_remain" => 10,
				"inbound_user" => 21,
		);
		$result[1] = array("add_id" => "256", "add_name" => "AA-01-B",
				"qty_remain" => 30,
				"inbound_user" => 21,
		); */
		
	}

	////////// END FIFO & FEFO ////////////////////
	$i++;
	$qty_exp = explode(".", $srv_row['TRD_QTY']);
	if(count($qty_exp) > 0) {
		$qty = $qty_exp[0];
	}else{
		$qty = $srv_row['TRD_QTY'];
	}

	$taken = ($srv_row['qty_amount'])? $srv_row['qty_amount']: "0";
	
	
	/* Offer location */
	
	$qtyCheck = true;
	$qtyAmount = 0;
	$countLocation = count($result);
	$remain = $qty - $srv_row['qty_amount'];
	for($l=0;$l<$countLocation;$l++) {

		$decision_qty = 0;
		$isChecked = 0;
			
		if( ($qtyCheck) && ($qty > $srv_row['qty_amount']) ) {
				
			if( $remain >= $result[$l]['qty_remain'] ) {
				$decision_qty = $result[$l]['qty_remain'];
			}
			if( $remain < $result[$l]['qty_remain'] ) {
				$decision_qty = $remain;
			}
				
			$isChecked = 1;
			$qtyAmount+=$decision_qty;
			$remain = $qty - ($srv_row['qty_amount'] + $qtyAmount);
			if($remain == 0) {
				$qtyCheck = false;
			}
		}
			
		$result[$l]['offer'] = $isChecked;
		$result[$l]['offer_qty'] = $decision_qty;
		
	}
	/* Offer location */
	
	if(!empty($result)) {
		$j=0;
		foreach($result as $row) {
			
			/* Select report missing */
			$db->select("*")->from("report_missing");
			$db->where(array("rt_refid" => $srv_row['DI_REF'],
					"barcode" => $srv_row['TRD_SH_CODE'],
					"type" => "RT",
					"add_id" => $row['add_id']
			));
			$sql = $db->get();
			$missing_arr = $sql->row();
			
			$offer_qty = $row['qty_remain'];
			if($row['offer_qty'] <> 0) {
				$offer_qty = $row['offer_qty'];
			}
			
?>

				<tr <?php if($j % 2 == 0) {?> bgcolor="#FFFFFF" <?php } else { ?> bgcolor="#D8D8D8" <?php } ?>>
					<td><input type='checkbox' name='chk_list[]' id="chkbox_<?php echo $srv_row['TRD_SH_CODE']."_".$row['add_id']; ?>" value="<?php echo $srv_row['TRD_SH_CODE']."_".$row['add_id']; ?>"
						onclick="$.cal_qty($('#take_<?php echo $srv_row['TRD_SH_CODE']."_".$row['add_id']; ?>'), '<?php echo $srv_row['TRD_SH_CODE'];?>', '<?php echo $row['add_id']; ?>');" <?php if($row['offer']==1) { echo "checked"; } ?>></td>
					<td><b><?php echo $row['add_name']; ?></b></td>
					<td id="inStore_<?php echo $srv_row['TRD_SH_CODE']."_".$row['add_id'];?>" align="center"><?php echo $row['qty_remain']; ?></td>
					<td><input type="text" size="3" id="take_<?php echo $_REQUEST['prod']."_".$row['add_id']; ?>" name="data[0][location][<?php echo $j; ?>][qty]" value="<?php echo number_format($offer_qty); ?>"
						onfocus="$.chkauto('<?php echo $srv_row['TRD_SH_CODE']?>', '<?php echo $row['add_id'] ?>');" onKeyUp="$.CheckNum(this, this.value, '<?php echo $srv_row['TRD_SH_CODE']?>', '<?php echo $row['add_id'] ?>')"></td>
					<td style="display:none"><span id="cal-diff_<?php echo $_REQUEST['prod']; ?>_<?php echo $row['add_id']; ?>"><?php echo $row['qty_remain'] - $offer_qty; ?></span></td>
					<!-- <td><input type="text" size="5" name="data[0][location][<?php echo $j; ?>][remark]" value="<?php echo $missing_arr->detail; ?>" onfocus="$.chkauto('<?php echo $srv_row['TRD_SH_CODE']?>', '<?php echo $row['add_id'] ?>');"></td> -->
					
					<td style="display: none"><input type="checkbox" name="data[blank_address][]" value="<?php echo $row['add_id'] ?>"></td>
					<td><input type="checkbox" id="ismissing_<?php echo $srv_row['TRD_SH_CODE']; ?>_<?php echo $row['add_id']; ?>" value="" onclick="$.toggleMissing(this, '<?php echo $srv_row['TRD_SH_CODE']; ?>', '<?php echo $row['add_id']; ?>');"></td>
					
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][outbound_id]" value="<?php echo $row['rt_id']; ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][rt_id]" value="<?php echo $srv_row['DI_REF']; ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][chk_id]"	value="<?php echo $row['chk_id']; ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][check_qty]"	value="<?php echo $row['qty_amount']; ?>">
					
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][barcode]"	value="<?php echo $srv_row['TRD_SH_CODE']; ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][inbound_user]"	value="<?php echo $row['inbound_user']; ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][qty_amount]"	value="<?php echo $row['qty_remain']; ?>">
					
					
					<input type="hidden" name="data[0][id]"	value="<?php echo $srv_row['rt_id']; ?>">
					<input type="hidden" name="data[0][rt_refid]" value="<?php echo $srv_row['DI_REF']; ?>">
					<input type="hidden" name="data[0][rt_qty]"	value="<?php echo number_format($qty); ?>" id="rt_qty_<?php echo $srv_row['TRD_SH_CODE']; ?>">
					<input type="hidden" name="data[0][rt_date]" value="<?php echo $srv_row['DI_CRE_DATE']; ?>">
					<input type="hidden" name="data[0][barcode]" value="<?php echo $srv_row['TRD_SH_CODE']; ?>">
					<input type="hidden" name="data[0][goods_name]"	value="<?php echo $srv_row['GOODS_ALIAS']; ?>">
					<input type="hidden" name="data[0][unit]" value="<?php echo $srv_row['TRD_UTQNAME']; ?>">
					<input type="hidden" name="data[0][qty_amount]" value="<?php echo $srv_row['qty_amount']; ?>">

					<input type="hidden" id="qty_amount_<?php echo $srv_row['TRD_SH_CODE']; ?>" value="<?php echo $taken; ?>">

					<input type="hidden" name="data[0][location][<?php echo $j; ?>][add_id]" value="<?php echo $row['add_id'] ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][id]" value="<?php echo $row['id'] ?>">

					<input type="hidden" name="data[0][location][<?php echo $j; ?>][location_qty]" value="<?php echo $row['qty_remain'] ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][qty_remain]" value="<?php echo $row['qty_remain'] ?>">
					<input type="hidden" name="data[0][location][<?php echo $j; ?>][inbound_location_id]" value="<?php echo $row['inbound_location_id'] ?>">
				</tr>
				
				<tr <?php if($j % 2 == 0) {?> bgcolor="#FFFFFF" <?php } else { ?> bgcolor="#D8D8D8" <?php } ?> id="missing_zone_<?php echo $srv_row['TRD_SH_CODE']; ?>_<?php echo $row['add_id']; ?>" style="display:none">
					<td colspan="10">
						<table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td align="left">สินค้ามีปัญหา</td>
								<td align="right" nowrap>
						        	<label>ไม่ครบ</label>&nbsp;<input type="text" size="1" name="data[0][location][<?php echo $j; ?>][disappear]" id="disappear_<?php echo $srv_row['TRD_SH_CODE']; ?>_<?php echo $row['add_id'] ?>" value="<?php echo $missing_arr->qty_disappear;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $srv_row['TRD_SH_CODE']; ?>', '<?php echo $row['add_id'] ?>')">
						        </td>
						        <td width="50px;">&nbsp;</td>
						   	</tr>
						   	<tr>
						   		<td></td>
						        <td align="right" nowrap>
						        	<label>ชำรุด</label>&nbsp;<input type="text" size="1" name="data[0][location][<?php echo $j; ?>][wornout]" id="wornout_<?php echo $srv_row['TRD_SH_CODE']; ?>_<?php echo $row['add_id'] ?>" value="<?php echo $missing_arr->qty_wornout;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $srv_row['TRD_SH_CODE']; ?>', '<?php echo $row['add_id'] ?>')">
						        </td>
						        <td></td>
						    </tr>
						    <tr>
						    	<td></td>
						        <td align="right" nowrap>
						        	<label>หมดอายุ</label>&nbsp;<input type="text" size="1" name="data[0][location][<?php echo $j; ?>][expire]" id="expire_<?php echo $srv_row['TRD_SH_CODE']; ?>_<?php echo $row['add_id'] ?>" value="<?php echo $missing_arr->qty_expire;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $srv_row['TRD_SH_CODE']; ?>', '<?php echo $row['add_id'] ?>')">
						        </td>
						        <td></td>
						    </tr>
						   	<input type="hidden" name="data[0][location][<?php echo $j; ?>][rep_id]" value="<?php echo $missing_arr->id; ?>"/>
						</table>
					</td>
				</tr>
				
				<tr height="5px" bgcolor="#0099FF">
			    	<td colspan="10"></td>
			    </tr>

<?php
			$j++;
		}
?>
		<tr height="20px" bgcolor="#FFFACD">
			<td colspan="3"><b>Total</b></td>
			<td><b><font size="2px"><span id="cal-amt_<?php echo $srv_row['TRD_SH_CODE']; ?>"><?php echo $taken; ?></span></font></b></td>
			<td></td>
		</tr>

<?php
	} else {
?>
		<tr height="20px">
			<td colspan="10" align="center"><font color="red"><i>-- Data empty --</i></font></td>
		</tr>

<?php
	}
?>
			</tbody>
		</table>
	</div>
	<button type="submit" onclick="">บันทึก</button>
	<span style="margin-left:150px"><a href="" onclick="$.backItems('<?php echo $rtID; ?>', '<?php echo $todate; ?>');">ย้อนกลับ</a></span>
	<input type="hidden" id="mode" name="mode"/>
	
	<input type="hidden" name="rt_no" id="rt_no" value="<?php echo $_REQUEST['refid']; ?>">
	<input type="hidden" id="product_amount" name="product_amount" value="<?php echo $product_amount; ?>">
	<input type="hidden" id="allproductinrt" name="allproductinrt" value="<?php echo $allproductinrt; ?>">
	<input type="hidden" id="finddate" value="<?php echo $todate; ?>">
	<input type="hidden" id="prod_id" value="<?php echo $prodID; ?>">
	
	<input type="hidden" name="rt_date" value="<?php echo $srv_row['DI_CRE_DATE']; ?>">
	<input type="hidden" name="rt_branch" value="<?php echo $srv_row['shipto_id']; ?>">
	
	<input type="hidden" name="data[<?php echo $i; ?>][allproduct]"	value="<?php echo $stock_prod['product_qty']; ?>">

</form>
<!-- <div id="menu-back" align="right" style="width: 180px;"><a href="" onclick="$.backItems('<?php echo $rtID; ?>', '<?php echo $todate; ?>');">ย้อนกลับ</a></div> -->
<script>
var BASE_URL = "<?php echo _BASE_URL_; ?>";
$(function(){

	$("[id^='chkbox_']").each(function( index ) {
		var point = this.id.split('_');	
		var code = point[1];
		var loca = point[2];

		if($(this).prop('checked') == false) {
			$('#take_'+code+'_'+loca).prop('disabled', true);
			$('#ismissing_'+code+'_'+loca).prop('disabled', true);
		}
	});
	$("[id^='chkbox_']").change(function( index ) {
		var point = this.id.split('_');	
		var code = point[1];
		var loca = point[2];

		if($(this).prop('checked') == false) {
			$('#take_'+code+'_'+loca).prop('disabled', true);
			$('#ismissing_'+code+'_'+loca).prop('disabled', true);
		} else {
			$('#take_'+code+'_'+loca).prop('disabled', false);
			$('#ismissing_'+code+'_'+loca).prop('disabled', false);
		}
	});
	
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

	/* Save */
	$('#rt_outbound').submit(function (e) {
		var refid = $('#rt_no').val();
		var todate = $('#finddate').val();
		var prod_amt = $('#product_amount').val();
		var rt_total_prod = $('#allproductinrt').val();
		var prod_id = $('#prod_id').val();

		var params = {
				refid: refid,
				todate: todate,
				prod: prod_id,
				amt: prod_amt,
				allamt: rt_total_prod
			};
		
// 		event.returnValue = false;
		if( $('input[type="checkbox"]:checked').length <=0 ) {
	        alert('กรุณาเลือกรายการเพื่อบันทึก ');
	        event.returnValue = false;
	    } else {

	    	 if (confirm('ต้องการบันทึกข้อมูลใช่หรือไม่') == true) {
	    		 	$('#mode').val('save');
				$.ajax({
					type: 'post',
					url: '?page=rt_process',
					data: $('#rt_outbound').serialize(),
					success: function (html) {
// 						console.log(html);
// 						alert('บันทึกข้อมูลแล้ว');
// 	 					if(html == 'success') {
							
			 				$.postAndRedirectNoEventReturn('?page=rt_items', params);
// 	 					}
					}
			    });
				event.returnValue = false;
			} else {
				event.returnValue = false;
			}
	    }
	});

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
			event.returnValue = false;
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

	$.backItems = function(refid, todate) {

		var params = {
				refid: refid,
				todate: todate
			};
		
		$.postAndRedirect('?page=rt_items', params);
	}

	$.postAndRedirect = function(url, postData)
	{
	    var postFormStr = "<form method='POST' action='" + url + "'>\n";
	    
	    for (var key in postData)
	    {
	        if (postData.hasOwnProperty(key))
	        {
	            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'></input>";
	            
	        }
	    }

	    postFormStr += "</form>";
	    var formElement = $(postFormStr);
		event.returnValue=false;
	    $('body').append(formElement);
	    $(formElement).submit();
	}

	$.postAndRedirectNoEventReturn = function(url, postData)
	{
		
	    var postFormStr = "<form method='POST' action='" + url + "'>\n";
	    
	    for (var key in postData)
	    {
	        if (postData.hasOwnProperty(key))
	        {
	            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'/>";
	            
	        }
	    }
	    
	    postFormStr += "</form>";
	    var formElement = $(postFormStr);
	    $('body').append(formElement);
	    $(formElement).submit();
	}

	$.toggleMissing = function(obj, prod, loca){
		if(obj.checked) {
			$('#missing_zone_' + prod + '_' +loca).show();
		} else {
			$('#disappear_' + prod + '_' +loca).val('');
			$('#wornout_' + prod + '_' +loca).val('');
			$('#expire_' + prod + '_' +loca).val('');
			
			$('#missing_zone_' + prod + '_' +loca).hide();
		}
	}

});


</script>