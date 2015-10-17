<?php 
$search = isset($_GET['search'])?$_GET['search']:'';
$finddate = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 07:00:00';
$eoftoday = $finddate.' 06:59:59';

$user_id = $_SESSION['userID'];


?>

	<table id="table-report-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>RT Number</th>
				<th>RT Date</th>
				<th>Product</th>
				<th>Disappear</th>
				<th>Worn-out</th>
				<th>Expire</th>
				<th>Operator</th>
				
				<th style="display:none">Action</th>
			</tr>
		</thead>
		<tbody>
<?php 

$refid = 'DBN255807/0011';
/* Select report*/
$db->select("rep.*, ort.goods_name, user.user_pname, user.user_fname, user.user_lname")->from("report_missing AS rep");
$db->join("outbound_rt AS ort", "rep.barcode = ort.barcode", "LEFT");
// $db->join("stock_product AS stock", "rep.barcode = stock.product_id", "LEFT");
$db->join("user", "rep.user_id = user.user_id", "LEFT");
$db->where(array("rep.status" => 0,
// 		"date(rep.rt_date)" => $finddate,
		"rep.rt_refid" => $refid
));
// $db->group_by("rt_refid");
$sql = $db->get();
// _print($db->last_query());exit;
$result = $sql->result_array();
_print($result[0]);
$rNum = 1;
foreach ($result as $row) {
?>
			<tr>
				<td><?php echo $rNum; ?></td>
				<td><?php echo $row['rt_refid']; ?><span class="label <?php echo $label; ?> pull-right"><?php echo $message; ?></span></td>
				<td><?php echo $row['rt_date']; ?></td>
				<td><?php echo $row['goods_name']; ?></td>
				<td><?php echo $row['qty_disappear']; ?></td>
				<td><?php echo $row['qty_wornout']; ?></td>
				<td><?php echo $row['qty_expire']; ?></td>
				<td><?php echo $row['user_fname'] . " " . $row['user_lname']; ?></td>
				
				<td style="display:none">
					<a href="?page=outbound_check&rtid=<?php echo $row['rt_refid']; ?>&finddate=<?php echo $finddate; ?>"><button class="btn btn-primary">รายละเอียด</button></a>
					<button class="btn btn-danger" onclick="$.delchecking('<?php echo $row['rt_refid']; ?>');">ลบ</button>
				</td>
			</tr>
<?php 
	$rNum++;
}

?>			
		</tbody>
	</table>
<script>

$(function() {
	var oTable = $('#table-report-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-list_filter').find('input[type="search"]');
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
	 	window.location.href = '?page=outbound_report&finddate='+search_date;
	})
	$('#table-report-list_filter').append(label);
	$('#table-report-list_filter').append(button);
	
});

</script>

