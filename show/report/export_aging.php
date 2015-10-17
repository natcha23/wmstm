<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');

$db = DB();
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$search = isset($_GET['search'])?$_GET['search']:'';

$time = strtotime("now");
$date = date('Ymd');
$filename = "REPORT_AGING_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
$db->select_sum('loc.qty', 'sum_product_qty');
$db->select_sum('loc.qty_remain', 'sum_qty_remain');
$db->select("po.cat, sup.name AS sup_name");
$db->select("po.po_id, po.po_supplier, po.product_no, po.product_name, po.product_unit, po.product_date_in, po.product_qty")->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");

$db->where("po.product_qty > 0");
$db->where("DATE(po.product_date_in) != '0000-00-00'");

if(!empty($search)) {
	$db->where("(po.po_id LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%' OR po.product_date_in LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%' OR po.po_supplier LIKE '%".$search."%')", NULL, FALSE);
	$db->having("sum_qty_remain > 0");
}

$db->group_by("po.product_no");
$db->order_by("po.product_date_in DESC");

$sql = $db->get();
$results = $sql->result_array();
/* end SQL */

$row = 1;
$objPHPExcel->getProperties()->setCreator("E-Office online")
->setLastModifiedBy("E-Office Online")
->setTitle("Office 2007 XLSX Report Document")
->setSubject("Office 2007 XLSX Report Document")
->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
->setKeywords("office 2007 openxml php")
->setCategory("Aging Report");

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', 'Barcode')
->setCellValue('B1', 'ProductName')
->setCellValue('C1', 'Category')
->setCellValue('D1', 'ReceiveDate')
->setCellValue('E1', 'ProductQty')
->setCellValue('F1', 'ProductUnit')
->setCellValue('G1', 'Supplier')
->setCellValue('H1', 'SupplierName')
->setCellValue('I1', 'DayInStore');

$i = 2;
foreach($results as $result)
{
// 	$originalDate = $result['product_date_in'];
// 	$newDate = date("Y-m-d", strtotime($originalDate));
	
// 	$arrival = explode('-', $newDate); #-----------เป็นวันแรก (ปี เดือน วัน) [yyyymmdd]
// 	$departure = date("Y-m-d"); #----------เป็นวันสุดท้าย
// 	// แยก วัน เดือน ปี ออกมาเพื่อคำนวณ
	
// 	$year = $arrival[0];
// 	$mount = $arrival[1];
// 	$day = $arrival[2];
	
// 	//เช็ค error ของ วันเดือนปี
// 	if ($newDate<=$departure) { $pass = 1; } else { $pass = 0; }
// 	if ($pass == 0) { echo "Error is $checkin < $checkout"; exit(); }
// 	//คำนวณหา วันสุดท้าย - วันแรก มีทั้งหมดกี่วัน
// 	$count_date = 0;
// 	$arrival1 = $arrival;
// 	while ($arrival1 != $departure) {
// 		$count_date++;
// 		$arrival1 = date("Y-m-d",mktime (0,0,0,$mount,$day+$count_date,$year));
// 	}
	
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
	
// 	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['product_no'], PHPExcel_Cell_DataType::TYPE_STRING);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "'".$result['product_no']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['product_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['cat']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $result['product_date_in']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['product_qty']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['product_unit']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, "'".$result['po_supplier']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $result['sup_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $Days);
	$i++;
}

if (!file_exists($xlspath)) {
	mkdir($xlspath, 0777);
}

// $objPHPExcel->getActiveSheet()->setTitle('Product Aging Report');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($filepath);

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=".basename($filepath).";");
header("Content-Transfer-Encoding: binary ");
header("Content-Type: application/vnd.ms-excel");
header("Content-Length: ".filesize($filepath));

readfile($filepath);
unlink($filepath);
ob_end_flush();
exit;
?>