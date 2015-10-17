<?php
$connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
sqlsrv_query("SET NAMES UTF8");

$today = isset($_GET['today'])?$_GET['today']:date( "Y-m-d");
// echo $today;exit;
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

function convertDate($date=null) {
	if(!empty($date)) {
		$data = explode(" ", $date);
		
		$_date = explode("-", $data[0]);
		$thYear = $_date[0] + 543;
		if(count($data) > 1) {
			$_result = $_date[2] . "/" . $_date[1] . "/" . $thYear . " " . $data[1];
		} else {
			$_result = $_date[2] . "/" . $_date[1] . "/" . $thYear;
		}
		return $_result;
	} else {
		return;
	}
}

?>

<table id="table-inbound" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th><input type="checkbox" id="chk_all" onchange="$.checkAll('#chk_all','chk_list[]')"></th>
		<th>RT Number</th>
		<th>RT Date</th>
		<th>Barcode</th>
		<th>Goods Name</th>
		<th>Quantity</th>
		<th>Unit</th>
	</tr>
</thead>
<tbody>

<?php 
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
	$qty = explode(".", $result['TRD_QTY']);
?>

	<tr>
		<td><input type="checkbox" name="chk_list[]" value="<?php echo $result['AP_CODE']; ?>_<?php echo $result['TRD_SH_CODE']; ?>"></td>
		<td><?php echo $result['DI_REF']; ?></td>
		<td><?php echo convertDate($result['DI_CRE_DATE']->format('Y-m-d H:i:s')); ?></td>
		<td><?php echo $result['TRD_SH_CODE']; ?></td>
		<td><?php echo $result['GOODS_ALIAS']; ?></td>
		<td><?php echo number_format($qty[0]); ?></td>
		<td><?php echo $result['TRD_UTQNAME']; ?></td>
	</tr>

<?php 
}
?>
	</tbody>
</table>

<script>
$(function(){
	var oTable = $('#table-inbound').dataTable({
		"pageLength": 100,
	});
	var search = $('#table-inbound_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $today; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});
	
	var label = $('<label />').html(' Date: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=outbound_allitems&today='+search_date;
	})
	$('#table-inbound_filter').append(label);
	$('#table-inbound_filter').append(button);
})
</script>