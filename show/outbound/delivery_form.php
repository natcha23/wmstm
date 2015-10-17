<?php

require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');
$db = DB();

$rt_id 			= $_REQUEST['rtID'];
$organize_id 	= $_REQUEST['organize_id'];
// _print($_REQUEST);exit;

/* Diplay details */
$db->select("*")->from("outbound_car AS ocr");
$db->join("car_list AS car", "ocr.car_id = car.car_id", "LEFT");
$db->join("user AS user", "ocr.driver_id = user.user_id", "LEFT");
$db->where(array("outbound_rt" => $_REQUEST['rtID']));

$sql = $db->get();
// _print($db->last_query());
$result = $sql->row_array();
// _print($result);

$results = $sql->result_array();
// _print($result);
?>
<style></style>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">
		<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
	</button>
	<!-- <label class="modal-title">RT No. :  <?php echo $_GET['rtID']; ?> <br> ทะเบียนรถ :  <span id="car_plate"><?php echo (!empty($result['car_code']))? $result['car_code']:$result['other_car_code']; ?></span></label> -->
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
	
		
<?php 

$db->select("outbound_car.*, rt_branch, delivery_order_id")->from("outbound_car");
$db->join("outbound_rt_status", "outbound_rt = rt_id", "INNER");
// $db->where("car_id", $_REQUEST['car_id']);
$db->where("organize_truck_id", $_REQUEST['organize_id']);
$db->where("status_process", 1);
// $db->where("date_time", $_REQUEST['car_id']);
$sql = $db->get();
// _print($db->last_query());exit;
$outbound_cars = $sql->result_array();
// _print($outbound_cars);exit;
foreach($outbound_cars AS $val) {
	$newArr[$val['rt_branch']][] = $val;
}
// _print($newArr);exit;
foreach($newArr as $key_branch => $val_branch) {
?>
	

	
	<?php 
	foreach($val_branch as $key_item => $val_item) {
		$db->select("*")->from("outbound_rt");
		$db->join("outbound_check", "outbound_rt.id = outbound_id", "LEFT");
		$db->where("rt_refid", $val_item['outbound_rt']);
		$db->where("check_qty > 0");
			
		$rtsql = $db->get();
		$items = $rtsql->result_array();
	?>
	<span class="block-left"><br>
		<label>วันที่:&nbsp; </label><?php echo date("Y-m-d");?><br>
		<label>ทะเบียนรถ:&nbsp; </label><?php echo ($result['car_code'])?$result['car_code']:$result['other_car_code'];?><br>
		<label>สาขา:&nbsp; </label><?php echo $items[0]['shipto_name']?><br>
	</span>
	<span class="block-right">
		<label>เอกสาร:&nbsp;<?php echo $val_item['delivery_order_id']; ?></label><br>
		<label>เวลา:&nbsp; </label><?php echo date("H:i");?>&nbsp;น.<br>
		<?php if(empty($result['user_fname']) || empty($result['user_lname'])) { $drivername = $result['driver_name']; } else { $drivername = $result['user_fname'] . " " . $result['user_lname']; }?>
		<label>ชื่อคนขับ:&nbsp; </label><?php echo $drivername;?>
	</span>
	<p></p>
	<!-- 
	<span><label>DO255809/00001</label></span><p></p>
	<span class="block-1"><label>ทะเบียนรถ: </label><?php echo $result['car_code'];?></span>
	<span class="block-2"><label>ชื่อคนขับ: </label><?php echo $result['user_fname'] . " " . $result['user_lname'];?></span><br>
	<span class="block-1"><label>สาขา: </label><?php echo $items[0]['shipto_name']?></span>
	<span><label>รายละเอียด RT: </label><?php echo $items[0]['rt_refid']?></span>
	-->
	<div style="clear:both"><label>รายละเอียด: </label><?php echo $items[0]['rt_refid']?></div>
	<table id="table-report-aging-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>ลำดับ</th>
				<th>รหัสสินค้า</th>
				<th>ชื่อสินค้า</th>
				<th>จำนวนสินค้า</th>
				<th>หน่วยสินค้า</th>
			</tr>
		</thead>
		<tbody>		
		
	<?php 
		$row=1;
		foreach($items as $item) {
		?>
				<tr>
					<td><?php echo $row; ?></td>
					<td><?php echo $item['barcode'];?></td>
					<td><?php echo $item['goods_name'];?></td>
					<td><?php echo $item['check_qty'];?></td>
					<td><?php echo $item['unit'];?></td>
				</tr>
		<?php 
			$row++;		
			}
			?>
		</tbody>
	</table>
	
	<span class="block-left">
	&nbsp;&nbsp;&nbsp;&nbsp;เจ้าหน้าที่ประจำสาขา<br><br>
		________________________
	</span>
	
	<span class="block-right">
	&nbsp;&nbsp;&nbsp;&nbsp;พนักงานขับรถ<br><br>
		________________________
		
	</span>
	<div style="clear: both;"></div>
	
			<?php 
	}  
}

// _print($newArr);exit;
// $db->select("sts.rt_branch");
// $db->select("chk.check_qty");
// $db->select("rt.*")->from("outbound_car AS car");
// $db->join("outbound_rt_status AS sts", "car.outbound_rt = sts.rt_id", "LEFT");
// $db->join("outbound_rt AS rt", "sts.rt_id = rt.rt_refid", "LEFT");
// $db->join("outbound_check AS chk", "rt.id = chk.outbound_id", "INNER");

// $db->where(array(
// 		"car.car_id" => $_REQUEST['car_id']
// ));
// $sql = $db->get();
// _print($db->last_query());exit;
// $rows = $items = array();
// $rows = $sql->result_array();
// _print($rows);exit;
// _print($items);

?>

	
	<!-- 
		<embed src=”file.pdf” mce_src="<?php echo _BASE_URL_; ?>upload/examDO10092558.pdf" width="500" height="650″></embed>
 		<object type=”application/pdf” data=”/upload/examDO10092558.pdf” width=”500″ height=”650″ ></object>
		<div class="modal-footer" style="border:none">
		</div>
	-->
	
	</form>
	</div>
	<div class="modal-footer" style="border:none">
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
	<!-- <button type="button" class="btn btn-primary" onclick="$.uploadAction('<?php echo $_GET['row']; ?>', '<?php echo $_GET['rtID']; ?>');">ยืนยันออกรถ</button> -->
	<button type="button" class="btn btn-success" onclick="$.printDiv('printableDiv');">พิมพ์</button>
</div>

<script>

</script>

