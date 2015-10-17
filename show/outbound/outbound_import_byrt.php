
<?php

$user_id = ($_SESSION['userID'])?$_SESSION['userID']:0;

if(!empty($_FILES['fileToUpload']) ) {

	$inputFileName = $_FILES['fileToUpload']['tmp_name'];
	/** PHPExcel_IOFactory - Reader */
	include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';
	
	// Read CSV
	$objReader = new PHPExcel_Reader_CSV();// สร้าง object ของ Class PHPExcel_Reader_CSV
	$objReader->setInputEncoding('CP1252');// กำหนดค่าต่างตามนี้
	$objReader->setDelimiter(',');
	$objReader->setEnclosure('');
	$objReader->setLineEnding("\r\n");
	$objReader->setSheetIndex(0);
	$objPHPExcel = $objReader->load($inputFileName);	//<====File Path
	$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
	
	$highestRow = $objWorksheet->getHighestRow();
	$highestColumn = $objWorksheet->getHighestColumn();
	$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
	$headingsArray = $headingsArray[1];
	
	$r = -1;
	$dataArray = array();
	for ($row = 2; $row <= $highestRow; ++$row) {
		$dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
		if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
			++$r;
			$loop = 0;
			foreach($headingsArray as $columnKey => $columnHeading) {
	// 			mb_detect_encoding($tmp)=="UTF-8" // ตรวจสอบการเข้ารหัส
// 				$dataRow[$row][$columnKey] = utf8_decode($dataRow[$row][$columnKey]);
// 				$dataArray[$r][$loop] = iconv( 'TIS-620', 'UTF-8', $dataRow[$row][$columnKey]);
				$dataArray[$r][$loop] = $dataRow[$row][$columnKey];
				if($loop == 6) {
					$dataArray[$r][$loop] = (string)str_replace("'", "", $dataArray[$r][$loop]);
				}
				if($loop == 2) {
					$dataArray[$r][$loop] = (string)$dataArray[$r][$loop];
				}
				$loop++;
			}
			
		}
	}
	
	$now = date('Y-m-d H:i:s');
	if( !empty ( $dataArray ) ) {
		
		foreach($dataArray as $value ) {
			if(preg_match('/^RT/', $value[1])) {
				
				$fields = array(
						'outbound_id' => '',
						'order_type' => $value[0],
						'order_number' => $value[1],
						'shipto_id' => $value[2],
						'shipto_name' => $value[3],
						'date_enter' => conv2mysqldatetime($value[4]),
						'order_line' => $value[5],
						'cate_name' => null,
						'product_id' => $value[6],
						'product_name' => $value[7],
						'order_qty' => str_replace(',', '', $value[8]),
						'product_unit' => $value[9],
						'remark' => ($value[10])?$value[10]:'',
						'date_create' => $now,
						'date_update' => $now,
						'date_cancel' => '0'
				);
// 				$db->insert("sync_outbound_rt", $fields);
// 				_print($db->last_query());
				$db->select('id')->from('outbound_rt');
				$db->where(array(
						'rt_refid' => $value[1],
						'rt_date' => conv2mysqldatetime($value[4]),
						'barcode' => $value[6],
						'order_line' => $value[5],
						'status' => 0,
						'status_cancel' => 0
				));
				$query = $db->get();
				
				$outbound = array(
						'user_id' => $user_id,
						'rt_refid' => $value[1],
						'order_type' => $value[0],
						'shipto_id' => $value[2],
						'shipto_name' => $value[3],
						'rt_date' => conv2mysqldatetime($value[4]),
						'order_line' => $value[5],
						'rt_qty' => str_replace(',', '', $value[8]),
						'barcode' => $value[6],
						'goods_name' => $value[7],
						'unit' => $value[9],
						'qty_amount' => 0,
						'remark' => ($value[10])?$value[10]:'',
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
	
				$db->update("outbound_rt_status", array('status'=>1,'rt_product_amount'=>0,'sum_product'=>0), array('rt_id'=>$value[1]));
			}
		}
		echo '<script>alert("นำเข้าข้อมูลเรียบร้อยแล้ว");</script>';
	}
	
}

// Check that the class exists before trying to use it
// if (class_exists('PHPExcel')) {
// 	$myclass = new MyClass();
// 	echo 'no phpExcel';
// }

?>

<link rel="stylesheet" href="<?php echo _BASE_URL_; ?>/lib/bootstrap-fileinput/css/fileinput.min.css">
<script src="<?php echo _BASE_URL_; ?>/lib/bootstrap-fileinput/js/fileinput.min.js"></script>

<form action="" method="post" enctype="multipart/form-data">
	<div class="ibox-title" style="height: 150px;"> 
		<label class="control-label">Select *.csv file only.</label>
		<input id="input-import-file" name="fileToUpload" type="file" class="file-loading" data-preview-file-type="text" data-show-preview="false" accept="csv/*">
	</div>
</form>



<script>
$(function(){
	$('#nav-import').parent().addClass('active');
	$('#nav-import').addClass('in');
	$('#outbound_import_byrt').addClass('active');
	// initialize with defaults
// 	$("#input-1a").fileinput();

	// with plugin options
	$("#input-import-file").fileinput({
		showUpload:		true, 
		autoReplace: 	true,
		maxFileCount: 	1,
		allowedFileExtensions: ["csv"]
			
	});

});
</script>




