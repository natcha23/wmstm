<?php
$connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
sqlsrv_query("SET NAMES UTF8");

$todate = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
$start = date( "Y-m-d", strtotime( "$todate -1 day" ) ).' 12:00:00';
$stop = $todate.' 11:59:59';

$sql = "SELECT DI.DI_KEY, DI.DI_REF, DI.DI_DATE, DI.DI_REMARK, APF.AP_CODE, APF.AP_NAME, TH.TRH_SHIP_DATE, TH.TRH_KEY, TD.TRD_KEY, TD.TRD_SEQ, TD.TRD_U_PRC,
TD.TRD_SH_CODE, TD.TRD_SH_NAME, TD.TRD_G_KEYIN, TD.TRD_QTY, TD.TRD_Q_FREE, TD.TRD_UTQQTY, TD.TRD_UTQNAME, TD.TRD_WL, TD.TRD_TO_WL, WL.WL_CODE, WL.WL_NAME,
WH.WH_CODE, WH.WH_NAME, DT.DT_PROPERTIES, DT.DT_PREFIX, DT.DT_ENABLE, DT.DT_DOCCODE, TD.TRD_REFER_DATE,TD.TRD_SH_NAME,
	(SELECT MAX(ISNULL(INB_ROUND,0)) AS MAX_PRINTORDER FROM INBOUND GROUP BY INB_DOCDATE
	HAVING (INB_DOCDATE = CONVERT(DATETIME, '". $stop ."', 102))) AS PrintNum
FROM APFILE AS APF
INNER JOIN APPO ON APF.AP_KEY = APPO.APPO_AP
RIGHT OUTER JOIN WARELOCATION AS WL
INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
INNER JOIN DOCINFO AS DI
LEFT OUTER JOIN TRANSTKH AS TH ON DI.DI_KEY = TH.TRH_DI
INNER JOIN TRANSTKD AS TD ON TH.TRH_KEY = TD.TRD_TRH ON WL.WL_KEY = TD.TRD_TO_WL
INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY ON dbo.APPO.APPO_DI = DI.DI_KEY
WHERE WL.WL_CODE IN ('0401','0402','0403') AND ( DT.DT_PROPERTIES IN ('211') AND DI.DI_ACTIVE='0' AND DT.DT_ENABLE='Y' AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '". $start ."', 121) AND CONVERT(datetime, '". $stop ."', 121)) )
OR ( DT.DT_PROPERTIES = '203' AND DT.DT_KEY IN ('1183', '1184') AND DI.DI_ACTIVE='0' AND DT.DT_ENABLE='Y' AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '". $start ."', 121) AND CONVERT(datetime, '". $stop ."', 121)) ) and WL.WL_CODE IN ('0401','0402','0403')
ORDER BY DI.DI_REF, TD.TRD_SEQ";
$params = array();
$query = sqlsrv_query( $conn, $sql, $params ) or die (sqlsrv_errors());
$row = sqlsrv_num_rows($query);
$arr_po = array();
while($result = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)){
	if(!in_array($result['DI_REF'],$arr_po)){
		$arr_po[] = $result['DI_REF'];
	}
}
?>

<table id="table-inbound-po" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>#</th>
		<th>Date</th>
		<th>PO Reference No.</th>
		<th>Action</th>
	</tr>
</thead>
<tbody>
<?php
	foreach($arr_po as $i => $arr){
?>
	<tr>
		<td><?php echo $i+1; ?></td>
		<td><?php echo $todate; ?></td>
		<td><?php echo $arr; ?></td>
		<td><a href="?page=inbound_items&todate=<?php echo $todate; ?>&search=<?php echo $arr; ?>" class="btn btn-xs btn-primary">ดูรายการสินค้า</a></td>
	</tr>
<?php
	}
?>
</tbody>
</table>

<script>
$(function(){
	var oTable = $('#table-inbound-po').dataTable({
		"pageLength": 100,
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $todate; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var label = $('<label />').html(' Date: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_po&todate='+search_date;
	})
	$('#table-inbound-po_filter').append(label);
	$('#table-inbound-po_filter').append(button);
})
</script>

