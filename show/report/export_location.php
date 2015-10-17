<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
require_once('../../helper/class_upload.php');

$db = DB();
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$find_status = isset($_GET['findstatus'])?$_GET['findstatus']:'-1';
$search = isset($_GET['search'])?$_GET['search']:'';

$time = strtotime("now");
$date = date('Ymd');
$filename = "REPORT_LOCATION_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
$db->select_sum("loc.qty_remain", "sum_qty_remain");
$db->select("loc.location_id, tbadd.add_name, tbadd.blank_status, loc.qty_remain");
$db->select("po.po_id, po.po_supplier, po.product_no, po.product_name, po.product_unit, po.product_date_in, po.product_qty, po.cat");

$db->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
$db->join("tb_address AS tbadd", "loc.location_id = tbadd.add_id", "LEFT");

if( $find_status != -1 ){
	$db->where("tbadd.blank_status = '" .$find_status. "'");
}

if (!empty($search) ) {
	$db->where("(po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR tbadd.add_name LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%' OR po.product_unit LIKE '%".$search."%')", NULL, FALSE);
}

$db->where("loc.qty_remain > 0");

$db->group_by("loc.location_id");
$db->group_by("po.product_no");

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
->setCellValue('A1', 'Barcode')
->setCellValue('B1', 'ProductName')
->setCellValue('C1', 'Category')
->setCellValue('D1', 'Location')
->setCellValue('E1', 'ProductUnit')
->setCellValue('F1', 'ProductQty');

$i = 2;
$total_order_qty = $total_product_qty = 0;
foreach($results as $result)
{
	$objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "'".$result['product_no']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['product_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['cat']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $result['add_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['product_unit']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['qty_remain']);
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