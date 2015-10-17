<?php
$finddate 	= isset($_GET['finddate'])?$_GET['finddate']:'';
$zone 		= isset($_GET['zone'])?$_GET['zone']:'0';
$user_id	= $_SESSION['userID'];
$search 	= isset($_GET['search'])?$_GET['search']:'';
if(empty($search) && empty($_GET['finddate']) && empty($zone) ) {
	$_GET['finddate'] = date('Y-m-d');
}


$db->select('zone_id, zone_name')->from('tb_zone');
$db->where(array('status' => 0));
$zoneSQL = $db->get();
$zoneArr = $zoneSQL->result_array();

?>
<div class="ibox-title">
	<table id="table-report-product-transfer-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>รหัสสินค้า</th>
				<th style="display:none">location</th>
				<th>ชื่อสินค้า</th>
				<th>Checking in</th>
				<th>In Store</th>
				<th>Launch</th>
				<th>Pick up</th>
				<th>Checking out</th>
				<th>Choose car</th>
				<th>Transport</th>
				<th style="display:none">To branch</th>
				<th style="display:none">Total</th>
			</tr>
		</thead>
		<tbody>
<?php 

	$limit = 100;
	$offset = 0;
	/* PO step */
	$sqlwhere = '';
	$sqlmsg = "SELECT stk.product_id, stk.product_name, stk.product_qty, rtloc.location_id, ".
			
				" SUM(CASE WHEN posts.status = '1' THEN poloc.qty_remain ELSE 0 END) inbound_products, ".
				" SUM(CASE WHEN posts.status = '2' THEN poloc.qty_remain ELSE 0 END) store_products, ".
				
				" SUM(CASE WHEN rtsts.status = '1' THEN rtloc.qty ELSE 0 END) launch_products, ".
				" SUM(CASE WHEN rtsts.status = '2' THEN rtloc.qty ELSE 0 END) pickup_products, ".
				" SUM(CASE WHEN rtsts.status = '3' THEN rtloc.qty ELSE 0 END) checkingout_products, ".
				" SUM(CASE WHEN rtsts.status = '4' THEN rtloc.qty ELSE 0 END) choosecar_products, ".
				" SUM(CASE WHEN rtsts.status = '5' THEN rtloc.qty ELSE 0 END) transport_products, ".
				" SUM(CASE WHEN rtsts.status = '7' THEN rtloc.qty ELSE 0 END) tobranch_products" .
				
				" FROM stock_product AS stk".
				" LEFT JOIN inbound_po AS po ON stk.product_id = po.product_no".
				" LEFT JOIN inbound_status AS posts ON po.po_id = posts.inbound_id".
				" LEFT JOIN inbound_location AS poloc ON po.inbound_id = poloc.inbound_id".
	
				" LEFT JOIN outbound_rt AS rt ON stk.product_id = rt.barcode".
				" LEFT JOIN outbound_rt_status AS rtsts ON rt.rt_refid = rtsts.rt_id".
				" LEFT JOIN outbound_items_location AS rtloc ON rt.id = rtloc.outbound_id AND rtloc.status = 0";
					
	if ( !empty($finddate) ) {
		$sqlwhere .= (!empty($sqlwhere))? " AND ":" WHERE ";
		$sqlwhere .= " DATE(stk.product_update) = '" .$finddate. "'";
	}
	
	if ( !empty($search) ) {
		$sqlwhere .= (!empty($sqlwhere))? " AND ":" WHERE ";
		$sqlwhere .= " (stk.product_id LIKE '%" .$search. "%' OR stk.product_name LIKE '%" .$search. "%')";
	}
	if ( $zone > 0 ) {
		$sqlmsg .= " LEFT JOIN tb_address AS poaddr ON (poloc.location_id = poaddr.add_id)";
		$sqlmsg .= " LEFT JOIN tb_address AS rtaddr ON (rtloc.location_id = rtaddr.add_id)";
		$sqlwhere .= (!empty($sqlwhere))? " AND ":" WHERE "; 
		$sqlwhere .= "(poaddr.zone_id = '" .$zone. "' OR rtaddr.zone_id = '" .$zone. "')";	
	}					
	if(!empty($sqlwhere)) {
		$sqlmsg .= $sqlwhere;
	} 					
	$sqlmsg .=	" GROUP BY stk.product_id";
// 	_print($sqlmsg);
	$query = $db->query($sqlmsg);
	
	$num_rows = $query->num_rows();
// 	_print($num_rows);
// 	$sqlmsg .= " LIMIT " .$offset. ", " .$limit;
	$queryresult = $db->query($sqlmsg);
	
	$results = $queryresult->result_array();

// 	_print($results);
	$row = 1;
	foreach($results as $result) {
?>		
		<tr>
			<td><?php echo $result['product_id'];?></td>
			<td style="display:none"><?php echo $result['location_id']?></td>
			<td><?php echo $result['product_name']?></td>
			<td align="right"><?php echo ($result['inbound_products'])?number_format($result['inbound_products']):0;?></td>
			<td align="right"><?php echo ($result['store_products'])?number_format($result['store_products']):0;?></td>
			<td align="right"><?php echo ($result['launch_products'])?number_format($result['launch_products']):0;?></td>
			<td align="right"><?php echo ($result['pickup_products'])?number_format($result['pickup_products']):0;?></td>
			<td align="right"><?php echo ($result['checkingout_products'])?number_format($result['checkingout_products']):0;?></td>
			<td align="right"><?php echo ($result['choosecar_products'])?number_format($result['choosecar_products']):0;?></td>
			<td align="right"><?php echo ($result['transport_products'])?number_format($result['transport_products']):0;?></td>
			<td align="right" style="display:none"><?php echo ($result['tobranch_products'])?number_format($result['tobranch_products']):0;?></td>
			<td align="right" style="display:none"><?php echo ($result['product_qty'])?number_format($result['product_qty']):0;?></td>
		</tr>
<?php
		$row++; 
	} ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_product_trans').addClass('active');

	var oTable = $('#table-report-product-transfer-list').dataTable({
		"pageLength": 100,
		
	});

	var search = $('#table-report-product-transfer-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13 ) {
		 	var search_key = $('#search_key').val();
		 	var search_date = $('#search_date').val();
		 	var search_zone = $('#search_zone').val();
		 	window.location.href = '?page=report_product_trans&finddate='+search_date+'&search='+search_key+'&zone='+search_zone;
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
	 	var search_zone = $('#search_zone').val();
	 	window.location.href = '?page=report_product_trans&finddate='+search_date+'&search='+search_key+'&zone='+search_zone;
	})

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_date = $('#search_date').val();
		 	var search_key = $('#table-report-product-transfer-list_filter').find('input[type=search]').val();
		 	var search_zone = $('#search_zone').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_product_trans.php?finddate='+search_date+'&search='+search_key;
		});
	
	var zonelist = $('<div />', { class: 'form-group pull-left' });
	var zoneLbl = $('<label />', { class: 'control-label'}).html('Location Zone:');
	var selectOpts = $('<select />', { class:'form-control' }).attr({'id':'search_zone', 'name':'zone_id'});
	var zoneOpts = '<option value="0">All</option>';
	<?php if(!empty($zoneArr)) { 
		foreach($zoneArr as $row) {?>
			zoneOpts += '<option value="<?php echo $row['zone_id']; ?>"><?php echo $row['zone_name']; ?></option>';
	<?php } }?>
	
	$(selectOpts).append(zoneOpts);
	$(selectOpts).val('<?php echo $zone; ?>');
	$(selectOpts).change( function (e) {
// 		alert($(this).val());
		var search_zone = $(this).val();
		var search_date = $('#search_date').val();
	 	var search_key = $('#table-report-product-transfer-list_filter').find('input[type=search]').val();
	 	window.location.href = '?page=report_product_trans&finddate='+search_date+'&search='+search_key+'&zone='+search_zone;
	});
	
	$(zonelist).append(zoneLbl);
	$(zonelist).append(selectOpts);
	
	$('#table-report-product-transfer-list_filter').append(zonelist);
	
	$('#table-report-product-transfer-list_filter').append(label);
	$('#table-report-product-transfer-list_filter').append(button);
	$('#table-report-product-transfer-list_filter').append(btnXLS);
	
// 	var pagelength = $("select[name='table-report-product-transfer-list_length']");
// 	$(pagelength).change( function (e) {
// 		console.log($(this).val());
// 	});
	
});
</script>