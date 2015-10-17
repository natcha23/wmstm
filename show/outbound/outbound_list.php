<?php
// $connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
// $conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
// sqlsrv_query("SET NAMES UTF8");

$search = isset($_GET['search'])?$_GET['search']:'';
// $today = isset($_GET['today'])?$_GET['today']:date( "Y-m-d");
$today = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
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
<div class="ibox-title">
<table id="table-outbound" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>#</th>
		<th>RT Number</th>
		<th>RT Date</th>
		<th>Action</th>
	</tr>
</thead>
<tbody>

<?php 
$condition = '';
$cond_date = " AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '{$yesterday}', 121) AND CONVERT(datetime, '{$eoftoday}', 121)) ";

$arr_cond = array(
// 		"DT.DT_PROPERTIES IN ('211','304')",
// 		211 RT
		"DT.DT_PROPERTIES IN ('211')",
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

$sql .= " GROUP BY DI.DI_CRE_DATE, DI.DI_REF";
$sql .= " ORDER BY DI.DI_REF ";

$params = array();
// $stmt = sqlsrv_query( $conn, $sql, $params );
// if( $stmt === false) {
// 	die( '<pre>' . print_r( sqlsrv_errors(), true) . '</pre>' );
// }

// dSo
$db->select("*, rt.rt_date AS rt_date")->from("outbound_rt AS rt");
if ( !empty($today) ) {
	$db->where("date(rt.rt_date) = '$today' ");
}
// $db->join("outbound_rt_status AS status", "rt.rt_refid = status.rt_id", "LEFT");
// $db->where("rt.rt_date BETWEEN '$yesterday' AND '$eoftoday' ");
$db->where(array("rt.status" => 0,
// 		"rt.rt_success" => 1,
// 		'status.status' => 1
));

if( !empty($search) ) {
	$db->where("(rt.rt_refid LIKE '%".$search."%')", NULL, FALSE);
}

$db->group_by("rt.rt_refid");
$sql = $db->get();
// _print($db->last_query());
$results = $sql->result_array();
// dSo

$i=0;
// while( $result = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
foreach($results as $result) {
	$result['TRD_QTY'] = $result['rt_qty'];
	$result['DI_CRE_DATE'] = $result['rt_date'];
	$result['DI_REF'] = $result['rt_refid'];
// 	echo date_format($date,"Y-m-d H:i:s");

	$i++;
	$qty = explode(".", $result['TRD_QTY']);
// 	$di_cre_date = convertDate($result['DI_CRE_DATE']->format('Y-m-d'));
	$di_cre_date = $result['DI_CRE_DATE']; // del So
	
	$db->select("*")->from("outbound_rt_status")->where(array("rt_id" => $result['rt_refid']));
	$query = $db->get();
	$rows = $query->row();
	$rtstatus = $rows->status;
	
	$message = "ดำเนินการ";
	$label = "label-success";
	 
	if ( $rtstatus >= 1 ) {
		$message = "สำเร็จ";
		$label = "label-primary";
	} else {
		$message = "ดำเนินการ";
		$label = "label-warning";
	}
?>
	<tr>
		<td><?php echo $i; ?></td>
		<td><?php echo $result['DI_REF']; ?><span class="label <?php echo $label; ?> pull-right"><?php echo $message; ?></span></td>
		<td><?php echo $di_cre_date ?></td>
		<td>
			<a href="?page=outbound_items&refid=<?php echo $result['DI_REF']; ?>&todate=<?php echo $today; ?>"><button class="btn btn-primary">รายละเอียด</button></a>
			<!-- <button class="btn btn-danger" onclick="$.canceloutbound('<?php echo $result['DI_REF']; ?>');">ยกเลิกเอกสาร</button> -->
		</td>
	</tr>

<?php 
}
?>
	</tbody>
</table>

</div>
<script>
$(function(){
	$('#nav-outbound').parent().addClass('active');
	$('#nav-outbound').addClass('in');
	$('#outbound_list').addClass('active');	
	
	var oTable = $('#table-outbound').dataTable({
		"pageLength": 100,
	});
	var search = $('#table-outbound_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
			var search_date = $('#search_date').val();
		 	var search_key = $('#search_key').val();
		 	window.location.href = '?page=outbound_list&finddate='+search_date+'&search='+search_key;
// 			 oTable.fnFilter( this.value );
		}
	});

	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' ,data:"test"});
	$(input).val('<?php echo $today; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd',
	 	autoclose: true
	});
	 	
	$(input).on("changeDate", function(ele) {});
	
	var label = $('<label />').html(' Date: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
	 	var search_date = $('#search_date').val();
	 	var search_key = $('#search_key').val();
	 	window.location.href = '?page=outbound_list&finddate='+search_date+'&search='+search_key;
	})
	$('#table-outbound_filter').append(label);
	$('#table-outbound_filter').append(button);

	$.canceloutbound = function(del_id) {

		event.preventDefault();

    	if (confirm('ต้องการยกเลิกเอกสารเลขที่ '+del_id+' ใช่หรือไม่') == true) {
			$.ajax({             
				type: 'post',
				url: 'handheld/?page=rt_delete',
				data: {mode: 'deletechk', rt_id: del_id},
				success: function (html) {
					location.reload();
				}
		    });
			event.preventDefault();
		    return false;
		} else {
			event.preventDefault();
		}
	}

})
</script>