<?php
$search = isset($_GET['search'])?$_GET['search']:'';
$finddate = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$user_id = $_SESSION['userID'];
?>
<div class="ibox-title">
	<table id="table-report-aging-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>หมวดสินค้า</th>
				<th>วันที่รับสินค้า</th>
				<th>จำนวนสินค้า</th>
				<th>หน่วยสินค้า</th>
				<th>รหัสผู้จำหน่าย</th>
				<th>ชื่อผู้จำหน่าย</th>
				<th>เวลาที่อยู่ในคลัง<br>(วัน)</th>
			</tr>
		</thead>
		<tbody>
<?php 
$db->select_sum('loc.qty', 'sum_product_qty');
$db->select_sum('loc.qty_remain', 'sum_qty_remain');
$db->select("po.cat, sup.name AS sup_name");
$db->select("po.po_id, po.po_supplier, po.product_no, po.product_name, po.product_unit, po.product_date_in, po.product_qty")->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");

$db->where("po.product_qty > 0");
$db->where("DATE(po.product_date_in) != '0000-00-00'");
// $db->where("po.cat != ''");
if(!empty($search)) {
	$db->where("(po.po_id LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%' OR po.product_date_in LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%' OR po.po_supplier LIKE '%".$search."%')", NULL, FALSE);
	$db->having("sum_qty_remain > 0");
}

$db->group_by("po.product_no");
$db->order_by("po.product_date_in DESC");
// $db->having('sum_product_qty > 0');
$sql = $db->get();
$results = $sql->result_array();
// _print($results);exit;
$row = 1;
foreach($sql->result_array() as $result) {

	if( empty($result['product_date_in']) || $result['product_date_in'] == "0000-00-00 00:00:00" ) {
		$product_date_in = date("Y-m-d");
	} else {
		$product_date_in = $result['product_date_in'];
	}
	 
	$current_date = date('Y-m-d H:i:s');
	$currentDate = date("Y-m-d", strtotime($current_date));
	$productinDate = date("Y-m-d", strtotime($product_date_in));
	 
	$now = explode("-", $currentDate);
	$productin = explode("-", $productinDate);
	 
	$date1 = mktime(0,0,0,$now[1],$now[2],$now[0]); //15 กันยายน 2540
	$date2 = mktime(0,0,0,$productin[1],$productin[2],$productin[0]); //1 พฤศจิกายน 2550
	//หาผลต่าง
	$diff = $date1-$date2;
	//ทำการแปลงจากผลต่างเป็นวินาทีเป็นระยะเวลา
	$Days = floor($diff / 86400);
?>		
			<tr>
				<td><?php echo $result['product_no'];?></td>
				<td><?php echo $result['product_name'];?></td>
				<td><?php echo $result['cat'];?></td>
				<td><?php echo $result['product_date_in'];?></td>
				<td><?php echo $result['product_qty'];?></td>
				<td><?php echo $result['product_unit'];?></td>
				<td><?php echo $result['po_supplier'];?></td>
				<td><?php echo $result['sup_name'];?></td>
				<!-- <td <?php if($count_date > 100) { ?> style="color:red" <?php } ?>><?php echo number_format($count_date);?></td> -->
				<td <?php if($Days > 90) { ?> style="color:red" <?php } ?>><?php echo number_format($Days);?></td>
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
	$('#report_aging').addClass('active');
	
	var oTable = $('#table-report-aging-list').dataTable({
		"pageLength": 100,
		"order": [[ 8, "desc" ]]
	});

	var search = $('#table-report-aging-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
		 	var search_key = $('#search_key').val();
		 	window.location.href = '?page=report_aging&search='+search_key;
		}
	});

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=report_aging&finddate='+search_date;
	})
// 	$('#table-report-aging-list_filter').append(button);
	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_key = $('#table-report-aging-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_aging.php?search='+search_key;
		});
	$('#table-report-aging-list_filter').append(btnXLS);
});
</script>