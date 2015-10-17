<?php
$search = isset($_GET['search'])?$_GET['search']:'';
if ( empty($search) && empty($_GET['finddate']) ) {
	$_GET['finddate'] = date('Y-m-d');
}
$finddate = isset($_GET['finddate'])?$_GET['finddate']:'';
$user_id = $_SESSION['userID'];
// $finddate = '2015-07-08';
?>
<div class="ibox-title">
	<table id="table-report-transport-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>ทะเบียนรถ</th>
				<th>ชื่อผู้ขับ</th>
				<th>Delivery Order</th>
				<th>RT No.</th>
				<th>สาขาปลายทาง</th>
				<th>วันเวลา ออกรถ</th>
				<th style="display:none">วัน/เวลา ถึงปลายทาง</th>
				<th style="display:none">สถานะการขนส่ง</th>
			</tr>
		</thead>
		<tbody>
<?php 

if ( !empty($search) ) {
	$search_id = '';
	$db->select("car_id")->from("car_list")->where("car_code LIKE '%".$search."%' AND status = 0");
	$findcarsql = $db->get();
	$search_id = $findcarsql->row('car_id');
	
	$wheremessage = "car_id = '" . $search_id . "'";

	if( empty($search_id) ) {
		$db->select("user_id")->from("user")->where("status = 0");
		$db->where("(user_fname LIKE '%".$search."%' OR user_lname LIKE '%".$search."%')");
		$driversql = $db->get();
		$search_id = $driversql->row('user_id');
		
		$wheremessage = "driver_id = '" . $search_id . "'";
	}
}

$db->select("outcar.*");
$db->select("rt.shipto_name");
$db->select("sts.time_out_car, sts.time_branch_confirm, sts.delivery_order_id");
$db->from("outbound_car AS outcar");

$db->join("outbound_rt AS rt", "outcar.outbound_rt = rt.rt_refid", "LEFT");
$db->join("outbound_rt_status AS sts", "outcar.outbound_rt = sts.rt_id", "LEFT");

if ( !empty($finddate) ) {
	$db->where("DATE(outcar.date_time) = '" . $finddate . "'");
}

if ( !empty($search) ) {
	$db->where("(rt.rt_refid LIKE '%".$search."%' OR sts.delivery_order_id LIKE '%".$search."%'".
			" OR rt.shipto_name LIKE '%".$search."%' OR other_car_code LIKE '%".$search."%' OR driver_name LIKE '%".$search."%')", NULL, FALSE);
	
	if( !empty($search_id) ) {
		$db->or_where($wheremessage);
	}
}

$db->group_by("outcar.outbound_rt");


/* $db->select("outcar.*, user.user_fname, user.user_lname");
$db->select("rt.shipto_name");
$db->select("car.car_code, car.car_detail")->from("car_list AS car");
$db->select("sts.time_out_car, sts.time_branch_confirm");

$db->join("outbound_car AS outcar", "car.car_id = outcar.car_id", "INNER");
$db->join("outbound_rt AS rt", "outcar.outbound_rt = rt.rt_refid", "INNER");
$db->join("outbound_rt_status AS sts", "outcar.outbound_rt = sts.rt_id", "LEFT");
$db->join("user AS user", "outcar.driver_id = user.user_id");

$db->where("car.status = 0");
$db->where("DATE(outcar.date_time) = '" . $finddate . "'");

$db->group_by("outcar.outbound_rt"); */

$sql = $db->get();
// _print($db->last_query());

$results = $sql->result_array();
$row = 1;
foreach($results as $result) {
	if($result['car_id'] != -1) {
		$carsql = '';
		$db->select('car_code, car_detail')->from('car_list AS car')->where('car_id', $result['car_id']);
		$carsql = $db->get();
		$car_code = $carsql->row_array();
		$result['car_code'] = $car_code['car_code'];
	} else {
		$result['car_code'] = $result['other_car_code'];
	}
	$driver_name = '';
	if($result['driver_id'] != -1) {
		$db->select("user.user_fname, user.user_lname")->from("user")->where("user_id", $result['driver_id']);
		$driver_sql = $db->get();
		$driver = $driver_sql->row_array();
		$driver_name = $driver['user_fname'] . " " . $driver['user_lname'];
	} else {
		
		$driver_name = $result['driver_name'];
	}
	
	$label = "label-warning";
	$status_process = '';
	switch ($result['status_process']) {
		case 0 : $status_process = "รอรถออก";		break;
		case 1 : $status_process = "ออกรถไปสาขา";	break;
		case 2 : $status_process = "ถึงสาขา";		break;
			
		default :	break;
	}
?>		
			<tr>
				<td><?php echo $result['car_code'];?></td>
				<td><?php echo $driver_name;?></td>
				<td><?php echo $result['delivery_order_id']; ?></td>
				<td><?php echo $result['outbound_rt'];?></td>
				<td><?php echo $result['shipto_name'];?></td>
				<td><?php echo $result['date_time'];?></td>
				<td style="display:none"><?php if($result['time_branch_confirm'] != "0000-00-00 00:00:00") { echo $result['time_branch_confirm']; } else {echo "-";}?></td>
				<td style="display:none"><!-- <span class="label <?php echo $label; ?> pull-right"><?php echo $status_process; ?></span> --><?php echo $status_process;?></td>
			</tr>
<?php 
	$row++;
}
?>
		</tbody>
		<tfoot></tfoot>
	</table>
</div>
<script>
$(function() {
	$('#nav-report').parent().addClass('active');
	$('#nav-report').addClass('in');
	$('#report_transport').addClass('active');

	var oTable = $('#table-report-transport-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-transport-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).attr('id', 'search_key');
	$(search).focus();

	$("div.dataTables_filter input").unbind();
	$("div.dataTables_filter input").keyup( function (e) {
		if ( e.keyCode == 13) {
		 	var search_key = $('#search_key').val();
		 	var search_date = $('#search_date').val();
		 	window.location.href = '?page=report_transport&finddate='+search_date+'&search='+search_key;
		}
	});

	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $finddate; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd',
		autoclose: true
	});
	
	var label = $('<label />').html(' วันที่ออกรถ : ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
		var search_key = $('#search_key').val();
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=report_transport&finddate='+search_date+'&search='+search_key;
	})
	$('#table-report-transport-list_filter').append(label);
	$('#table-report-transport-list_filter').append(button);

	var fa_excel = $('<i />', { class: 'fa fa-file-excel-o' });
	var btnXLS = $('<button />',{ class: 'btn btn-sm btn-primary' }).css({ 'margin-left':'3px' });
		$(btnXLS).attr('data-toggle', 'tooltip');
		$(btnXLS).attr('data-placement', 'top');
		$(btnXLS).attr('title', 'Export to Excel');
		$(btnXLS).append(fa_excel);
		$(btnXLS).click(function(e){ 
		 	var search_date = $('#search_date').val();
		 	var search_key = $('#table-report-transport-list_filter').find('input[type=search]').val();
			location.href = '<?php echo _BASE_URL_;?>show/report/export_transport.php?finddate='+search_date+'&search='+search_key;
		});
	$('#table-report-transport-list_filter').append(btnXLS);
});
</script>