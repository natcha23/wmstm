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
$filename = "REPORT_RECEIVE_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
// $db->select("user.user_fname, user.user_lname");
$db->select("sup.name AS sup_name");
$db->select("po.*, status.*")->from("inbound_status AS status");
$db->join("inbound_po AS po", "po.po_id = status.inbound_id", "LEFT");
// $db->join("user AS user", "po.user_update = user.user_id", "LEFT");
$db->join("tb_supplier AS sup", "po.po_supplier = sup.supplier_id", "LEFT");
// $db->group_by("rt.barcode");
// $db->where("loc.qty > 0");
$db->where("po.product_date_in NOT LIKE '0000-%'");

if ( !empty($finddate) ) {
	$db->where("DATE(po.po_create) = '" . $finddate . "'");
}

if ( !empty($search) ) {
	$db->where("(po.po_id LIKE '%".$search."%' OR po.cat LIKE '%".$search."%' OR po.product_name LIKE '%".$search."%'".
			" OR status.time_get_product LIKE '%".$search."%' OR po.product_no LIKE '%".$search."%'".
			" OR po.po_supplier LIKE '%".$search."%' OR sup.name LIKE '%".$search."%' OR po.product_unit LIKE '%".$search."%')", NULL, FALSE);
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
->setCellValue('A1', 'PONumber')
->setCellValue('B1', 'Supplier')
->setCellValue('C1', 'SupplierName')
->setCellValue('D1', 'Category')
->setCellValue('E1', 'Barcode')
->setCellValue('F1', 'ProductName')
->setCellValue('G1', 'ReceiveDate')
->setCellValue('H1', 'ProductUnit')
->setCellValue('I1', 'ProductQty')
->setCellValue('J1', 'ReceiveQty');

$i = 2;
$total_order_qty = $total_product_qty = 0;
foreach($results as $result)
{
	$total_order_qty += $result['order_qty'];
	$total_product_qty += $result['product_qty'];
	
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['inbound_id']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['po_supplier']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['sup_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $result['cat']);
	$objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['product_no']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['product_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $result['time_get_product']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $result['product_unit']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $result['order_qty']);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $result['product_qty']);
	$i++;
}

// $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "Total");
// $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['po_supplier']);
// $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['cat']);
// $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $result['product_no']);
// $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['product_name']);
// $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['time_get_product']);
$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, "Total");
$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $total_order_qty);
$objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $total_product_qty);


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