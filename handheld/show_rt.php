<?php

// $connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
// $conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
// sqlsrv_query("SET NAMES UTF8");

$search = isset($_REQUEST['search'])?$_REQUEST['search']:'';
$today = isset($_REQUEST['todate'])?$_REQUEST['todate']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

?>
<div><h3>นำออก</h3></div>
<form action="" method="POST">
	<!-- <input type="text" name="todate" size="15" value="<?php echo $today; ?>"/><p></p> -->
	<input type="text" name="search" size="10" value="<?php echo $search; ?>"/><br>
	<input type="text" name="todate" size="10" value="<?php echo $today; ?>"/>
	<input type="submit" value="ค้นหา"/>
	<input type="button" value="เมนู" onclick="javascript:window.location.href='?'"/>
</form>
<table id="table-outbound-po" class="table">
<thead>
	<tr>
		<th>#</th>
		<!-- <th>Date</th> -->
		<th>RT No.</th>
		<th>Action</th>
	</tr>
</thead>
<tbody>
<?php

$condition = '';
$cond_date = " AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '{$yesterday}', 121) AND CONVERT(datetime, '{$eoftoday}', 121)) ";

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

$sql_fields = " DI.DI_CRE_DATE, DI.DI_REF ";

$sql	= "SELECT ". $sql_fields . "
						FROM  DOCINFO AS DI
						LEFT JOIN  TRANSTKH AS TH ON DI.DI_KEY = TH.TRH_DI
						INNER JOIN TRANSTKD AS TD ON TH.TRH_KEY = TD.TRD_TRH
						INNER JOIN WARELOCATION AS WL ON WL.WL_KEY = TD.TRD_WL
						INNER JOIN WARELOCATION as WL_TO on TD.TRD_TO_WL = WL_TO.WL_KEY
						INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
						INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY
                      ".$condition;

$sql .= " GROUP BY DI.DI_CRE_DATE, DI.DI_REF ";
$sql .= " ORDER BY DI.DI_REF ";

// $stmt = sqlsrv_query( $conn, $sql, $params );
// if( $stmt === false) {
// 	die( '<pre>' . print_r( sqlsrv_errors(), true) . '</pre>' );
// }


$i=0;
// while( $result = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
// del //
$db->select("*, rt.rt_date AS rt_date")->from("outbound_rt AS rt");
// $db->where("rt.rt_date BETWEEN '$yesterday' AND '$eoftoday'");
$db->where("date(rt.rt_date) = '$today'");
$db->where("rt.status = '0'");

if( !empty($today) ) {
	$db->where("date(rt.rt_date) = '$today'");
}
if ( !empty($search) ) {
	$db->where("rt.rt_refid LIKE '%$search%'");
}

$db->group_by("rt.rt_refid");

$sql = $db->get();

$results = $sql->result_array();

foreach($results as $result) {
	$result['TRD_QTY'] = $result['rt_qty'];
	$result['DI_REF'] = $result['rt_refid'];
	$result['DI_CRE_DATE'] = $result['rt_date'];
// del //

	$checkstatus = array();
	$i++;
	$qty = explode(".", $result['TRD_QTY']);
// 	$di_cre_date = $result['DI_CRE_DATE']->format('Y-m-d');
	$di_cre_date = $result['DI_CRE_DATE']; // del


?>
	<tr>
		<td><?php echo $i; ?></td>
		<!-- <td><?php echo $di_cre_date; ?></td> -->
		<td><?php echo $result['DI_REF']; ?></td>
		<td>
            <?php
            /* Check Status RT *///$result['DI_REF']
			$table = "outbound_rt";
			$db->select("id")->from($table)->where(array("rt_refid" => $result['DI_REF']));
			$sql = $db->get();
			$num_RT = $sql->num_rows();

			if($num_RT > 0){
				$db->select("barcode")->from($table)->where(array("rt_refid" => $result['DI_REF']))->group_by("barcode");
				$sql = $db->get();
				$checkstatus['product_list'] = $sql->num_rows();
				
				$db->select_sum('qty_amount')->where(array("rt_refid" => $result['DI_REF']));
				$query = $db->get($table);
				$sum = $query->row();
				$checkstatus['product_qty'] = $sum->qty_amount;
			}
			
			/* Query RT status */
			$db->select("*")->from("outbound_rt_status")->where(array("rt_id" => $result['DI_REF']));
			$query = $db->get();
			$status = $query->row();
			if( !empty($status) ) {
				$checkstatus['all_product_list'] = $status->rt_product_amount;
				$checkstatus['all_product_qty'] = $status->sum_product;
				$checkstatus['status'] = $status->status;
			}
			$message = "ดำเนินการ";
			if( !empty($checkstatus['status']) && $checkstatus['status'] >= 2 ) {
				$message = "สำเร็จ";
			}
// 			if(!empty($checkstatus)) {
// 				if ($checkstatus['product_list'] == $checkstatus['all_product_list']) {
// 					if($checkstatus['product_qty'] == $checkstatus['all_product_qty']) {
// 						$message = "สำเร็จ";
// 					} else {
// 						$message = "ดำเนินการ";	
// 					}
// 				} else {
// 					if($checkstatus['product_list'] > 0) {
// 						$message = "ดำเนินการ";
// 					}
// 				}
// 			}

			if($checkstatus['product_qty'] == $checkstatus['all_product_qty']) {
				$message = "สำเร็จ";
			}
			
			if($message == "สำเร็จ") {
				echo '<span style="color: #009933; font-weight: bold;">'. $message .'</span>';
			} else {
			?>
                <a href="" id="<?php echo $result['DI_REF']; ?>" onclick="$.toItems('<?php echo $result['DI_REF']; ?>','<?php echo $today; ?>');"><?php echo $message; ?></a>
            <?php } ?>
        </td>
	</tr>
<?php
	}
?>
</tbody>
</table>
<script>
$(function(){

	$.toItems = function(refid, todate) {

		var params = {
				refid: refid,
				todate: todate
			};
		
		$.postAndRedirect('?page=rt_items', params);
	}

	$.postAndRedirect = function(url, postData)
	{
	    var postFormStr = "<form method='POST' action='" + url + "'>\n";
	    
	    for (var key in postData)
	    {
	        if (postData.hasOwnProperty(key))
	        {
	            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'/>";
	            
	        }
	    }

	    postFormStr += "</form>";
	    var formElement = $(postFormStr);
		event.returnValue=false;
	    $('body').append(formElement);
	    $(formElement).submit();
	}
	
});
</script>
