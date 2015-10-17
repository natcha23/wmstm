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

$time = strtotime("now");
$date = date('Ymd');
$filename = "REPORT_OUTBOUND_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("E-Office online")
->setLastModifiedBy("E-Office Online")
->setTitle("Office 2007 XLSX Report Document")
->setSubject("Office 2007 XLSX Report Document")
->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
->setKeywords("office 2007 openxml php")
->setCategory("Aging Report");

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', 'DateProductOut')
->setCellValue('B1', 'RTNumber')
->setCellValue('C1', 'Category')
->setCellValue('D1', 'Barcode')
->setCellValue('E1', 'ProductName')
->setCellValue('F1', 'ProductUnit')
->setCellValue('G1', 'Location')
->setCellValue('H1', 'ProductQty')
->setCellValue('I1', 'ProductOutQty')
->setCellValue('J1', 'ProductRemain');

/* SQL */
$db->select("loc.qty as sum_qty_out, loc.date_out, loc.location_id, ad.add_id, ad.add_name");
$db->select("loc.before_out_qty, loc.after_out_qty");
$db->select("rt.rt_refid, rt.rt_date, rt.barcode, rt.goods_name, rt.unit, rt.category")->from("outbound_rt AS rt");
$db->join("outbound_items_location AS loc", "rt.id = loc.outbound_id", "LEFT");
$db->join("tb_address AS ad", "loc.location_id = ad.add_id");

$db->where("rt.status = 0");
$db->where("sts.status >= '2'");

$db->where("DATE(loc.date_out) = '" . $finddate . "'");

if ( !empty($finddate) ) {
	$db->where("DATE(loc.date_out) = '" . $finddate . "'");
}
if ( !empty($search) ) {
	$db->where("(rt.unit LIKE '%".$search."%' OR rt.category LIKE '%".$search."%' OR rt.rt_refid LIKE '%".$search."%'".
			" OR ad.add_name LIKE '%".$search."%' OR rt.barcode LIKE '%".$search."%' OR rt.goods_name LIKE '%".$search."%')", NULL, FALSE);
}
$db->order_by("rt.rt_refid", "ASC");
$db->order_by("loc.date_out", "ASC");

$sql_out = $db->get();
$out_arr = $sql_out->result_array();

$results = array();
foreach($out_arr as $val) {
	$db->select_sum('loc.qty', 'sum_product_qty');
	$db->select_sum('loc.qty_remain', 'sum_qty_remain');
	$db->select("po.po_id, po.po_supplier, po.cat")->from("inbound_po AS po");
	$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id");
	$db->where("po.product_no = '" . $val['barcode'] . "'");
	$db->where("loc.location_id = '". $val['add_id'] ."'");
	
	$sql_in = $db->get();
	$rs = $sql_in->row_array();
	$results[] = array_merge($val, $sql_in->row_array());
}

$total_order_qty = $total_product_qty = $total_qty = 0;
/* end SQL */

$row = 1;
$i = 2;
foreach($results as $result) {
	$diff = $result['before_out_qty'] - $result['sum_qty_out'];
	
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['date_out']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['rt_refid']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['category']);
	$objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT );
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, "'".$result['barcode']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['goods_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['unit']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $result['add_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $result['before_out_qty']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $result['sum_qty_out']);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $diff);
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