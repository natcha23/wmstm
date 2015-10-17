<?php
// phpinfo();exit;
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
	// 			mb_detect_encoding($tmp)=="UTF-8" // ตรวจสอบการเข้ารหัส
				$dataRow[$row][$columnKey] = utf8_decode($dataRow[$row][$columnKey]);
				$dataArray[$r][$columnHeading] = iconv( 'TIS-620', 'UTF-8', $dataRow[$row][$columnKey]);
			}
		}
	}

	$now = date('Y-m-d H:i:s');
	
	/////////import inbound make data
	$supplierArr = array("01001428","01001292","01001066","01000960","01001476","01001435","01000475");
	
	$start_date = "2015-10-01";
	$end_date = "2016-09-30";
	// Convert to timetamps
	$min = strtotime($start_date);
	$max = strtotime($end_date);
	/////////import inbound make data
	
	if( !empty ( $dataArray ) ) {
		
		foreach($dataArray as $value ) {
			if(preg_match('/^RT/', $value['Order Number'])) {
				
				/////////import inbound make data
// 				include_once "make_data.php"; // this will include php file
				$DateEntered = conv2mysqldatetime( $value['Date Entered'] );
				$datedelivery = explode(' ', $DateEntered);
				
				$isFEFO = rand(0, 1);
				$dateFEFO = null;
				if($isFEFO == 1) {
					$int = mt_rand($min, $max);
					$dateFEFO = date("Y-m-d", $int);
				}
				
				$rand_supplier = $supplierArr[array_rand($supplierArr, 1)];
				$rand_location = rand(1, 4182);
				$rand_date = rand(1, 7);
				$delivery_date  = mktime(0, 0, 0, date("m")  , date("d")+$rand_date, date("Y"));
				
				$inbound_po_fields = array(
				
						'po_id' => str_replace('RT', 'PO', $value['Order Number']),
						'po_create' => '2015-06-01 12:00:00',
						'po_delivery_date' => date('Y-m-d', $delivery_date), //$datedelivery[0],
						'po_supplier' => $rand_supplier,
						'product_no' => $value['Barcode'],
						'product_name' => "[test data] ".$value['Name'],
						'order_qty' => str_replace(',', '', $value['Ordered Quantity']),
						'product_qty' => str_replace(',', '', $value['Ordered Quantity']),
						'product_unit' => $value['UOM'],
						'product_date_in' => conv2mysqldatetime($value['Date Entered']),
						'product_create_date' => $now,
						'product_fefo' => $isFEFO,
						'product_fefo_date' => $dateFEFO,
						'user_create' => $user_id,
						'user_update' => $user_id,
						'note' => 'onrt',
						'po_status' => 2,
						'datecreate' => $now,
						'dateupdate' => $now,
						'cat' => $value['CAT'],
				
				);
				$db->insert('inbound_po', $inbound_po_fields);
// 				_print($db->last_query());
				$last_id = $db->insert_id();
				$inbound_location_fields = array(
				
						'inbound_id' => $last_id,
						'location_id' => $rand_location,
						'qty' => str_replace(',', '', $value['Ordered Quantity']),
						'qty_remain' => str_replace(',', '', $value['Ordered Quantity']),
						'time' => $now,
						'update_time' => $now,
						'user_id' => $user_id,
						'user_id_update' => $user_id,
						'action_status' => 1,
						'note' => 'onrt'
				
				);
				$db->insert('inbound_location', $inbound_location_fields);
				
				// inbound_status
				$inbound_status_fields = array(
						'inbound_id' => str_replace('RT', 'PO', $value['Order Number']),
						'start_date' => $now,
						'status' => 1,
						'time_get_product' => $now,
						'time_in_stock' => $now
				);
				
// 				$where = array("inbound_id" => str_replace('RT', 'PO', $value['Order Number']));
// 				$db->select("inbound_id")->from("inbound_status")->where($where);
// 				$sql = $db->get();
// 				$count = $sql->num_rows();
// 				if(empty($count)) {
// 					$db->insert('inbound_status', $inbound_status_fields);
// 				}else{
// 					$result = $sql->row();
// 					$inbound_id = $result->inbound_id;
// 					$inbound_status_update_fields = array(
// 							'product_qty' => str_replace(',', '', $value['Ordered Quantity']) + $product_qty,
// 							'product_update' => $now,
// 							'user_id' => $user_id
// 					);
// 					$db->update("inbound_status", $inbound_status_update_fields, $where);
// 				}
				// END inbound_status
				
				
				$stock_product_fields = array(
						'product_id' => $value['Barcode'],
						'product_name' => $value['Name'],
						'product_unit' => $value['UOM'],
						'product_qty' => str_replace(',', '', $value['Ordered Quantity']),
						'num_exp' => null,
						'qty_max' => null,
						'product_update' => $now,
						'user_id' => $user_id,
						'note' => ''
				);
				$where = array("product_id" => $value['Barcode']);
				$db->select("product_qty")->from("stock_product")->where($where);
				$sql = $db->get();
				$count = $sql->num_rows();
				if(!empty($count)) {
					$result = $sql->row();
					$product_qty = $result->product_qty;
					$stock_product_update_fields = array(
							'product_qty' => str_replace(',', '', $value['Ordered Quantity']) + $product_qty,
							'product_update' => $now,
							'user_id' => $user_id
					);
					$db->update("stock_product", $stock_product_update_fields, $where);
					
				}else{
					$db->insert('stock_product', $stock_product_fields);
				}
				
				///////////import inbound make data
				
				
				
				
				
				
				$fields = array(
						'outbound_id' => '',
						'order_type' => $value['Order TYPE'],
						'order_number' => $value['Order Number'],
						'shipto_id' => $value['Ship-To store'],
						'shipto_name' => $value['Ship to Name'],
						'date_enter' => conv2mysqldatetime($value['Date Entered']),
						'order_line' => $value['Order Line'],
						'category' => $value['CAT'],
						'product_id' => $value['Barcode'],
						'product_name' => $value['Name'],
						'order_qty' => str_replace(',', '', $value['Ordered Quantity']),
						'product_unit' => $value['UOM'],
						'remark' => ($value['Remark'])?$value['Remark']:'',
						'date_create' => $now,
						'date_update' => $now,
						'date_cancel' => '0'
				);
				
				
				
// 				$db->insert("sync_outbound_rt", $fields);
// 				_print($db->last_query());
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
// 				_print($db->last_query());exit;
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
// 					$db->insert("outbound_rt", $outbound);
				} else {
					foreach ($query->result() as $row)
					{
						$key_id = $row->id;
					}
					$where = array('id' => $key_id);
					unset($outbound['date_create']);
// 					$db->update('outbound_rt', $outbound, $where);
				}
				/* Save LOG table */
				unset($outbound['date_update']);
				unset($outbound['date_update']);
// 				$db->insert("outbound_rt_log", $outbound);
	// 			_print($db->last_query());exit;
// 				$db->update("outbound_rt_status", array('status'=>1,'rt_product_amount'=>0,'sum_product'=>0), array('rt_id'=>$value['Order Number']));
			}
		}
		
	
	}
}

// Check that the class exists before trying to use it
if (class_exists('PHPExcel')) {
// 	$myclass = new MyClass();
	echo 'no phpExcel';
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




