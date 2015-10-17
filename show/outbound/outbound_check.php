<?php 
$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$rtid = isset($_GET['rtid'])?$_GET['rtid']:'';
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$user_id = $_SESSION['userID'];

?>
<button class="btn btn-warning" onclick="window.location.href='?page=outbound_rtcheck&finddate=<?php echo $today; ?>'">กลับสู่หน้ารายการ RT</button>
<div class="ibox-title">
<form method="POST" action="?page=outbound_process" id="check-form">
	<input type="hidden" name="mode" id="mode" />
	<table id="table-outbound-check" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>RT Number</th>
				<th>RT Date</th>
				<th>Barcode</th>
				<th>Goods Name</th>
				<th>Quantity</th>
				<th>Unit</th>
				<th>Outbound<br>Qty</th>
				<th>Check<br>Qty</th>
				<th>Diff</th>
				<th>Remark</th>
				<th>Checked</th>
				<th>Missing</th>
			</tr>
		</thead>
		<tbody>
		
		
<?php 
/* Query select product each RT */
$db->select("*, oc.id as chk_id, rt.id as rt_id ")->from("outbound_rt AS rt");
// $db->join("outbound_items_location AS lc", "rt.id = lc.outbound_id", "INNER");
$db->join("outbound_check AS oc", "rt.id = oc.outbound_id AND oc.status = 0", "LEFT");
$db->where(array(
		"rt.status" => 0,
		"rt.rt_refid" => $rtid,
// 		"rt.rt_success" => 0,
		
));
$db->group_by("barcode");

$sql = $db->get();
$results = $sql->result_array();

$loop = 1;
foreach ($results as $row) {
	
	/* Select report missing */
	$db->select("*")->from("report_missing");
	$db->where(array("rt_refid" => $rtid,
			"barcode" => $row['barcode'],
			"type" => "CHK"
	));
	$sql = $db->get();
	$missing_arr = $sql->row();
?>		
			<tr>
				<td><?php echo $loop; ?></td>
				<td><?php echo $row['rt_refid']; ?></td>
				<td><?php echo $row['rt_date']; ?></td>
				<td><?php echo $row['barcode']; ?></td>
				<td><?php echo $row['goods_name']; ?></td>
				<td><?php echo $row['rt_qty']; ?></td>
				<td><?php echo $row['unit']; ?></td>
				<td><b><span id="ob-qty_<?php echo $row['barcode']; ?>"><?php echo $row['qty_amount']; ?></span></b></td>
				<td><input type="text" name="data[<?php echo $loop; ?>][qty]" value="<?php echo ($row['check_qty'])?$row['check_qty']:$row['qty_amount']; ?>" size="3" onchange="$.clearValue(this, '<?php echo $row['barcode']; ?>');" onKeyUp="$.checkNum(this, this.value, '<?php echo $row['barcode']; ?>')"></td>
				<td><span id="chk-diff_<?php echo $row['barcode'];?>"><?php echo ($row['check_qty'])? $row['qty_amount'] - $row['check_qty'] : 0; ?></span></td>
				<td><input type="text" name="data[<?php echo $loop; ?>][check_remark]" value="<?php echo $row['check_remark']; ?>" size="10"></td>
				<td><input type="checkbox" name="data[<?php echo $loop; ?>][check_status]" <?php if ( !is_null($row['check_status']) && $row['check_status'] == 0) {?> checked <?php } ?>></td>
				
				<td nowrap>
		        	<label>ไม่ครบ</label>&nbsp;<input type="text" size="1" name="data[<?php echo $loop; ?>][missing][disappear]" id="disappear_<?php echo $row['barcode']; ?>" value="<?php echo $missing_arr->qty_disappear;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $row['barcode']; ?>')">
		        	<label>ชำรุด</label>&nbsp;<input type="text" size="1" name="data[<?php echo $loop; ?>][missing][wornout]" id="wornout_<?php echo $row['barcode']; ?>" value="<?php echo $missing_arr->qty_wornout;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $row['barcode']; ?>')">
		        	<label>หมดอายุ</label>&nbsp;<input type="text" size="1" name="data[<?php echo $loop; ?>][missing][expire]" id="expire_<?php echo $row['barcode']; ?>" value="<?php echo $missing_arr->qty_expire;?>" onKeyUp="$.missingNum(this, this.value, '<?php echo $row['barcode']; ?>')">
		        	<input type="hidden" name="data[<?php echo $loop; ?>][missing][id]" value="<?php echo $missing_arr->id; ?>"/>
		        </td>
        
				<input type="hidden" name="data[<?php echo $loop; ?>][outbound_id]" value="<?php echo $row['rt_id']; ?>">
				<input type="hidden" name="data[<?php echo $loop; ?>][rt_id]" value="<?php echo $row['rt_refid']; ?>">
				<input type="hidden" name="data[<?php echo $loop; ?>][chk_id]"	value="<?php echo $row['chk_id']; ?>">
				<input type="hidden" name="data[<?php echo $loop; ?>][check_qty]"	value="<?php echo $row['qty_amount']; ?>">
				
				<input type="hidden" name="data[<?php echo $loop; ?>][barcode]"	value="<?php echo $row['barcode']; ?>">
				<input type="hidden" name="data[<?php echo $loop; ?>][rt_user]"	value="<?php echo $row['rt_user']; ?>">
				<input type="hidden" name="data[<?php echo $loop; ?>][qty_amount]"	value="<?php echo $row['qty_amount']; ?>">
			</tr>
			
<?php 
	$loop++;
}
/* Remark checker */
$db->select("id, detail");
$db->from("outbound_remarks");
$db->where(array("rt_refid" => $rtid,
		"type" => "CHK"
));

$sql = $db->get();
$remark = $sql->row_array();
?>		

		</tbody>
	</table>
	
	<label>Remark:</label> 
	<br/>
	<textarea rows="5" cols="80" name="remark_chk"><?php echo $remark['detail']; ?></textarea>
	<input type="hidden" name="remark_id" value="<?php echo $remark['id']; ?>"> 
	<input type="hidden" name="rt_no" id="rt_no" value="<?php echo $rtid; ?>">
	
	<input type="hidden" id="finddate" value="<?php echo $today; ?>"/>
</form>
</div>
<script>

$(function() {
	$('#nav-outbound').parent().addClass('active');
	$('#nav-outbound').addClass('in');
	$('#outbound_rtcheck').addClass('active');
	
	var oTable = $('#table-outbound-check').dataTable({
		"pageLength": 100
	});

	var search = $('#table-outbound-check_filter').find('input[type="search"]');
	$(search).addClass( "pull-right" );
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

	var button = $('<button />',{ class:'btn btn-primary' }).html('บันทึก').css({ 'margin':'0 30px 0 130px' });
	$(button).click(function (e) {

// 	$.checking = function() {
		
		e.preventDefault();
		if( $('input[type="checkbox"]:checked').length <=0 ) {
			alert('กรุณาเลือกรายการเพื่อบันทึก ');
			
		} else {
			
			if (confirm("ต้องการบันทึกข้อมูลใช่หรือไม่") == true) {
				$('#mode').val('save');
				$.ajax({
					type: 'post',
					url: 'handheld/?page=check_process',
					data: $('#check-form').serialize(),
					success: function(html) {
// 						alert("บันทึกข้อมูลเรียบร้อยแล้ว");
						location.reload();
					}
				});

			} else {
				event.returnValue = false;
			}
		}
		
	});

	var btnEndProcess = $('<button />',{ class:'btn btn-danger' }).html('ปิดรายการ').css({ 'margin':'0 30px 0 130px' });
	$(btnEndProcess).click(function (e) {

		e.preventDefault();
		var msg_comment = $('#remark').val();
		if( $('input[type="checkbox"]:checked').length <=0 && msg_comment=='') {
	        alert('กรุณาเลือกรายการเพื่อบันทึก ');
	        event.preventDefault();
	    } else {

	    	 if (confirm('ต้องการปิดรายการเพื่อนำสินค้าส่งใช่หรือไม่') == true) {
	    		$('#mode').val('save');

				$.ajax({             
					type: 'post',
					url: 'handheld/?page=check_process',
					data: $('#check-form').serialize(),
					success: function (html) {

						$.ajax({             
							type: 'post',
							url: 'show/outbound/update_process.php',
							data: {	method : 'send-to-transport',	rt_id : $('#rt_no').val()},
							success: function (html) {
								window.location.href = '?page=outbound_rtcheck&finddate='+$('#finddate').val();
							}
					    });
//						console.log(html);alert('บันทึกข้อมูลแล้ว');
// 						location.reload();
					}
			    });
				event.preventDefault();
			    return false;
			} else {
				event.preventDefault();
			}
	    }
	})
	
	$('#table-outbound-check_filter').append(btnEndProcess);
	$('#table-outbound-check_filter').append(button);

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
	
});
	

</script>
