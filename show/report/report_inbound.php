<?php
$search = isset($_GET['search'])?$_GET['search']:'';
if(empty($search) && empty($_GET['finddate'])) {
	$_GET['finddate'] = date('Y-m-d');
}
$finddate = isset($_GET['finddate'])?$_GET['finddate']:'';
$start = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 12:00:00';
$stop = $finddate.' 11:59:59';
$user_id = $_SESSION['userID'];
// $finddate = '2015-07-08';
?>

<div class="ibox-title">
<!-- 	<div class="row wrapper border-bottom white-bg page-heading"> -->
<!-- 		<div class="col-lg-12"> -->
<!-- 			<h2>รายงานสินค้าขาเข้า</h2> -->
<!-- 		</div> -->
<!-- 	</div> -->
	<table id="table-report-inbound-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>วันที่นำสินค้าเก็บ</th>
				<th>PO No.</th>
				<th>รหัสผู้จำหน่าย</th>
				<th>ชื่อผู้จำหน่าย</th>
				<th>หมวดสินค้า</th>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>สถานที่เก็บสินค้า</th>
				<th>หน่วยสินค้า</th>
				<th>จำนวนสินค้า</th>
				<th>จำนวนคงเหลือ</th>
			</tr>
		</thead>
		<tbody>
<?php 
$db->select("product_no, po_id, add_name, po_create, po_delivery_date, po_supplier, product_name, product_unit, time");
$db->select("loc.qty, loc.qty_remain, po.cat, sup.name AS sup_name");

$db->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id", "LEFT");
$db->join("tb_address AS ad", "loc.location_id = ad.add_id", "INNER");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");
// $db->where("po.product_date_in BETWEEN '". $start ."' AND '". $stop ."'");
// $db->where("loc.time BETWEEN '". $start ."' AND '". $stop ."'");

if ( !empty($finddate) ) {
	$db->where("loc.time = '$finddate'");	
}

if ( !empty($search) ) {
	$db->where("(po_id LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR add_name LIKE '%".$search."%' OR product_no LIKE '%".$search."%' OR sup.name LIKE '%".$search."%')", NULL, FALSE);
}

$sql = $db->get();
// _print($db->last_query());
// $results = $sql->result_array();
$row = 1;
$total_order_qty = $total_product_qty = 0;
foreach($sql->result_array() as $result) {
// 	$diff = $result['sum_product_qty'] - $result['sum_qty_remain'];
	$total_remain_qty += $result['sum_qty_remain'];
	$total_product_qty += $result['sum_product_qty'];
?>		
			<tr>
				<td><?php echo $result['time'];?></td>
				<td><?php echo $result['po_id'];?></td>
				<td><?php echo $result['po_supplier'];?></td>
				<td><?php echo $result['sup_name'];?></td>
				<td><?php echo $result['cat'];?></td>
				<td><?php echo $result['product_no'];?></td>
				<td><?php echo $result['product_name'];?></td>
				<td><?php echo $result['add_name'];?></td>
				<td><?php echo $result['product_unit'];?></td>
				<td align="right"><?php echo number_format($result['qty'], 0, '', ',');?></td>
				<td align="right" class="<?php if($result['qty_remain'] < 0){ echo 'red-font'; }?>"><?php echo number_format($result['qty_remain'], 0, '', ',');?></td>
			</tr>
<?php 
	$row++;
}
?>
		</tbody>
		<tfoot>
		<?php if($row > 1) { ?>
			<!-- <tr class="total-row">
				<td colspan="6">Total</td>
				<td align="right"><?php echo number_format($total_product_qty); ?></td>
				<td align="right"><?php echo number_format($total_remain_qty); ?></td>
			</tr> -->
		<?php } ?>
		</tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_inbound').addClass('active');
	
	var oTable = $('#table-report-inbound-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-inbound-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
			var search_date = $('#search_date').val();
		 	var search_key = $('#search_key').val();
		 	window.location.href = '?page=report_inbound&finddate='+search_date+'&search='+search_key;
		}
	});

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
	 	var search_key = $('#search_key').val();
	 	window.location.href = '?page=report_inbound&finddate='+search_date+'&search='+search_key;
	})
	$('#table-report-inbound-list_filter').append(label);
	$('#table-report-inbound-list_filter').append(button);

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_date = $('#search_date').val();
		 	var search_key = $('#table-report-inbound-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_inbound.php?finddate='+search_date+'&search='+search_key;
		});
	$('#table-report-inbound-list_filter').append(btnXLS);
});
</script>