<?php
require_once('config.php');
require_once('class_my.php');
require_once('func.php');

$db = DB();

$finddate 	= isset($_GET['finddate'])?$_GET['finddate']:'';
$zone 		= isset($_GET['zone'])?$_GET['zone']:'0';
$user_id	= $_SESSION['userID'];
$search 	= isset($_GET['search'])?$_GET['search']:'';
if(empty($search) && empty($_GET['finddate']) && empty($zone) ) {
	$_GET['finddate'] = date('Y-m-d');
}


$db->select('zone_id, zone_name')->from('tb_zone');
$db->where(array('status' => 0));
$zoneSQL = $db->get();
$zoneArr = $zoneSQL->result_array();

$limit = 100;
$offset = 0;
/* PO step */
$sqlwhere = '';
// 	$sqlmsg = "SELECT stk.product_id, stk.product_name, stk.product_qty, rtloc.location_id, ".
$sqlmsg = "SELECT stk.product_id, stk.product_name, ".
			
		" SUM(CASE WHEN posts.status = '1' THEN poloc.qty_remain ELSE 0 END) inbound_products, ".
		" SUM(CASE WHEN posts.status = '2' THEN poloc.qty_remain ELSE 0 END) store_products, ".

		" SUM(CASE WHEN rtsts.status = '1' THEN rtloc.qty ELSE 0 END) launch_products, ".
		" SUM(CASE WHEN rtsts.status = '2' THEN rtloc.qty ELSE 0 END) pickup_products, ".
		" SUM(CASE WHEN rtsts.status = '3' THEN rtloc.qty ELSE 0 END) checkingout_products, ".
		" SUM(CASE WHEN rtsts.status = '4' THEN rtloc.qty ELSE 0 END) choosecar_products, ".
		" SUM(CASE WHEN rtsts.status = '5' THEN rtloc.qty ELSE 0 END) transport_products ".
		// 				" SUM(CASE WHEN rtsts.status = '7' THEN rtloc.qty ELSE 0 END) tobranch_products" .

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
// 	_print($num_rows);exit;
// 	$sqlmsg .= " LIMIT " .$offset. ", " .$limit;.
/*
 * Paging
*/
_print($_GET);
_print($_POST);
$sLimit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
{
	$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
			intval( $_GET['iDisplayLength'] );
}


$queryresult = $db->query($sqlmsg);

$results = $queryresult->result_array();
// $datarows = json_encode($results);
// echo $datarows;

$aoColumns = array('product_id',
			'product_name',
			'inbound_products',
			'store_products',
			'launch_products',
			'pickup_products',
			'checkingout_products',
			'choosecar_products',
			'transport_products');

_print(json_encode($aoColumns));

/**
 * Output
 */
$output = array(
		"sEcho"                => intval($input['sEcho']),
		"iTotalRecords"        => $num_rows,
		"iTotalDisplayRecords" => $num_rows,
		"aaData"               => $results,
		"aoColumns" 			=> json_encode($aoColumns) 
);


echo json_encode( $output );


?>