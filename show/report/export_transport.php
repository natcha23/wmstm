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
if ( empty($search) && empty($_GET['finddate']) ) {
	$_GET['finddate'] = date('Y-m-d');
}
$finddate = isset($_GET['finddate'])?$_GET['finddate']:'';
$start = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 12:00:00';
$stop = $finddate.' 11:59:59';
$user_id = $_SESSION['userID'];

$time = strtotime("now");
$date = date('Ymd');
$filename = "REPORT_TRANSPORT_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export/";
$filepath = $xlspath . $filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();


/* SQL */

if ( !empty($search) ) {
	$search_id = '';
	$db->select("car_id")->from("car_list")->where("car_code LIKE '%".$search."%' AND status = 0");
	$findcarsql = $db->get();
	$search_id = $findcarsql->row('car_id');
	
	$wheremessage = "car_id = '" . $search_id . "'";

	if( empty($search_id) ) {
		$db->select("user_id")->from("user")->where("status = 0");
		$db->where("(user_fname LIKE '%".$search."%' OR user_lname LIKE '%".$search."%')");
		$driversql = $db->get();
		$search_id = $driversql->row('user_id');
		
		$wheremessage = "driver_id = '" . $search_id . "'";
	}
}

$db->select("outcar.*");
$db->select("rt.shipto_name");
$db->select("sts.time_out_car, sts.time_branch_confirm, sts.delivery_order_id");
$db->from("outbound_car AS outcar");

$db->join("outbound_rt AS rt", "outcar.outbound_rt = rt.rt_refid", "LEFT");
$db->join("outbound_rt_status AS sts", "outcar.outbound_rt = sts.rt_id", "LEFT");

if ( !empty($finddate) ) {
	$db->where("DATE(outcar.date_time) = '" . $finddate . "'");
}

if ( !empty($search) ) {
	$db->where("(rt.rt_refid LIKE '%".$search."%' OR sts.delivery_order_id LIKE '%".$search."%'".
			" OR rt.shipto_name LIKE '%".$search."%' OR other_car_code LIKE '%".$search."%' OR driver_name LIKE '%".$search."%')", NULL, FALSE);

	if( !empty($search_id) ) {
		$db->or_where($wheremessage);
	}
}

$db->group_by("outcar.outbound_rt");

$sql = $db->get();
// _print($db->last_query());exit;
$results = $sql->result_array();
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
->setCellValue('A1', 'CarNumber')
->setCellValue('B1', 'DriverName')
->setCellValue('C1', 'DONumber')
->setCellValue('D1', 'RTNumber')
->setCellValue('E1', 'BranchDestination')
->setCellValue('F1', 'DateTransport');
// ->setCellValue('G1', 'DateDestination')
// ->setCellValue('H1', 'Status');

$i = 2;
$total_order_qty = $total_product_qty = 0;
foreach($results as $result)
{
	
	if($result['car_id'] != -1) {
		$carsql = '';
		$db->select('car_code, car_detail')->from('car_list AS car')->where('car_id', $result['car_id']);
		$carsql = $db->get();
		$car_code = $carsql->row_array();
		$result['car_code'] = $car_code['car_code'];
	} else {
		$result['car_code'] = $result['other_car_code'];
	}
	$driver_name = '';
	if($result['driver_id'] != -1) {
		$db->select("user.user_fname, user.user_lname")->from("user")->where("user_id", $result['driver_id']);
		$driver_sql = $db->get();
		$driver = $driver_sql->row_array();
		$driver_name = $driver['user_fname'] . " " . $driver['user_lname'];
	} else {
		
		$driver_name = $result['driver_name'];
	}
	
	$label = "label-warning";
	$status_process = '';
	switch ($result['status_process']) {
		case 0 : $status_process = "รอรถออก";		break;
		case 1 : $status_process = "ออกรถไปสาขา";	break;
		case 2 : $status_process = "ถึงสาขา";		break;
			
		default :	break;
	}
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['car_code']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $driver_name);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $result['delivery_order_id']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $result['outbound_rt']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $result['shipto_name']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $result['date_time']);
// 	$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($result['time_branch_confirm'] != '0000-00-00 00:00:00')? $result['time_branch_confirm']:"-");
// 	$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $status_process);
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