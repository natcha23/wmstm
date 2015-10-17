<?php
$search = isset($_GET['search'])?$_GET['search']:'';
$finddate = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$find_status = isset($_GET['findstatus'])?$_GET['findstatus']:'-1';
$user_id = $_SESSION['userID'];

?>
<div class="ibox-title">
	<table id="table-report-location-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>หมวดสินค้า</th>
				<th>ขื่อที่เก็บสินค้า</th>
				<th>หน่วยสินค้า</th>
				<th>จำนวนสินค้า ณ ปัจจุบัน</th>
			</tr>
		</thead>
		<tbody>
<?php 
$db->select_sum("loc.qty_remain", "sum_qty_remain");
$db->select("loc.location_id, tbadd.add_name, tbadd.blank_status, loc.qty_remain");
$db->select("po.po_id, po.po_supplier, po.product_no, po.product_name, po.product_unit, po.product_date_in, po.product_qty, po.cat");
// $db->from("inbound_location AS loc");
// $db->join("inbound_po AS po", "loc.inbound_id = po.inbound_id", "LEFT");
$db->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
$db->join("tb_address AS tbadd", "loc.location_id = tbadd.add_id", "LEFT");

if( $find_status != -1 ){
	$db->where("tbadd.blank_status = '" .$find_status. "'");
}

if (!empty($search) ) {
	$db->where("(po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR tbadd.add_name LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%' OR po.product_unit LIKE '%".$search."%')", NULL, FALSE);
}

$db->where("loc.qty_remain > 0");
$db->group_by("loc.location_id");
$db->group_by("po.product_no");


$sql = $db->get();
// _print($db->last_query());exit;
// $result = $sql->result_array();
// _print($result);

$row = 1;
foreach($sql->result_array() as $result) {
/* 	$blank = $result['blank_status'];
	if($blank == 1) {
		$statusmsg = "ไม่ว่าง";
	} else {
		$statusmsg = "ว่าง";
	} */
?>		
			<tr>
				<td><?php echo $result['product_no'];?></td>
				<td><?php echo $result['product_name'];?></td>
				<td><?php echo $result['cat'];?></td>
				<td><?php echo $result['add_name'];?></td>
				<td><?php echo $result['product_unit'];?></td>
				<td style="font-weight: bold;"><?php echo number_format($result['qty_remain']);?></td>
			</tr>
<?php 
	$row++;
}
?>
		</tbody>
		<tfoot></tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_location').addClass('active');
	
	var oTable = $('#table-report-location-list').dataTable({
		"pageLength": 100
	});

	var search = $('#table-report-location-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
		 	var search_key = $('#search_key').val();
		 	window.location.href = '?page=report_location&search='+search_key;
		}
	});

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_key = $('#table-report-location-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_location.php?search='+search_key;
		});
	$('#table-report-location-list_filter').append(btnXLS);

});
</script>