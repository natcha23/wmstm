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
$finddate 	= isset($_GET['finddate'])?$_GET['finddate']:'';
$zone 		= isset($_GET['zone'])?$_GET['zone']:'0';
$user_id	= $_SESSION['userID'];

$time = strtotime("now");
$date = date('Ymd');
$filename = "REPORT_PRODUCT_TRANSFER_".$date.$time.".xls";
$xlspath = _DOC_ROOT_."/export";
$filepath = $xlspath . "/" .$filename;

/** PHPExcel_IOFactory */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

/* SQL */
// $db->select('zone_id, zone_name')->from('tb_zone');
// $db->where(array('status' => 0));
// $zoneSQL = $db->get();
// $zoneArr = $zoneSQL->result_array();

$limit = 100;
$offset = 0;
$sqlwhere = '';
$sqlmsg = "SELECT stk.product_id, stk.product_name, stk.product_qty, rtloc.location_id, ".
			
		" SUM(CASE WHEN posts.status = '1' THEN poloc.qty_remain ELSE 0 END) inbound_products, ".
		" SUM(CASE WHEN posts.status = '2' THEN poloc.qty_remain ELSE 0 END) store_products, ".

		" SUM(CASE WHEN rtsts.status = '1' THEN rtloc.qty ELSE 0 END) launch_products, ".
		" SUM(CASE WHEN rtsts.status = '2' THEN rtloc.qty ELSE 0 END) pickup_products, ".
		" SUM(CASE WHEN rtsts.status = '3' THEN rtloc.qty ELSE 0 END) checkingout_products, ".
		" SUM(CASE WHEN rtsts.status = '4' THEN rtloc.qty ELSE 0 END) choosecar_products, ".
		" SUM(CASE WHEN rtsts.status = '5' THEN rtloc.qty ELSE 0 END) transport_products, ".
		" SUM(CASE WHEN rtsts.status = '7' THEN rtloc.qty ELSE 0 END) tobranch_products" .

		" FROM stock_product AS stk".
		" LEFT JOIN inbound_po AS po ON stk.product_id = po.product_no".
		" LEFT JOIN inbound_status AS posts ON po.po_id = posts.inbound_id".
		" LEFT JOIN inbound_location AS poloc ON po.inbound_id = poloc.inbound_id".

		" LEFT JOIN outbound_rt AS rt ON stk.product_id = rt.barcode".
		" LEFT JOIN outbound_rt_status AS rtsts ON rt.rt_refid = rtsts.rt_id".
		" LEFT JOIN outbound_items_location AS rtloc ON rt.id = rtloc.outbound_id AND rtloc.status = 0";
	
if ( !empty($finddate) ) {
			$sqlwhere .= (!empty($sqlwhere))? " AND ":" WHERE ";
			$sqlwhere .= " DATE(stk.product_update) = '" .$finddate. "'";
}

if ( !empty($search) ) {
	$sqlwhere .= (!empty($sqlwhere))? " AND ":" WHERE ";
	$sqlwhere .= " (stk.product_id LIKE '%" .$search. "%' OR stk.product_name LIKE '%" .$search. "%')";
}
if ( $zone > 0 ) {
	$sqlmsg .= " LEFT JOIN tb_address AS poaddr ON (poloc.location_id = poaddr.add_id)";
	$sqlmsg .= " LEFT JOIN tb_address AS rtaddr ON (rtloc.location_id = rtaddr.add_id)";
	$sqlwhere .= (!empty($sqlwhere))? " AND ":" WHERE "; 
	$sqlwhere .= "(poaddr.zone_id = '" .$zone. "' OR rtaddr.zone_id = '" .$zone. "')";	
}	
if(!empty($sqlwhere)) {
	$sqlmsg .= $sqlwhere;
}
$sqlmsg .=	" GROUP BY stk.product_id";
// 	_print($sqlmsg);
$query = $db->query($sqlmsg);

$num_rows = $query->num_rows();
// 	_print($num_rows);
// 	$sqlmsg .= " LIMIT " .$offset. ", " .$limit;
$queryresult = $db->query($sqlmsg);

$results = $queryresult->result_array();

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
->setCellValue('A1', 'Barcode')
->setCellValue('B1', 'ProductName')
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

		
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $result['product_id']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $result['product_name']);
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

if (!file_exists($xlspath)) {
	mkdir($xlspath, 0777);
}

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