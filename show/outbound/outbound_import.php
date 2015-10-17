
<?php

$user_id = ($_SESSION['userID'])?$_SESSION['userID']:0;

if(!empty($_FILES['fileToUpload']) ) {

	$inputFileName = $_FILES['fileToUpload']['tmp_name'];
	/** PHPExcel_IOFactory - Reader */
	include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';
	
	$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($inputFileName);
	
	$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
	$highestRow = $objWorksheet->getHighestRow();
	$highestColumn = $objWorksheet->getHighestColumn();
	$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
	$headingsArray = $headingsArray[1];
	
	$r = -1;
	$dataArray = array();
	for ($row = 3; $row <= $highestRow; ++$row) {
		$dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
		if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
			++$r;
			foreach($headingsArray as $columnKey => $columnHeading) {
	// 			mb_detect_encoding($dataRow[$row][$columnKey])//=="UTF-8" // ตรวจสอบการเข้ารหัส
// 				$dataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
				$dataRow[$row][$columnKey] = utf8_decode($dataRow[$row][$columnKey]);
				$dataArray[$r][$columnHeading] = iconv( 'TIS-620', 'UTF-8', $dataRow[$row][$columnKey]);
			}
		}
	}
	
	$now = date('Y-m-d H:i:s');
	
	if( !empty ( $dataArray ) ) {
		
		foreach($dataArray as $value ) {
			if(preg_match('/^RT/', $value['Order Number'])) {
				
				$db->select('id')->from('outbound_rt');
				$db->where(array(
						'rt_refid' => $value['Order Number'],
						'rt_date' => conv2mysqldatetime($value['Date Entered']),
						'barcode' => $value['Barcode'],
						'order_line' => $value['Order Line'],
						'status' => 0,
						'status_cancel' => 0
				));
				$query = $db->get();
				
				$outbound = array(
						'user_id' => $user_id,
						'rt_refid' => $value['Order Number'],
						'order_type' => $value['Order TYPE'],
						'shipto_id' => $value['Ship-To store'],
						'shipto_name' => $value['Ship to Name'],
						'rt_date' => conv2mysqldatetime($value['Date Entered']),
						'order_line' => $value['Order Line'],
						'category' => $value['CAT'],
						'rt_qty' => str_replace(',', '', $value['Ordered Quantity']),
						'barcode' => $value['Barcode'],
						'goods_name' => $value['Name'],
						'unit' => $value['UOM'],
						'qty_amount' => 0,
						'remark' => ($value['Remark'])?$value['Remark']:'',
						'date_create' => $now,
						'date_update' => $now,
						'date_cancel' => NULL
				);
				
				if ($query->num_rows() == 0) {
					$db->insert("outbound_rt", $outbound);
				} else {
					foreach ($query->result() as $row)
					{
						$key_id = $row->id;
					}
					$where = array('id' => $key_id);
					unset($outbound['date_create']);
					$db->update('outbound_rt', $outbound, $where);
				}
				/* Save LOG table */
				unset($outbound['date_update']);
				unset($outbound['date_update']);
// 				$db->insert("outbound_rt_log", $outbound);
	// 			_print($db->last_query());exit;
				$db->update("outbound_rt_status", array('status'=>1,'rt_product_amount'=>0,'sum_product'=>0), array('rt_id'=>$value['Order Number']));
			}
		}
		echo '<script>alert("นำเข้าข้อมูลเรียบร้อยแล้ว");</script>';
	
	}
}

?>

<link rel="stylesheet" href="<?php echo _BASE_URL_; ?>/lib/bootstrap-fileinput/css/fileinput.min.css">
<script src="<?php echo _BASE_URL_; ?>/lib/bootstrap-fileinput/js/fileinput.min.js"></script>

<form action="" method="post" enctype="multipart/form-data">
	<div class="ibox-title" style="height: 150px;"> 
	
		<label class="control-label">Select *.xls file only.</label>
		<input id="input-import-file" name="fileToUpload" type="file" class="file-loading" data-preview-file-type="text" data-show-preview="false" accept="csv/*">
	</div>
</form>

<script>
$(function(){

	$('#nav-import').parent().addClass('active');
	$('#nav-import').addClass('in');
	$('#outbound_import').addClass('active');	
	
	// initialize with defaults
// 	$("#input-1a").fileinput();

	// with plugin options
	$("#input-import-file").fileinput({
		'showUpload':true, 
// 		'previewFileType':'any',
		autoReplace: true,
		maxFileCount: 1,
// 		allowedFileExtensions: ["xls", "csv"]
		allowedFileExtensions: ["xls"]
			
	});


});
</script>




