<?php

$user_id = ($_SESSION['userID'])?$_SESSION['userID'] : 0;

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
	$headingsArray = $objWorksheet->rangeToArray('A5:'.$highestColumn.'5',null, true, true, true);
	$headingsArray = $headingsArray[5];

	$r = -1;
	$dataArray = array();
	for ($row = 4; $row <= $highestRow; ++$row) {
		$dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);

		$col=0;
		if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
			++$r;
			foreach($headingsArray as $columnKey => $columnHeading) {
	// 			mb_detect_encoding($tmp)=="UTF-8" // ตรวจสอบการเข้ารหัส
                //$dataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
				$dataRow[$row][$columnKey] = utf8_decode($dataRow[$row][$columnKey]);
				$dataArray[$r][$col] = iconv( 'TIS-620', 'UTF-8', $dataRow[$row][$columnKey]);
				++$col;
			}
		}
	}

	$now = date('Y-m-d H:i:s');

	if( !empty ( $dataArray ) ) {

		foreach ( $dataArray as $value ) {

				$add_fields = array(
                    'barcode' => $value[1],
                    'name' => $value[2],
                    'cat' => $value[3],
                    'import' => $value[4],
                    'supplier' => $value[5],
                    'type' => $value[6],
                    'status' => $value[7],
                    'length' => $value[8],
                    'width' => $value[9],
                    'height' => $value[10],
                    'net_weight' => $value[11],
                    'uom' => $value[12],
                    'stackable' => $value[13],
                    'fifo_fefo' => $value[14],
                    'aging' => $value[15],
                    'update_time' => _DATE_TIME_,
                    'user_id' => $user_id,
				);

				$where = array('barcode' => $value[1]);
				$db->select('barcode')->from('tb_product');
				$db->where($where);
				$sql = $db->get();
				$count = $sql->num_rows();
				if( empty($count) ) { //ถ้าไม่มี ให้ Insert
					$db->insert('tb_product', $add_fields);
				} else { // ถ้ามีอยุ่แล้ว
                    $db->update('tb_product',$add_fields,array('barcode'=>$value[1]));
				}
// 				_print($db->last_query());
		}

	}
	echo '<script>alert("นำเข้าข้อมูลเรียบร้อยแล้ว");</script>';
}

// Check that the class exists before trying to use it
// if (class_exists('PHPExcel')) {
// 	$myclass = new MyClass();
// 	echo 'no phpExcel';exit;
// }

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
/*
	$('#nav-import').parent().addClass('active');
	$('#nav-import').addClass('in');
	$('#inbound_import').addClass('active');*/

	// initialize with defaults
// 	$("#input-1a").fileinput();

	// with plugin options
	$("#input-import-file").fileinput({
		'showUpload':true,
// 		'previewFileType':'any',
		autoReplace: true,
		maxFileCount: 1,
// 		allowedFileExtensions: ["xls", "csv"]
		allowedFileExtensions: ["xls","xlsx"]

	});


});
</script>




