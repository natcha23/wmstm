<?php 
require_once('config.php');

// if($_SERVER['HTTP_HOST'] == "http://eoffice1.com/"){
// 	define('_DB_HOST_','localhost');
// 	define('_DB_USER_','root');
// 	define('_DB_PASS_','eoffice0841606322');
// 	define('_DB_DATA_','thaimart');
// 	define('_DB_TYPE_','mysql');
// }else{
// 	define('_DB_HOST_','192.168.1.111');
// 	define('_DB_USER_','root');
// 	define('_DB_PASS_','eoffice0841606322');
// 	define('_DB_DATA_','thaimart');
// 	define('_DB_TYPE_','mysql');
// }

// /*** MS Database Connect ***/
// define('_MS_HOST_','tmintranet.dyndns.org, 1434');
// define('_MS_USER_','bplusbase');
// define('_MS_PWD_','#58255825$');
// define('_MS_DBNAME_','TMC_LVII');

// $DOC_ROOT = $_SERVER['DOCUMENT_ROOT']."/www/thaimart/";
// $BASE_URL = "http://192.168.1.111/thaimart/";

// define('_DOC_ROOT_',$_SERVER['DOCUMENT_ROOT'].'/thaimart/');
// define('_BASE_URL_','http://'.$_SERVER['HTTP_HOST'].'/thaimart/');

// //CI Library
// $system_path = $_SERVER['DOCUMENT_ROOT'].'/thaimart/lib/';
// $system_path = rtrim($system_path, '/').'/';
// define('BASEPATH', str_replace("\\", "/", $system_path));
// $root_path = realpath('.').'/';
// $root_path = rtrim($root_path, '/').'/';
// define('ROOTPATH', str_replace("\\", "/", $root_path));

// define('_COOKIE_NAME_',md5($_SERVER['HTTP_HOST']._DB_DATA_));




$date = new DateTime();
// echo $date->getTimestamp();

// require_once('class_my.php');
// require_once('func.php');
/** PHPExcel */
// require_once (_DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel.php');
// echo _BASE_URL_.'lib/ClassesPHPExcel/PHPExcel/IOFactory.php';exit;
/** PHPExcel_IOFactory - Reader */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

// $inputFileName = _DOC_ROOT_."upload/myDatacsv.csv";
$inputFileName = _DOC_ROOT_."upload/myData2.xls";

$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($inputFileName);

$writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
$writer->save(_DOC_ROOT_."upload/myData".$date->getTimestamp().".csv");

/*
 // for No header
 $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
 $highestRow = $objWorksheet->getHighestRow();
 $highestColumn = $objWorksheet->getHighestColumn();

 $r = -1;
 $namedDataArray = array();
 for ($row = 1; $row <= $highestRow; ++$row) {
 $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
 if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
 ++$r;
 $namedDataArray[$r] = $dataRow[$row];
 }
 }
*/

$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
$highestRow = $objWorksheet->getHighestRow();
$highestColumn = $objWorksheet->getHighestColumn();
// iconv( 'TIS-620', 'UTF-8', "สวัสดีครับ Mindphp.com");
$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
$headingsArray = $headingsArray[1];

$r = -1;
$dataArray = array();
for ($row = 2; $row <= $highestRow; ++$row) {
	$dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
	if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
		++$r;
		foreach($headingsArray as $columnKey => $columnHeading) {
				
			// 			$dataArr = iconv("UTF-8","TIS-620",$dataArr);
// 			$temp = utf8_encode($dataRow[$row][$columnKey]);
// 			$dataArray[$r][$columnHeading] = $temp;
		echo $columnHeading . ' -> ' . mb_detect_encoding($dataRow[$row][$columnKey], mb_detect_order()).'</br>';
			$dataArrayutf[$r][$columnHeading] = iconv(mb_detect_encoding($dataRow[$row][$columnKey], mb_detect_order(), true), "UTF-8", $dataRow[$row][$columnKey]);;
// 			$dataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
// 			$dataArray[$r][$columnHeading] = mb_convert_encoding($dataRow[$row][$columnKey], "UTF-8", "TIS-620");
		}
	}
}

// echo '<pre>';
// var_dump($dataArray);
// echo '<pre>'. print_r($dataArrayutf,1). '</pre>';
// echo '<pre>'. print_r($dataArray,1). '</pre>';
// echo '</pre><hr />';
exit;
?>