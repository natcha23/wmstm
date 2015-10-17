<?php 
$search = isset($_GET['search'])?$_GET['search']:'';
$finddate = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 07:00:00';
$eoftoday = $finddate.' 06:59:59';

$user_id = $_SESSION['userID'];

?>
<div class="ibox-title">
	<table id="table-outbound-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>RT Number</th>
				<th>RT Date</th>
				<th>Branch</th>
				<th>Progress Date</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
<?php 
/* Query select */

$db->select("rt.*");
$db->select("status.rt_branch, status.status AS chk_status");
$db->from("outbound_rt AS rt");
$db->join("outbound_rt_status AS status", "rt.rt_refid = status.rt_id", "LEFT");

// $db->where(array("rt.rt_success" => 0));
// $db->where("status.status BETWEEN '2' AND '3'");
$db->where("status.status = '2'");
$db->where("rt.rt_date BETWEEN '" .$yesterday. "' AND '" .$eoftoday. "'");

$db->group_by("rt.rt_refid");
$sql = $db->get();
$result = $sql->result_array();

$rNum = 1;
foreach ($result as $row) {
?>

			<?php
			/* Check status */
            	$db->select("*")->from("outbound_rt AS ort");
            	$db->join("outbound_check AS ochk", "ort.id = ochk.outbound_id", "LEFT");
            	
            	$db->where(array("ort.rt_refid" => $row['rt_refid'],
            			"ochk.check_status" => 0,
            			"ochk.status" => 0
            	));
            	$db->group_by("barcode");
            	$query = $db->get();
            	$count_chk = $query->num_rows();
            	
            	$db->select("*")->from("outbound_rt_status")->where(array("rt_id" => $row['rt_refid']));
            	$query = $db->get();
            	$rows = $query->row();
            	$rtlist = $rows->rt_product_amount;
            	$rtstatus = $rows->status;
            	
            	$message = "ดำเนินการ";
            	$label = "label-success";
            	
            	if ( $rtstatus == 3 ) {
            		$message = "สำเร็จ";
            		$label = "label-primary";
            	} else {
            		$message = "ดำเนินการ";
            		$label = "label-warning";
            	}
//             	if($count_chk == $rtlist) {
//             		$message = "สำเร็จ";
//             		$label = "label-primary";
//             	} else {
//             		if( ($count_chk > 0) && ($count_chk < $rtlist) ) {
//             			$message = "ดำเนินการ";
//             			$label = "label-warning";
//             		}
//             	}	
            ?>

			<tr>
				<td><?php echo $rNum; ?></td>
				<td><?php echo $row['rt_refid']; ?><span class="label <?php echo $label; ?> pull-right"><?php echo $message; ?></span></td>
				<td><?php echo $row['rt_date']; ?></td>
				<td><?php echo $row['rt_branch']; ?></td>
				<td><?php echo $row['date_update']; ?></td>
				<td>
					<a href="?page=outbound_check&rtid=<?php echo $row['rt_refid']; ?>&finddate=<?php echo $finddate; ?>"><button class="btn btn-primary">รายละเอียด</button></a>
					<!-- button class="btn btn-danger" onclick="$.delchecking('<?php echo $row['rt_refid']; ?>');">ลบ</button-->
				</td>
			</tr>
<?php 
	$rNum++;
}

?>			
		</tbody>
	</table>
</div>

<script>

$(function() {
	$('#nav-outbound').parent().addClass('active');
	$('#nav-outbound').addClass('in');
	$('#outbound_rtcheck').addClass('active');
	
	var oTable = $('#table-outbound-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-outbound-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $finddate; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd',
		autoclose: true
	});
	
	var label = $('<label />').html(' Date: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=outbound_rtcheck&finddate='+search_date;
	})
	$('#table-outbound-list_filter').append(label);
	$('#table-outbound-list_filter').append(button);

	$('.dataTables_length').parent().removeClass( 'col-sm-6' ).addClass('col-sm-4');
	$('.dataTables_filter').parent().removeClass( 'col-sm-6' ).addClass('col-sm-8');

	$.delchecking = function(del_id) {

		event.preventDefault();

    	if (confirm('ต้องการลบข้อมูลใช่หรือไม่') == true) {
			$.ajax({             
				type: 'post',
				url: 'handheld/?page=check_process',
				data: {mode: 'deletechk', del_id: del_id},
				success: function (html) {
					alert('ลบข้อมูลแล้ว');
// 					console.log(html);
					location.reload();
				}
		    });
			event.preventDefault();
		    return false;
		} else {
			event.preventDefault();
		}
	}
	
});

</script>

