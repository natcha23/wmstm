<?php
$connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
sqlsrv_query("SET NAMES UTF8");

$search = isset($_GET['search'])?$_GET['search']:'';
$todate = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
$start = date( "Y-m-d", strtotime( "$todate -1 day" ) ).' 12:00:00';
$stop = $todate.' 11:59:59';
?>

<table id="table-inbound" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>#</th>
		<th>วันที่กำหนดส่งสินค้า</th>
		<th>PO Reference No.</th>
		<th>Receipt Type</th>
		<th>Supplier No.</th>
		<!-- <th>Supplier name</th> -->
		<th>Barcode</th>
		<th>Name</th>
		<th>Amount</th>
		<th>Unit</th>
		<th>Location</th>
		<th style="width:200px">วันหมดอายุ</th>
	</tr>
</thead>
<tbody>

<?php
// $sql = "SELECT TH.TRH_SHIP_DATE, DI.DI_REF, WL.WL_CODE, TD.TRD_SEQ, TD.TRD_SH_CODE, TD.TRD_QTY, TD.TRD_SH_NAME, TD.TRD_UTQNAME, AP.AP_CODE, AP.AP_NAME, DT.DT_PROPERTIES
// FROM DOCINFO AS DI
// INNER JOIN TRANSTKH AS TH ON TH.TRH_DI = DI.DI_KEY
// INNER JOIN TRANSTKD AS TD ON TD.TRD_TRH = TH.TRH_KEY
// LEFT JOIN WARELOCATION AS WL ON WL.WL_KEY = TD.TRD_WL
// INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
// INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY
// LEFT JOIN APPO AS AO ON AO.APPO_DI = DI.DI_KEY
// LEFT JOIN APFILE AS AP ON AP.AP_KEY = AO.APPO_AP
// WHERE DI.DI_CRE_DATE<? AND DI.DI_CRE_DATE>? AND DI.DI_DT IN (?,?) AND WL.WL_CODE IN ( ?,?,? )
// ORDER BY DI.DI_REF ASC, TD.TRD_SEQ";
// $params = array( $stop, $start, '1183', '1184' , '0401' , '0402' , '0403');
$sql = "SELECT DI.DI_KEY, DI.DI_REF, DI.DI_DATE, DI.DI_REMARK, APF.AP_CODE, APF.AP_NAME, TH.TRH_SHIP_DATE, TH.TRH_KEY, TD.TRD_KEY, TD.TRD_SEQ, TD.TRD_U_PRC,
TD.TRD_SH_CODE, TD.TRD_SH_NAME, TD.TRD_G_KEYIN, TD.TRD_QTY, TD.TRD_Q_FREE, TD.TRD_UTQQTY, TD.TRD_UTQNAME, TD.TRD_WL, TD.TRD_TO_WL, WL.WL_CODE, WL.WL_NAME,
WH.WH_CODE, WH.WH_NAME, DT.DT_PROPERTIES, DT.DT_PREFIX, DT.DT_ENABLE, DT.DT_DOCCODE, TD.TRD_REFER_DATE,TD.TRD_SH_NAME, DI.DI_CRE_DATE,
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
ORDER BY DI.DI_REF, TD.TRD_SEQ
";

$query = sqlsrv_query( $conn, $sql, $params, $options ) or die (sqlsrv_errors());
$row_data = 0;

$po = '';
$count_po = 0;
while($result = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)){ $row_data++;
	$qty = explode('.',$result['TRD_QTY']);
	$DT_PROPERTIES	= $result['DT_PROPERTIES'];
	if($po!=$result['DI_REF']){
		$po = $result['DI_REF'];
		$count_po++;
	}
	$AP_CODE = $result['AP_CODE'];
	if($DT_PROPERTIES=='211'){//211 RT
		if($result['WL_CODE']=='0402'){
			$DT_DOCCODE = 'THRV';
		}elseif($result['WL_CODE']=='0403'){
			$DT_DOCCODE = 'THRS';
		}elseif($result['WL_CODE']=='0499'){
			$DT_DOCCODE = 'THRO';
		}elseif($result['WL_CODE']!='0402' and $result['WL_CODE']!='0403'){
			$DT_DOCCODE = 'THRO';
		}
		$AP_CODE = $result['WL_CODE'];
	}else{//203 OR ORV ORN
		if($result['WL_CODE']=='0401'){
			$DT_DOCCODE = 'THPO';
		}
	}

	$query_ib = mysql_query("SELECT * FROM inbound_po WHERE po_id='".$result['DI_REF']."' && po_create='".retrunDateToDB($result['DI_CRE_DATE'])."' && po_supplier='".$AP_CODE."' && product_no='".$result['TRD_SH_CODE']."' ");
	$row_ib = mysql_num_rows($query_ib);

	$product_fefo = '';
	$product_fefo_date = '';
	if($row_ib==0){
		mysql_query("INSERT INTO inbound_po (po_id,po_create,po_delivery_date,po_supplier,product_no,product_name,product_qty,product_unit)
			VALUES ('". $result['DI_REF'] ."','". retrunDateToDB($result['DI_CRE_DATE']) ."','".retrunDateToDB($result['TRH_SHIP_DATE'])."','". $AP_CODE ."','". $result['TRD_SH_CODE'] ."','". $result['TRD_SH_NAME'] ."','". $qty[0] ."','". $result['TRD_UTQNAME'] ."') ");
		$row_id = mysql_insert_id();
	}else{
		$row_ib = mysql_fetch_array($query_ib);
		$row_id = $row_ib['inbound_id'];
		$product_fefo = $row_ib['product_fefo'];
		$product_fefo_date = $row_ib['product_fefo_date'];
	}
?>
	<tr>
		<td><?php echo $row_data; ?></td>
		<td><?php echo retrunDate($result['TRH_SHIP_DATE']); ?></td>
		<td><?php echo $result['DI_REF']; ?></td>
		<td><?php echo $DT_DOCCODE; ?></td>
		<td><?php echo $AP_CODE; ?></td>
		<!-- <td><?php echo $result['AP_NAME']; ?></td> -->
		<td style="color:red;font-weight:bold"><?php echo $result['TRD_SH_CODE']; ?></td>
		<td><?php echo $result['TRD_SH_NAME']; ?></td>
		<td style="color:red;font-weight:bold;text-align:right"><?php echo number_format($qty[0]); ?></td>
		<td><?php echo $result['TRD_UTQNAME']; ?></td>
		<td>

		</td>
		<td>
			<input id="chk_<?php echo $row_id; ?>" type="checkbox" value="<?php echo $row_id; ?>" <?php if($product_fefo==1){ ?> checked <?php } ?> onChange="$.checkFefo(this)">
			<span id="div_fefo_<?php echo $row_id; ?>" <?php if($product_fefo==0){ echo 'style="display:none"'; } ?>>
				<input type="text" id="date_fefo_<?php echo $row_id; ?>" class="fefo_datepicker" style="width:80px" data-mask="9999-99-99" value="<?php echo $product_fefo_date; ?>">
				<button id="bS_<?php echo $row_id; ?>" <?php if($product_fefo==1){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-success" onclick="$.updateFefo(<?php echo $row_id; ?>)"><i class="fa fa-floppy-o"></i></button>
				<button id="bE_<?php echo $row_id; ?>" <?php if($product_fefo==0){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-warning" onclick="$.updateFefo(<?php echo $row_id; ?>)"><i class="fa fa-pencil-square-o"></i></button>
				<button id="bD_<?php echo $row_id; ?>" <?php if($product_fefo==0){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-danger" onclick="$.removeFefo(<?php echo $row_id; ?>)"><i class="fa fa-times"></i></button>
			</span>
		</td>
	</tr>
<?php
}
?>

</tbody>
</table>
<?php // echo $count_po; ?>
<script>
$(function(){
	var oTable = $('#table-inbound').dataTable({
		"pageLength": 100,
	});
	$('.fefo_datepicker').datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var search = $('#table-inbound_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

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
	 	window.location.href = '?page=inbound_items2&todate='+search_date;
	})
	$('#table-inbound_filter').append(label);
	$('#table-inbound_filter').append(button);

	$.checkFefo = function(obj){
		var id = obj.value;
		if($(obj).is(':checked')){
			$('#div_fefo_'+id).show();
		}else{
			$('#div_fefo_'+id).hide();
		}
	}

	$.updateFefo = function(id){
		var date_fefo = $('#date_fefo_'+id).val();
		if(date_fefo!=''){
			$.post('show/inbound/inbound_action.php',{ method:'update_fefo', id:id, date_fefo:date_fefo },function(rs) {
				console.log(rs);
			})
		}
	}

	$.removeFefo = function(id){
		if(confirm("ลบวันหมดอายุ")==true){
			$('#chk_'+id).prop('checked', false);
			$('#div_fefo_'+id).hide();
			$('#date_fefo_'+id).val('');
			$('#bS_'+id).show();
			$('#bE_'+id).hide();
			$('#bD_'+id).hide();
		}
	}
})
</script>