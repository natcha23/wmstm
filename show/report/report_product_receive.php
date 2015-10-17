<?php
$search = isset($_GET['search'])?$_GET['search']:'';
if(empty($search) && empty($_GET['finddate'])) {
	$_GET['finddate'] = date('Y-m-d');
}
$finddate = isset($_GET['finddate'])?$_GET['finddate']:'';
$user_id = $_SESSION['userID'];
// $finddate = '2015-07-08';
?>
<div class="ibox-title">
	<table id="table-report-receive-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>PO No.</th>
				<th>รหัสผู้จำหน่าย</th>
				<th>ชื่อผู้จำหน่าย</th>
				<th>หมวดสินค้า</th>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>วันที่รับสินค้า</th>
				<th>หน่วยสินค้า</th>
				<th>จำนวนสินค้า</th>
				<th>จำนวนที่รับจริง</th>
				<th style="display:none">ผู้รับสินค้า</th>
			</tr>
		</thead>
		<tbody>
<?php 
// $db->select("user.user_fname, user.user_lname");
$db->select("sup.name AS sup_name");
$db->select("po.*, status.*")->from("inbound_status AS status");
$db->join("inbound_po AS po", "po.po_id = status.inbound_id", "LEFT");
// $db->join("user AS user", "po.user_update = user.user_id", "LEFT");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");
// $db->group_by("rt.barcode");
// $db->where("loc.qty > 0");
$db->where("po.product_date_in NOT LIKE '0000-%'");

if ( !empty($finddate) ) {
	$db->where("DATE(po.po_create) = '" . $finddate . "'");
}

if ( !empty($search) ) {
	$db->where("(po.po_id LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR status.time_get_product LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%'".
			" OR po.po_supplier LIKE '%".$search."%' OR sup.name LIKE '%".$search."%' OR po.product_unit LIKE '%".$search."%')", NULL, FALSE);
}

$sql = $db->get();
$results = $sql->result_array();
// _print($db->last_query());
$row = 1;
$total_order_qty = $total_product_qty = 0;
foreach($results as $result) {
	$total_order_qty += $result['order_qty'];
	$total_product_qty += $result['product_qty'];
// 	if(empty($result['user_update'])) {
// 		$result['user_fname'] = $_SESSION['fname'];
// 		$result['user_lname'] = $_SESSION['lname'];
// 	}
?>		
			<tr>
				<td><?php echo $result['inbound_id'];?></td>
				<td><?php echo $result['po_supplier'];?></td>
				<td><?php echo $result['sup_name'];?></td>
				<td><?php echo $result['cat'];?></td>
				<td><?php echo $result['product_no'];?></td>
				<td><?php echo $result['product_name'];?></td>
				<td><?php echo $result['time_get_product'];?></td>
				<td><?php echo $result['product_unit']?></td>
				<td align="right"><?php echo number_format($result['order_qty']);?></td>
				<td align="right"><b><?php echo number_format($result['product_qty']);?></b></td>
				<td style="display:none"><?php echo $result['user_fname'] . " " . $result['user_lname'];?></td>
			</tr>
<?php 
	$row++;
}
?>
		</tbody>
		<tfoot>
		<?php if($row > 1) { ?>
			<tr class="total-row" style="display: none">
				<td colspan="8">Total</td>
				<td align="right"><?php echo number_format($total_order_qty); ?></td>
				<td align="right"><?php echo number_format($total_product_qty); ?></td>
			</tr>
		<?php } ?>
		</tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_productreceive').addClass('active');

	var oTable = $('#table-report-receive-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-receive-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
		 	var search_key = $('#search_key').val();
		 	var search_date = $('#search_date').val();
		 	window.location.href = '?page=report_product_receive&finddate='+search_date+'&search='+search_key;
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
		var search_key = $('#search_key').val();
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=report_product_receive&finddate='+search_date+'&search='+search_key;
	})
	$('#table-report-receive-list_filter').append(label);
	$('#table-report-receive-list_filter').append(button);

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_date = $('#search_date').val();
		 	var search_key = $('#table-report-receive-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_product_receive.php?finddate='+search_date+'&search='+search_key;
		});
	$('#table-report-receive-list_filter').append(btnXLS);
});
</script>