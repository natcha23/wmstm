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
$filename = "REPORT_SUPPLIER_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
$db->select_sum('loc.qty', 'sum_product_qty');
$db->select_sum('loc.qty_remain', 'sum_qty_remain');
$db->select("po.po_id, po.po_supplier, po.product_no, po.product_name, po.product_unit, po.cat, sup.name AS sup_name")->from("inbound_po AS po");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");
$db->where("po.cat != ''");

if (!empty($search) ) {
	$db->where("(po.po_supplier LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR po.product_no LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%' OR po.po_supplier LIKE '%".$search."%'".
			" OR sup.name LIKE '%".$search."%')", NULL, FALSE);
}

$db->group_by("po.product_no");
$db->having("sum_qty_remain > 0");
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
->setCellValue('A1', 'Supplier')
->setCellValue('B1', 'SupplierName')
->setCellValue('C1', 'Category')
->setCellValue('D1', 'Barcode')
->setCellValue('E1', 'ProductName')
->setCellValue('F1', 'ProductQty')
->setCellValue('G1', 'ProductUnit');

$i = 2;
foreach($results as $result)
{
// 	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['product_no'], PHPExcel_Cell_DataType::TYPE_STRING);
	$objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "'".$result['po_supplier']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['sup_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['cat']);
	$objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, "'".$result['product_no']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['product_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['sum_qty_remain']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $result['product_unit']);
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