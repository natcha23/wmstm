<?php

$rt = $_POST['rt_id'];
$rt = "DBN255807/0012";
$now = date("Y-m-d H:i:s");
$user_id = $_SESSION['userID'];
$new_code = "401";
$add_id = "ZZ-01-21";

$qty = '25';
$barcode = "62654653";

/* Update case cancel B+ */
$condition = '';
$cond_date = " AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '{$yesterday}', 121) AND CONVERT(datetime, '{$eoftoday}', 121)) ";
// $condition =" WHERE DT.DT_PROPERTIES IN ('211','304') and DT.DT_KEY <> '2' and WL.WL_KEY in( '1','111','165') AND WL_TO.WL_CODE <> '0499'  AND DI.DI_ACTIVE=0 AND DT.DT_ENABLE='Y' ".$cond_date;

$arr_cond = array(
		"DT.DT_PROPERTIES IN ('211','304')",
		"DT.DT_KEY <> '2'",
		"WL.WL_KEY in( '1','111','165')",
		"WL_TO.WL_CODE <> '0499'",
		"DI.DI_ACTIVE=0",
		"DT.DT_ENABLE='Y'"
);

foreach ($arr_cond as $where) {
	$cond .= ($cond)?  " AND " . $where : " WHERE " . $where;
}
$condition = $cond . $cond_date;

$sql_fields = "DI.DI_KEY, DI.DI_CRE_DATE, DI.DI_REF, DI.DI_REMARK,
			TD.TRD_SH_CODE, TD.TRD_QTY, TD.TRD_UTQNAME, GDM.GOODS_ALIAS";

$sql	= "SELECT ". $sql_fields . "
						FROM  DOCINFO AS DI
						LEFT JOIN  TRANSTKH AS TH ON DI.DI_KEY = TH.TRH_DI
						INNER JOIN TRANSTKD AS TD ON TH.TRH_KEY = TD.TRD_TRH
						INNER JOIN WARELOCATION AS WL ON WL.WL_KEY = TD.TRD_WL
						INNER JOIN WARELOCATION as WL_TO on TD.TRD_TO_WL = WL_TO.WL_KEY
						INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
						INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY
						LEFT JOIN GOODSMASTER AS GDM ON TD.TRD_SH_CODE = GDM.GOODS_CODE
                      ".$condition;

$sql .= ' ORDER BY DI.DI_REF, TD.TRD_SEQ ';

$params = array();
$stmt = sqlsrv_query( $conn, $sql, $params );
if( $stmt === false) {
	die( print_r( sqlsrv_errors(), true) );
}

$i=0;
while( $result = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
	$i++;
// 	$qty = explode(".", $result['TRD_QTY']);
	$date_srv = $result['DI_CRE_DATE']->format('Y-m-d H:i:s');
}

$db->select("*")->from("outbound_rt");
$db->where(array('rt_refid' => $rt,
		'status' => 0,
		'status_cancel' => 0
));
$sql = $db->get();
$result = $sql->result_array();
if(false){
	
}

$rt_fields = array(
		'status_cancel' => 1,
		'date_cancel' => $now
);
$rt_where = "rt_refid = " . $rt;
$db->update("outbound_rt", $rt_fields, $rt_where);



$fields = array(
		"damage_add_id" => $add_id,
		"damage_qty" => $qty,
		"product_old_code" => $barcode,
		"product_new_code" => $new_code,
		"user_id" => $user_id,
		"date_create" => $now,
		"date_update" => $now,
);
if(empty($id)) {
	$db->insert("stock_damage", $fields);
} else {
	unset($fields['date_create']);
	$where = array("sd_id" => $id);
	$db->update("stock_damage1", $fields, $where);
}
// _print($db->last_query());exit;

function manageDamageProduct($items=array()) {

	$rt = $items['rt_id'];
	
	$rt = "DBN255807/0012";
	$now = date("Y-m-d H:i:s");
	$user_id = $_SESSION['userID'];
	$new_code = "401";
	$add_id = "ZZ-01-21";
	
	$fields = array(
			"damage_add_id" => $add_id,
			"damage_qty" => $items['qty'],
			"product_old_code" => $items['barcode'],
			"product_new_code" => $new_code,
			"user_id" => $user_id,
			"date_create" => $now,
			"date_update" => $now,
	);
	if(!empty($id)) {
		$db->insert("stock_damage", $fields);
	} else {
		unset($fields['date_create']);
		$where = array("sd_id" => $id);
		$db->update("stock_damage", $fields, $where);
	}
	
}
?>