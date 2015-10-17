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
$filename = "REPORT_STATUS_PROD_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export";
$filepath = $xlspath . "/" .$filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
$db->select("po.po_id, po.po_create, po.po_id AS doc_id, po.po_create AS doc_create");
$db->select("SUM(CASE WHEN status.status = '1' THEN loc.qty_remain ELSE 0 END) inbound_products, ".
		"SUM(CASE WHEN status.status = '2' THEN loc.qty_remain ELSE 0 END) store_products " );
$db->from("inbound_status AS status");
$db->join("inbound_po AS po", "status.inbound_id = po.po_id", "INNER");
$db->join("inbound_location AS loc", "po.inbound_id = loc.inbound_id", "LEFT");

if ( !empty($finddate) ) {
	$db->where("DATE(status.start_date) = '" . $finddate . "'");
}

if ( !empty($search) ) {
	$db->where("(po_id LIKE '%".$search."%')", NULL, FALSE);
}
// $db->where("DATE(status.start_date) = '" .$finddate. "'");
$db->group_by("po.po_id");
$sql = $db->get();
$poresult = $sql->result_array();

$db->select("rt_id, rt_date, rt_id AS doc_id, rt_date AS doc_create");
$db->select("SUM(CASE WHEN status.status = '1' THEN status.sum_product ELSE 0 END) launch_products, ".
		"SUM(CASE WHEN status.status = '2' THEN status.sum_product ELSE 0 END) pickup_products, ".
		"SUM(CASE WHEN status.status = '3' THEN status.sum_product ELSE 0 END) checkingout_products, ".
		"SUM(CASE WHEN status.status = '4' THEN status.sum_product ELSE 0 END) choosecar_products, ".
		"SUM(CASE WHEN status.status = '5' THEN status.sum_product ELSE 0 END) transport_products, ".
		"SUM(CASE WHEN status.status = '7' THEN status.sum_product ELSE 0 END) tobranch_products");

$db->select("SUM(CASE WHEN status.status = '1' THEN status.sum_product ELSE 0 END) launch_items, ".
		"SUM(CASE WHEN status.status = '2' THEN status.rt_product_amount ELSE 0 END) pickup_items, ".
		"SUM(CASE WHEN status.status = '3' THEN status.rt_product_amount ELSE 0 END) checkingout_items, ".
		"SUM(CASE WHEN status.status = '4' THEN status.rt_product_amount ELSE 0 END) choosecar_items, ".
		"SUM(CASE WHEN status.status = '5' THEN status.rt_product_amount ELSE 0 END) transport_items, ".
		"SUM(CASE WHEN status.status = '7' THEN status.rt_product_amount ELSE 0 END) tobranch_items");
$db->from("outbound_rt_status AS status");

if ( !empty($finddate) ) {
	$db->where("DATE(status.update_time) = '" . $finddate . "'");	
}

if ( !empty($search) ) {
	$db->where("(rt_id LIKE '%".$search."%')", NULL, FALSE);
}

$db->group_by("status.rt_id");

$sql = $db->get();
$results = array_merge($poresult, $sql->result_array());
$totalArr = array();
// _print($db->last_query());exit;
/* end SQL */

$row = 1;
$objPHPExcel->getProperties()->setCreator("E-Office online")
->setLastModifiedBy("E-Office Online")
->setTitle("Office 2007 XLSX Report Document")
->setSubject("Office 2007 XLSX Report Document")
->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
->setKeywords("office 2007 openxml php")
->setCategory("Warehouse System Report");

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', 'DocumentNumber')
->setCellValue('B1', 'DateDocument')
->setCellValue('C1', 'CheckingIn')
->setCellValue('D1', 'InStore')
->setCellValue('E1', 'Launch')
->setCellValue('F1', 'PickUp')
->setCellValue('G1', 'CheckingOut')
->setCellValue('H1', 'ChooseCar')
->setCellValue('I1', 'Transport');
// ->setCellValue('J1', 'ToBranch');

$i = 2;
$total_inbound = $total_store = 0;
$total_launch = $total_pickup = 0;
$total_checkingout = $total_choosecar = 0;
$total_transport = $total_tobranch = 0;

foreach($results as $result)
{
	$total_inbound 		+= (!empty($result['inbound_products']))?$result['inbound_products']:0;
	$total_store 		+= (!empty($result['store_products']))?$result['store_products']:0;
	$total_launch	 	+= (!empty($result['launch_products']))?$result['launch_products']:0;
	$total_pickup 		+= (!empty($result['pickup_products']))?$result['pickup_products']:0;
	$total_checkingout 	+= (!empty($result['checkingout_products']))?$result['checkingout_products']:0;
	$total_choosecar 	+= (!empty($result['choosecar_products']))?$result['choosecar_products']:0;
	$total_transport 	+= (!empty($result['transport_products']))?$result['transport_products']:0;
	$total_tobranch 	+= (!empty($result['tobranch_products']))?$result['tobranch_products']:0;
	
					
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['doc_id']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['doc_create']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, (!empty($result['inbound_products']))?$result['inbound_products']:0);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, (!empty($result['store_products']))?$result['store_products']:0);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, (!empty($result['launch_products']))?$result['launch_products']:0);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, (!empty($result['pickup_products']))?$result['pickup_products']:0);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, (!empty($result['checkingout_products']))?$result['checkingout_products']:0);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, (!empty($result['choosecar_products']))?$result['choosecar_products']:0);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, (!empty($result['transport_products']))?$result['transport_products']:0);
// 	$objPHPExcel->getActiveSheet()->setCellValue('J' . $i, (!empty($result['tobranch_products']))?$result['tobranch_products']:0);
	$i++;
}

$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, "Total");
$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $total_inbound);
$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $total_store);
$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $total_launch);
$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $total_pickup);
$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $total_checkingout);

$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $total_choosecar);
$objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $total_transport);
// $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $total_tobranch);

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