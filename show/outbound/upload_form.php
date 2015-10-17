<?php

require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');
$db = DB();

$db->select("*")->from("outbound_car AS ocr");
$db->join("car_list AS car", "ocr.car_id = car.car_id", "LEFT");
$db->where(array("outbound_rt" => $_REQUEST['rtID']));

$sql = $db->get();
$result = $sql->row_array();
// _print($result);
?>

<style></style>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">
		<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
	</button>
	<label class="modal-title">RT No. :  <?php echo $_GET['rtID']; ?> <br> ทะเบียนรถ :  <span id="car_plate"><?php echo (!empty($result['car_code']))? $result['car_code']:$result['other_car_code']; ?></span></label>
</div>
<div class="modal-body">
	<form method="post" enctype="multipart/form-data">
		<div class="control-group">
			<label class="control-label"></label>
			<div class="controls">
				<span class="btUpload"></span>
				<div id="uploadArea">
				<?php
				$db->select("*");
				$db->from("outbound_upload");
				$db->where(array("rt_id" => $_GET['rtID'],
						"status" => 0,
						"status_process" => 5
				));

				$sql = $db->get();
				$numrows = $sql->num_rows();
				foreach($sql->result_array() as $result) {
					$aFile = getdownload($result['file_id']);
					?>
					<div id="image_<?php echo $result['id'] ;?>" class="img_thumb">
					<div onclick="$.removeFile('outbound_upload','<?php echo $result['id'] ;?>')">
						<i class="fa fa-times img_remove"></i>
					</div>
					
					<a href="<?php echo $aFile['link']; ?>&ff=<?php echo $aFile['name'] ?>"
					class="fancybox" data-fancybox-group="galleryCase"
					style="background-color: #ffffff" target="_blank"
					onmouseover="$(this).tooltip();"
					title="<?php echo $aFile['name'] ?>"> 
						<img src="<?php echo iconImg($aFile['name'], $aFile['link'] . "&size=100"); ?>" 
						style="height:97px;width:100px"
						alt="<?php echo $aFile['name'] ?>"
						title="<?php echo $aFile['name'] ?>">
					</a>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>

		<div class="modal-footer" style="border:none">
		</div>
	</form>	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
	<button type="button" class="btn btn-primary" onclick="$.uploadAction('<?php echo $_GET['row']; ?>', '<?php echo $_GET['rtID']; ?>');">ยืนยันออกรถ</button>
</div>
