<?php

$inputFileName = _DOC_ROOT_."upload/REPORT_LOCATION.xls";

/** PHPExcel_IOFactory - Reader */
include _DOC_ROOT_.'lib/ClassesPHPExel/PHPExcel/IOFactory.php';

$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($inputFileName);

$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
$highestRow = $objWorksheet->getHighestRow();
$highestColumn = $objWorksheet->getHighestColumn();
$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
$headingsArray = $headingsArray[1];

$r = -1;
$dataArray = array();
for ($row = 3; $row <= $highestRow; ++$row) {
	$dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
	if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
		++$r;
		foreach($headingsArray as $columnKey => $columnHeading) {
			// 			mb_detect_encoding($tmp)=="UTF-8" // ตรวจสอบการเข้ารหัส
			$dataRow[$row][$columnKey] = utf8_decode($dataRow[$row][$columnKey]);
			$dataArray[$r][$columnHeading] = iconv( 'TIS-620', 'UTF-8', $dataRow[$row][$columnKey]);
		}
	}
}
