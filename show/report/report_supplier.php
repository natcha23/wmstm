<?php
$search = isset($_GET['search'])?$_GET['search']:'';
$finddate = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$user_id = $_SESSION['userID'];
// $finddate = '2015-07-08';
?>
<div class="ibox-title">
	<table id="table-report-supplier-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>รหัสผู้จำหน่าย</th>
				<th>ชื่อผู้จำหน่าย</th>
				<th>หมวดสินค้า</th>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>จำนวนสินค้า(คงเหลือ)</th>
				<th>หน่วยสินค้า</th>
			</tr>
		</thead>
		<tbody>
<?php 
$db->select_sum('loc.qty', 'sum_product_qty');
$db->select_sum('loc.qty_remain', 'sum_qty_remain');
$db->select("po.po_id, po.po_supplier, po.product_no, po.product_name, po.product_unit, po.cat, sup.name AS sup_name");

$db->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");

$db->where("po.cat != ''");

if (!empty($search) ) {
	$db->where("(po.po_supplier LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR po.product_no LIKE '%".$search."%' OR po.po_supplier LIKE '%".$search."%'".
			" OR sup.name LIKE '%".$search."%')", NULL, FALSE);
}

$db->group_by("po.product_no");
$db->having("sum_qty_remain > 0");
$sql = $db->get();
// _print($db->last_query());
$results = array();
$results = $sql->result_array();
// _print($results);exit;
$row = 1;
foreach($results as $result) {
?>		
			<tr>
				<td><?php echo $result['po_supplier'];?></td>
				<td><?php echo $result['sup_name'];?></td>
				<td><?php echo $result['cat'];?></td>
				<td><?php echo $result['product_no'];?></td>
				<td><?php echo $result['product_name'];?></td>
				<td><?php echo number_format($result['sum_qty_remain']);?></td>
				<td><?php echo $result['product_unit'];?></td>
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
	$('#report_supplier').addClass('active');

	var oTable = $('#table-report-supplier-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-supplier-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
		 	var search_key = $('#search_key').val();
		 	window.location.href = '?page=report_supplier&search='+search_key;
		}
	});

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=report_supplier&finddate='+search_date;
	})
// 	$('#table-report-supplier-list_filter').append(label);
// 	$('#table-report-supplier-list_filter').append(button);

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_key = $('#table-report-supplier-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_supplier.php?search='+search_key;
		});
	$('#table-report-supplier-list_filter').append(btnXLS);
});
</script>