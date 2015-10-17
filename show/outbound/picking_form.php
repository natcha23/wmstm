<?php

require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');
$db = DB();

$rt_id = $_GET['rtID'];

$db->select("*")->from("outbound_rt AS rt");
$db->join("outbound_items_location AS loca", "rt.id = loca.outbound_id", "LEFT");
$db->join("tb_address AS addr", "location_id = add_id", "LEFT");

$db->where(array("rt_refid" => $rt_id));
$db->where("rt.qty_amount > 0");
$sql = $db->get();
// _print($db->last_query());exit;
$results = $sql->result_array();
// _print($results[0]['rt_date']);exit;

?>

<style></style>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">
		<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
	</button>
	<label class="modal-title">Picking List</label>
</div>
<div class="modal-body">
<div><button type="button" class="btn btn-success pull-right" onclick="$.printDiv('printableDiv');">พิมพ์</button></div>
<div id="printableDiv">
<style>

.block-left {
	width: 200px;
	margin: 20px;
	float: left;
}
.block-right {
	width: 150px;
	margin: 20px;
	float: right;
}

</style>
<form method="post" enctype="multipart/form-data">
<span class="block-left"><label>รายละเอียด :&nbsp;</label><?php echo $rt_id?></span>

<span class="block-right"><label>วันที่ออก RT :&nbsp;</label><?php echo date('Y-m-d', strtotime($results[0]['rt_date']));?></span>

	<table id="table-report-aging-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>ลำดับ</th>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>หน่วยสินค้า</th>
				<th>จำนวนสินค้า</th>
				<th>ที่เก็บ</th>
			</tr>
		</thead>
		<tbody>		
		
	<?php 
		$row=1;
		foreach($results as $item) {
	?>
			<tr>
				<td><?php echo $row; ?></td>
				<td><?php echo $item['barcode'];?></td>
				<td><?php echo $item['goods_name'];?></td>
				<td><?php echo $item['unit'];?></td>
				<td><?php echo $item['qty'];?></td>
				<td><?php echo $item['add_name'];?></td>
			</tr>
	<?php 
			$row++;		
			}
	?>
		</tbody>
	</table>
	
</form>
</div>
<div class="modal-footer" style="border:none"></div>
<div class="modal-footer">
	<button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
	<button type="button" class="btn btn-success" onclick="$.printDiv('printableDiv');">พิมพ์</button>
</div>

<script>
</script>