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
if(empty($search) && empty($_GET['finddate'])) {
	$_GET['finddate'] = date('Y-m-d');
}
$finddate = isset($_GET['finddate'])?$_GET['finddate']:'';
$start = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 12:00:00';
$stop = $finddate.' 11:59:59';
$user_id = $_SESSION['userID'];

$time = strtotime("now");
$date = date('Ymd');
$filename = "REPORT_INBOUND_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
$db->select("product_no, po_id, add_name, po_create, po_delivery_date, po_supplier, product_name, product_unit, time");
$db->select("loc.qty, loc.qty_remain, po.cat, sup.name AS sup_name");

$db->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id", "LEFT");
$db->join("tb_address AS ad", "loc.location_id = ad.add_id", "INNER");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");
// $db->where("loc.time BETWEEN '". $start ."' AND '". $stop ."'");

if ( !empty($finddate) ) {
	$db->where("loc.time = '$finddate'");
}

if ( !empty($search) ) {
	$db->where("(po_id LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR add_name LIKE '%".$search."%' OR product_no LIKE '%".$search."%' OR sup.name LIKE '%".$search."%')", NULL, FALSE);
}

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

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', 'DateProductIn')
->setCellValue('B1', 'PONumber')
->setCellValue('C1', 'Supplier')
->setCellValue('D1', 'SupplierName')
->setCellValue('E1', 'Category')
->setCellValue('F1', 'Barcode')
->setCellValue('G1', 'ProductName')
->setCellValue('H1', 'Location')
->setCellValue('I1', 'ProductUnit')
->setCellValue('J1', 'ProductQty')
->setCellValue('K1', 'ProductRemain');

$i = 2;
$total_order_qty = $total_product_qty = 0;
foreach($results as $result)
{
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['time']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['po_id']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['po_supplier']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $result['sup_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['cat']);
	$objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['product_no']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $result['product_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $result['add_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $result['product_unit']);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $result['qty']);
	$objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $result['qty_remain']);
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