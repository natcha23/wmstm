<?php 
$search = isset($_REQUEST['search'])?$_REQUEST['search']:'';
$today = isset($_REQUEST['todate'])?$_REQUEST['todate']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$rtID = isset($_REQUEST['refid'])? $_REQUEST['refid'] : '';

?>

<div id="menu-name">ตรวจนับ</div>
<div id="menu-back"><span style="margin-left:10px"><a href="" onclick="$.backShowCheck('<?php echo $today; ?>');" border='1'>ย้อนกลับ</a></span></div>
<div style="clear:left"></div>

<b><?php echo 'RT : '.$rtID; ?></b>

<form id="frm-check">

<table border="1" bordercolor="#EEEDED" cellpadding="0" cellspacing="0">
    <tr height="20px;" bgcolor="#0099FF">
        <td align="center">รหัสสินค้า</td>
        <td>จำนวน<br>นำออก</td>
        <td>ตรวจ</td>
        <td>จำนวน<br>เช็คจริง</td>
        <!-- <td style="display:block">จำนวน<br>ผลต่าง</td> -->
        <td>Lose</td>
        <!-- <td>หมายเหตุ</td> -->
        <td style="display:none" align="center">รหัสสินค้า</td>
        <!-- <td align="center">สินค้าที่ขาด</td> -->
    </tr>
<?php
/* Query select product each RT */
$db->select("*, oc.id as chk_id, rt.id as rt_id, rt.user_id as rt_user")->from("outbound_rt AS rt");
// $db->join("outbound_items_location AS lc", "rt.id = lc.outbound_id", "LEFT");
$db->join("outbound_check AS oc", "rt.id = oc.outbound_id AND oc.status = 0", "LEFT");
$db->where(array(
		"rt.status" => 0,
		"rt.rt_refid" => $rtID,
// 		"rt.rt_success" => 0
		
));
$db->group_by("barcode");

$sql = $db->get();
$results = $sql->result_array();
$loop = 0;

foreach ($results as $row) {
	
	$db->select("*")->from("report_missing");
	$db->where(array("rt_refid" => $rtID,
			"barcode" => $row['barcode'],
			"type" => "CHK"
	));
	$sql = $db->get();
	$missing_arr = $sql->row();
	
	
	/*   */
	
?>
    <tr <?php if($loop%2 == 0) {?> bgcolor="#FFFFFF" <?php } else { ?> bgcolor="#D8D8D8" <?php } ?>>
        <td><?php echo $row['barcode'] . " [" . $row['rt_qty'] . " " . $row['unit'] . "] "; ?></td>
        <td><b><span id="ob-qty_<?php echo $row['barcode']; ?>"><?php echo $row['qty_amount'];?></span></b></td>
        <td align="center"><input type="checkbox" id="actchk_<?php echo $loop; ?>" name="data[<?php echo $loop; ?>][check_status]" <?php if ( !is_null($row['check_status']) && $row['check_status'] == 0) {?> checked <?php } ?> ></td>
        <td><input type="text" name="data[<?php echo $loop; ?>][qty]" value="<?php echo ($row['check_qty'])?$row['check_qty']:$row['qty_amount']; ?>" size="1" onKeyUp="$.checkNum(this, this.value, '<?php echo $row['barcode']; ?>')" onchange="$.clearValue(this, '<?php echo $row['barcode']; ?>');" /></td>
        <!-- 
        <td>
        	<span id="chk-diff_<?php echo $row['barcode'];?>"><?php echo ($row['check_qty'])? $row['qty_amount'] - $row['check_qty'] : 0; ?></span>
        </td>
        -->
        <td><input type="checkbox" id="ismissing" value="" onclick="$.toggleMissing(this, '<?php echo $row['barcode']; ?>');"></td>
        <!-- <td><input type="text" name="data[<?php echo $loop; ?>][check_remark]" value="<?php echo $row['check_remark']; ?>" size="3"/></td> -->
        <td style="display:none"><?php echo $row['barcode'];?></td>
        
        <input type="hidden" name="data[<?php echo $loop; ?>][outbound_id]" value="<?php echo $row['rt_id']; ?>">
		<input type="hidden" name="data[<?php echo $loop; ?>][rt_id]" value="<?php echo $row['rt_refid']; ?>">
		<input type="hidden" name="data[<?php echo $loop; ?>][chk_id]"	value="<?php echo $row['chk_id']; ?>">
		<input type="hidden" name="data[<?php echo $loop; ?>][check_qty]"	value="<?php echo $row['qty_amount']; ?>">
		
		<input type="hidden" name="data[<?php echo $loop; ?>][barcode]"	value="<?php echo $row['barcode']; ?>">
		<input type="hidden" name="data[<?php echo $loop; ?>][rt_user]"	value="<?php echo $row['rt_user']; ?>">
		<input type="hidden" name="data[<?php echo $loop; ?>][qty_amount]"	value="<?php echo $row['qty_amount']; ?>">
		
    </tr>
    	
    <tr <?php if($loop%2 == 0) {?> bgcolor="#FFFFFF" <?php } else { ?> bgcolor="#D8D8D8" <?php } ?> id="missing_zone_<?php echo $row['barcode']; ?>" style="display:none">
    	<td colspan="10">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td align="left">สินค้ามีปัญหา</td>
					<td align="right" nowrap>
			        	<label>ไม่ครบ</label>&nbsp;<input type="text" size="1" name="data[<?php echo $loop; ?>][missing][disappear]" id="disappear_<?php echo $row['barcode']; ?>" value="<?php echo $missing_arr->qty_disappear;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $row['barcode']; ?>')">
			       	</td>
			       	<td width="50px;">&nbsp;</td>
			    </tr>
			    <tr>
			    	<td></td>
					<td align="right" nowrap>
			        	<label>ชำรุด</label>&nbsp;<input type="text" size="1" name="data[<?php echo $loop; ?>][missing][wornout]" id="wornout_<?php echo $row['barcode']; ?>" value="<?php echo $missing_arr->qty_wornout;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $row['barcode']; ?>')">
			       	</td>
			       	<td></td>
			    </tr>
			    <tr>
			       	<td></td>
					<td align="right" nowrap>
			        	<label>หมดอายุ</label>&nbsp;<input type="text" size="1" name="data[<?php echo $loop; ?>][missing][expire]" id="expire_<?php echo $row['barcode']; ?>" value="<?php echo $missing_arr->qty_expire;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $row['barcode']; ?>')">
			        </td>
			        <td></td>
			    </tr>
	        	<input type="hidden" name="data[<?php echo $loop; ?>][missing][id]" value="<?php echo $missing_arr->id; ?>"/>
			</table>    	
    	</td>
    </tr>
    
    <tr height="5px" bgcolor="#0099FF">
    	<td colspan="10"></td>
    </tr>
    
<?php
	$loop++;
}
?>
</table>
<?php 
	// Remark RT
	$db->select("id, detail");
	$db->from("outbound_remarks");
	$db->where(array("rt_refid" => $rtID,
			"type" => "CHK"
	));
	
	$sql = $db->get();
	$remark = $sql->row_array();
	
	$db->select("status")->from("outbound_rt_status")->where("rt_id", $refid);
	$sql = $db->get();
	$rt_status = $sql->row('status');
?>

	<!-- <button type="button" onclick="$.closeProcess('<?php echo $today; ?>');">ปิดรายการ</button> -->
	<label><input type="checkbox" id="closeRT" name="closeRT" title="เพื่อปิดรายการหากสินค้าไม่ครบ" <?php if(!empty($rt_status) && $rt_status >= 3) { ?> checked="checked" disabled="disabled" <?php } ?>/>ปิดรายการ</label>
	<button type="submit" onclick="">บันทึก</button>
	<span style="margin-left:80px">
		<a href="" onclick="$.backShowCheck('<?php echo $today; ?>');" border='1'>ย้อนกลับ</a>
	</span>
	<input type="hidden" name="remark_id" value="<?php echo $remark['id']; ?>"> 
	<input type="hidden" id="mode" name="mode">
	<input type="hidden" id="rt_no" name="rt_no" value="<?php echo $rtID; ?>">
	<br/>
	
	<label><b>Remark :</b></label> 
	<br/>
	
	<textarea rows="3" cols="20" name="remark_chk"><?php echo $remark['detail']; ?></textarea>
	<br/>
	
</form>
	
<script>

$(function(){
	
	$.backShowCheck = function(todate) {

		var refid = '<?php echo $_POST['refid']; ?>';
		var todate = '<?php echo $_POST['todate']; ?>';
		var keyword = '<?php echo $_POST['search']; ?>';
		var params = {
				refid: refid,
				todate: todate,
				search: keyword 
			};
		
		$.postAndRedirect('?page=show_check', params);
	}
	
	$.postAndRedirect = function(url, postData)
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
	
	$('#frm-check').submit(function (e) {
		event.returnValue = false;

		if( $("input[id^='actchk_']:checked").length <=0 ) {
// 		if( $('input[type="checkbox"]:checked').length <=0 ) {
	        alert('กรุณาเลือกรายการเพื่อบันทึก ');
	        event.returnValue = false;
	    } else {

	    	 if (confirm('ต้องการบันทึกข้อมูลใช่หรือไม่') == true) {

				var refid = '<?php echo $_POST['refid']; ?>';
				var todate = '<?php echo $_POST['todate']; ?>';
				var keyword = '<?php echo $_POST['search']; ?>';
				var params = {
    					refid: refid,
    					todate: todate,
    					search: keyword 
    				};
				
	    		 	$('#mode').val('save');

					$.ajax({
						type: 'post',
						url: '?page=check_process',
						data: $('#frm-check').serialize(),
						success: function (html) {
	// 						alert('บันทึกข้อมูลแล้ว');console.log(html);
							/* เช็คว่า มีการเช็คให้ปิดรายการ หรือไม่ */
							var is_close = 'Y';
			        		if($('#closeRT').prop('checked') == true) {
			    	    		is_close = 'Y';
			        		} else {
			        			is_close = 'N';
			        		}
			        		/* Close this RT. */
			    			if(is_close == "Y") {
			    			 	$.ajax({
			    					type: 'post',
			    					url: '?page=update_process',
			    					data: {	'method' : 'send-to-transport',
			    							'rt_id' : $('#rt_no').val()},
			    					success: function (html) {
// 			    						$.postAndRedirect('?page=show_check', params);
// 			    						event.returnValue = false;
			    					}
			    			    });
			    			}
							/* Redirect page */
			    			$.postAndRedirectNoEventReturn('?page=show_check', params);
						}
				    });

				event.returnValue = false;
// 				window.location.href=window.location.href;
			} else {
				event.returnValue = false;
			}
	    }
	});

	$.checkNum = function(obj, val, code){
		var num = val*1;
		var maxval = $('#ob-qty_' + code ).html();
		if (isNaN(num)) {
			event.returnValue = false;
			alert('กรุณากรอกตัวเลข'); 
			$(obj).val(null).focus();
			
			$.cal_diff(obj, code, loca);
		}else{
			
			if(num <= maxval) {
				$.cal_diff(obj, code);
			} else {
				alert('เกินจำนวนที่มีอยู่!');
				$(obj).val(null).focus();
				$.cal_diff(obj, code);
				event.returnValue = false;
			}
		}
	}

	$.cal_diff = function(obj, code) {
		
		var showdiff = $('#chk-diff_' + code);
		var amount = $('#ob-qty_' + code ).html();
		var inpval = $(obj).val();
		var total = amount - inpval;
		
		showdiff.html(total);
	}
	$.clearValue = function(obj, code) {
		var _val = obj.value;
		$('#disappear_' + code).val('');
		$('#expire_' + code).val('');
		$('#wornout_' + code).val('');
	}

	$.missingNum = function(obj, val, code){
		var num = val*1;
		var maxval = $('#chk-diff_' + code ).html();
		if (isNaN(num)) {
			event.returnValue = false;
			alert('กรุณากรอกตัวเลข'); 
			$(obj).val(null).focus();
		}else{
			var disa = $('#disappear_' + code).val();
			var worn = $('#wornout_' + code).val();
			var expire = $('#expire_' + code).val();
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

	$.toggleMissing = function(obj, prod){
		if(obj.checked) {
			$('#missing_zone_' + prod).show();
		} else {
			$('#disappear_' + prod).val('');
			$('#wornout_' + prod).val('');
			$('#expire_' + prod).val('');
			
			$('#missing_zone_' + prod).hide();
		}
	}

	$.closeProcess = function(todate) {
//			event.returnValue = false;
	 if (confirm('ต้องการปิดรายการเพื่อนำสินค้าส่งใช่หรือไม่') == true) {
		$.ajax({
			type: 'post',
			url: '../show/outbound/update_process.php',
			data: {	'method' : 'send-to-transport',
					'rt_id' : $('#rt_no').val()},
			success: function (html) {
//	 			alert('บันทึกข้อมูลแล้ว');
		 		location.reload();
// 			 	console.log(html);
 				var params = {	todate: todate	};
//  				$.postAndRedirect('?page=show_check', params);
			}
	    });
		event.returnValue = false;
	} else {
		event.returnValue = false;
	}

}
	
});

</script>	
	
	
	
	
	