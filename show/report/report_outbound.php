<?php
$search = isset($_GET['search'])?$_GET['search']:'';
if(empty($search) && empty($_GET['finddate'])) {
	$_GET['finddate'] = date('Y-m-d');
}
$finddate = isset($_GET['finddate'])?$_GET['finddate']:'';
$user_id = $_SESSION['userID'];
?>
<div class="ibox-title">
	<table id="table-report-outbound-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>วันที่นำสินค้าออก</th>
				<th>RT No.</th>
				<th>หมวดสินค้า</th>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>หน่วยสินค้า</th>
				<th>สถานที่เก็บสินค้า</th>
				<th>จำนวนสินค้าที่มี</th>
				<th>จำนวนที่นำออก</th>
				<th>จำนวนคงเหลือ</th>
			</tr>
		</thead>
		<tbody>
<?php 
// $db->select_sum('loc.qty', 'sum_qty_out');
// $db->select("loc.date_out, loc.location_id");
$db->select("loc.qty as sum_qty_out, loc.date_out, loc.location_id, ad.add_id, ad.add_name");
$db->select("loc.before_out_qty, loc.after_out_qty");
$db->select("rt.rt_refid, rt.rt_date, rt.barcode, rt.goods_name, rt.unit, rt.category");

$db->from("outbound_rt AS rt");
$db->join("outbound_items_location AS loc", "rt.id = loc.outbound_id", "LEFT");
$db->join("outbound_rt_status as sts", "rt.rt_refid = sts.rt_id", "LEFT");
$db->join("tb_address AS ad", "loc.location_id = ad.add_id");

$db->where("rt.status = 0");
$db->where("sts.status >= '2'");


if ( !empty($finddate) ) {
	$db->where("DATE(loc.date_out) = '" . $finddate . "'");
}
if ( !empty($search) ) {
	$db->where("(rt.unit LIKE '%".$search."%' OR rt.category LIKE '%".$search."%' OR rt.rt_refid LIKE '%".$search."%'".
			" OR ad.add_name LIKE '%".$search."%' OR rt.barcode LIKE '%".$search."%' OR rt.goods_name LIKE '%".$search."%')", NULL, FALSE);
}

$db->order_by("rt.rt_refid", "ASC");
$db->order_by("loc.date_out", "ASC");

$sql_out = $db->get();
// _print($db->last_query());
$out_arr = $sql_out->result_array();

$results = array();
foreach($out_arr as $val) {
	$db->select_sum('loc.qty', 'sum_product_qty');
	$db->select_sum('loc.qty_remain', 'sum_qty_remain');
	$db->select("po.po_id, po.po_supplier, po.cat")->from("inbound_po AS po");
	$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
	$db->where("po.product_no = '" . $val['barcode'] . "'");
	$db->where("loc.location_id = '". $val['add_id'] ."'");
// 	$db->group_by("po.product_no");
// 	$db->group_by("loc.location_id");
// 	$db->having("sum_qty_remain > 0");
// 	$db->order_by("po.rt_refid");
	
	$sql_in = $db->get();
	$rs = $sql_in->row_array();
	$results[] = array_merge($val, $sql_in->row_array());
}
$row = 1;
$total_order_qty = $total_product_qty = $total_qty = 0;

foreach($results as $result) {
// 	if($result['sum_qty_remain'] > 0) {
	//$diff = $result['sum_qty_remain'] - $result['sum_qty_out'];
	$diff = $result['before_out_qty'] - $result['sum_qty_out'];
	$total_remain_qty += $diff;
	$total_product_qty += $result['sum_qty_out'];
	$total_qty += $result['sum_qty_remain'];
?>		
			<tr>
				<td><?php echo $result['date_out'];?></td>
				<td><?php echo $result['rt_refid'];?></td>
				<td><?php echo $result['category'];?></td>
				<td><?php echo $result['barcode'];?></td>
				<td><?php echo $result['goods_name'];?></td>
				<td><?php echo $result['unit'];?></td>
				<td><?php echo $result['add_name'];?></td>
				<td align="right"><?php echo number_format($result['before_out_qty'], 0, '', ',');?></td>
				<td align="right"><?php echo number_format($result['sum_qty_out']);?></td>
				<td align="right" class="<?php if($diff < 0){ echo 'red-font'; }?>"><?php echo number_format($diff, 0, '', ',');?></td>
			</tr>
<?php 
	$row++;
// 	}
}
?>
		</tbody>
		<tfoot>
		<?php if($row > 1) { ?>
<!-- 			<tr class="total-row">
 				<td colspan="5">Total</td>
				<td align="right" class="<?php if($total_qty < 0){ echo 'red-font'; }?>"><?php echo number_format($total_qty); ?></td>
				<td align="right" class="<?php if($total_product_qty < 0){ echo 'red-font'; }?>"><?php echo number_format($total_product_qty); ?></td>
				<td align="right" class="<?php if($total_remain_qty < 0 ) { echo "red-font"; } ?>"><?php echo number_format($total_remain_qty); ?></td>
 			</tr> -->
		<?php } ?>
		</tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_outbound').addClass('active');
	
	var oTable = $('#table-report-outbound-list').dataTable({
		"pageLength": 100,
		"order": [[ 7, "desc" ]]
	});

	var search = $('#table-report-outbound-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
			var search_date = $('#search_date').val();
		 	var search_key = $('#search_key').val();
		 	window.location.href = '?page=report_outbound&finddate='+search_date+'&search='+search_key;
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
	 	window.location.href = '?page=report_outbound&finddate='+search_date+'&search='+search_key;
	})
	$('#table-report-outbound-list_filter').append(label);
	$('#table-report-outbound-list_filter').append(button);

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_date = $('#search_date').val();
		 	var search_key = $('#table-report-outbound-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_outbound.php?finddate='+search_date+'&search='+search_key;
		});
	$('#table-report-outbound-list_filter').append(btnXLS);
});
</script>