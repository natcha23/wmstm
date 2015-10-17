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
	for ($row = 6; $row <= $highestRow; ++$row) {
		$dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);

		$col=0;
		if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
			++$r;
			foreach($headingsArray as $columnKey => $columnHeading) {
	// 			mb_detect_encoding($tmp)=="UTF-8" // ตรวจสอบการเข้ารหัส
				$dataRow[$row][$columnKey] = utf8_decode($dataRow[$row][$columnKey]);
				$dataArray[$r][$col] = iconv( 'TIS-620', 'UTF-8', $dataRow[$row][$columnKey]);
				++$col;
			}
		}
	}

	$now = date('Y-m-d H:i:s');

	if( !empty ( $dataArray ) ) {

		foreach ( $dataArray as $value ) {

				$inbound_po_fields = array(

						'po_id' =>  $value[3],
						'ibp_id' => '',
                        'receipt_type' => $value[4],
						'po_create' => $now,
						'po_delivery_date' => conv2mysqldatetime($value[0]),
						'po_supplier' => $value[2],
						'product_no' => $value[6],
						'product_name' => $value[7],
						'cat' => $value[11],
						'order_qty' => str_replace(',', '', $value[8]),
						'product_qty' => 0,
						'free_qty' => 0,
						'product_unit' => $value[9],
						'product_date_in' => 0,
						'product_create_date' => 0,
						'product_fefo' => 0,
						'product_fefo_date' => 0,
						'user_create' => $user_id,
						'user_update' => $user_id,
						'note' => $value[13],
						'po_status' => 0,
						'datecreate' => $now,
						'dateupdate' => $now,
						'status' => (strtolower($value[12])=='active')?0:1

				);

				$where = array('po_id' => $value[3], 'product_no' => $value[6]);
				$db->select('po_id')->from('inbound_po');

				$db->where($where);
				$sql = $db->get();
				$count = $sql->num_rows();
				if( empty($count) ) { //INSERT
					$db->insert('inbound_po', $inbound_po_fields);
				} else { // UPDATE
					unset($inbound_po_fields['user_create']);
					unset($inbound_po_fields['datecreate']);
					unset($inbound_po_fields['po_create']);

					$db->update('inbound_po', $inbound_po_fields, $where);
				}
// 				_print($db->last_query());
				// inbound_status
				$inbound_status_fields = array(
						'inbound_id' => $value[3],
						'start_date' => 0,
						'status' => 0,
						'time_get_product' => 0,
						'time_in_stock' => 0
				);

				$where_status = array("inbound_id" => $value[3]);
				$db->select("inbound_id")->from("inbound_status")->where($where_status);

				$sql_status = $db->get();
				$count_status = $sql_status->num_rows();

				if(empty($count_status)) {
					$db->insert('inbound_status', $inbound_status_fields);
				} else {
					$inbound_status_update = array('status' => 0);
					$db->update('inbound_status', $inbound_status_update, $where_status);
				}
				// END inbound_status

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

	$('#nav-import').parent().addClass('active');
	$('#nav-import').addClass('in');
	$('#inbound_import').addClass('active');

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




