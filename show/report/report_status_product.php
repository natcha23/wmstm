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
	<table id="table-report-status-product-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>หมายเลขเอกสาร</th>
				<th>วันที่ออกเอกสาร</th>
				<th>Checking in</th>
				<th>In Store</th>
				<th>Launch</th>
				<th>Pick up</th>
				<th>Checking out</th>
				<th>Choose car</th>
				<th>Transport</th>
				<th style="display:none">To branch</th>
			</tr>
		</thead>
		<tbody>
<?php 

$db->select("po.po_id, po.po_create, po.po_id AS doc_id, po.po_create AS doc_create");
$db->select("SUM(CASE WHEN status.status = '1' THEN loc.qty_remain ELSE 0 END) inbound_products, ".
		"SUM(CASE WHEN status.status = '2' THEN loc.qty_remain ELSE 0 END) store_products " );
$db->from("inbound_status AS status");
$db->join("inbound_po AS po", "status.inbound_id = po.po_id", "INNER");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id", "LEFT");

if ( !empty($finddate) ) {
	$db->where("DATE(status.start_date) = '" . $finddate . "'");
}

if ( !empty($search) ) {
	$db->where("(po_id LIKE '%".$search."%')", NULL, FALSE);
}
$db->group_by("po.po_id");
$sql = $db->get();
// _print($db->last_query());
$poresult = $sql->result_array();

// $db->select("user.user_fname, user.user_lname");
// $db->select("*");
$db->select("rt_id, rt_date, rt_id AS doc_id, rt_date AS doc_create");
$db->select("SUM(CASE WHEN status.status = '1' THEN status.sum_product ELSE 0 END) launch_products, ".
		"SUM(CASE WHEN status.status = '2' THEN status.sum_product ELSE 0 END) pickup_products, ".
		"SUM(CASE WHEN status.status = '3' THEN status.sum_product ELSE 0 END) checkingout_products, ".
		"SUM(CASE WHEN status.status = '4' THEN status.sum_product ELSE 0 END) choosecar_products, ".
		"SUM(CASE WHEN status.status = '5' THEN status.sum_product ELSE 0 END) transport_products, ".
		"SUM(CASE WHEN status.status = '7' THEN status.sum_product ELSE 0 END) tobranch_products");

$db->select("SUM(CASE WHEN status.status = '1' THEN status.sum_product ELSE 0 END) launch_items, ".
		"SUM(CASE WHEN status.status = '2' THEN status.rt_product_amount ELSE 0 END) pickup_items, ".
		"SUM(CASE WHEN status.status = '3' THEN status.rt_product_amount ELSE 0 END) checkingout_items, ".
		"SUM(CASE WHEN status.status = '4' THEN status.rt_product_amount ELSE 0 END) choosecar_items, ".
		"SUM(CASE WHEN status.status = '5' THEN status.rt_product_amount ELSE 0 END) transport_items, ".
		"SUM(CASE WHEN status.status = '7' THEN status.rt_product_amount ELSE 0 END) tobranch_items");
$db->from("outbound_rt_status AS status");

if ( !empty($finddate) ) {
	$db->where("DATE(status.update_time) = '" . $finddate . "'");	
}

if ( !empty($search) ) {
	$db->where("(rt_id LIKE '%".$search."%')", NULL, FALSE);
}
$db->group_by("status.rt_id");
$sql = $db->get();
// _print($db->last_query());
$results = array_merge($poresult, $sql->result_array());
$row = 1;
$statusArr = array(
		'inbound',
		'inpallet',
		'store',
		'launch',
		'pickup',
		'choosecar',
		'transport',
		'tobranch'
);
$totalArr = array();
$total_launch = $total_pickup = 0;
$total_inbound = $total_store = 0;
$total_checkingout = $total_choosecar = 0;
$total_transport = $total_tobranch = 0;
foreach($results as $result) {
	$total_inbound += (!empty($result['inbound_products']))?$result['inbound_products']:0;
	$total_store += (!empty($result['store_products']))?$result['store_products']:0;
	$total_launch += $result['launch_products'];
	$total_pickup += $result['pickup_products'];
	$total_checkingout += $result['checkingout_products'];
	$total_choosecar += $result['choosecar_products'];
	$total_transport += $result['transport_products'];
	$total_tobranch += $result['tobranch_products'];
?>		
			<tr>
				<td><?php echo $result['doc_id'];?></td>
				<td><?php echo $result['doc_create'];?></td>
				<td align="right"><?php echo ($result['inbound_products'])?number_format($result['inbound_products']):0;?></td>
				<td align="right"><?php echo ($result['store_products'])?number_format($result['store_products']):0;?></td>
				<td align="right"><?php echo ($result['launch_products'])?number_format($result['launch_products']):0;?></td>
				<td align="right"><?php echo ($result['pickup_products'])?number_format($result['pickup_products']):0;?></td>
				<td align="right"><?php echo ($result['checkingout_products'])?number_format($result['checkingout_products']):0;?></td>
				<td align="right"><?php echo ($result['choosecar_products'])?number_format($result['choosecar_products']):0;?></td>
				<td align="right"><?php echo ($result['transport_products'])?number_format($result['transport_products']):0;?></td>
				<td align="right" style="display:none"><?php echo ($result['tobranch_products'])?number_format($result['tobranch_products']):0;?></td>
			</tr>
<?php 
	$row++;
}
?>
		</tbody>
		<tfoot>
		<?php if($row > 1) { ?>
			<tr class="total-row">
				<td colspan="2">Total</td>
				<td align="right"><?php echo number_format($total_inbound); ?></td>
				<td align="right"><?php echo number_format($total_store); ?></td>
				<td align="right"><?php echo number_format($total_launch); ?></td>
				<td align="right"><?php echo number_format($total_pickup); ?></td>
				<td align="right"><?php echo number_format($total_checkingout); ?></td>
				<td align="right"><?php echo number_format($total_choosecar); ?></td>
				<td align="right"><?php echo number_format($total_transport); ?></td>
				<td align="right" style="display:none"><?php echo number_format($total_tobranch); ?></td>
			</tr>
		<?php } ?>
		</tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_status_product').addClass('active');

	var oTable = $('#table-report-status-product-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-status-product-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
		 	var search_key = $('#search_key').val();
		 	var search_date = $('#search_date').val();
		 	window.location.href = '?page=report_status_product&finddate='+search_date+'&search='+search_key;
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
	 	window.location.href = '?page=report_status_product&finddate='+search_date+'&search='+search_key;
	})
	$('#table-report-status-product-list_filter').append(label);
	$('#table-report-status-product-list_filter').append(button);

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_date = $('#search_date').val();
		 	var search_key = $('#table-report-status-product-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_status_product.php?finddate='+search_date+'&search='+search_key;
		});
	$('#table-report-status-product-list_filter').append(btnXLS);
});
</script>