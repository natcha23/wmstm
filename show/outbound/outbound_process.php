<?php

$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$db->select("*, sts.status AS status")->from("outbound_rt_status AS sts");
$db->join("outbound_rt AS rt", "sts.rt_id = rt.rt_refid", "LEFT");
$db->where(array("rt.status" => 0));
$db->where("sts.rt_date BETWEEN '" .$yesterday. "' AND '" .$eoftoday. "'");

$db->group_by("rt.rt_refid");
$sql = $db->get();
$results = $sql->result_array();
// $sql = $db->get_where('outbound_rt_status',array('status !=' => 0));

?>
<div class="ibox-title">
    <table border="1" id="table-outbound" class="table table-striped table-bordered table-hover">
	<thead>
        <tr>
            <th>#</th>
			<th>วันที่</th>
            <th>RT</th>
            <th>ปลายทาง</th>
			<th>สถานะ</th>
        </tr>
		</thead>
		<tbody>
        <?php
		$n = 1;
        foreach($sql->result_array() as $rs){
        ?>
        <tr>
            <td><?php echo $n?></td>
            <td><?php echo $rs['rt_date']?></td>
            <td><?php echo $rs['rt_id']?></td>
            <td><?php echo $rs['shipto_name']?></td>
            <td><?php echo NameOutboundStatus($rs['status']);?></td>
        </tr>
        <?php $n++; } ?>
		</tbody>
    </table>
</div>
<script>
$(function(){

	$('#nav-outbound').parent().addClass('active');
	$('#nav-outbound').addClass('in');
	$('#outbound_process').addClass('active');
	
	var oTable = $('#table-outbound').dataTable({
		"pageLength": 50,
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $today; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd',
		autoclose: true
	});

	var label = $('<label />').html(' วันที่: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=outbound_process&finddate='+search_date;
	})
	$('#table-outbound_filter').append(label);
	$('#table-outbound_filter').append(button);
})
</script>