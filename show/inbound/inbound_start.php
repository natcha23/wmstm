<?php
if($_GET['date_search'] && isset($_GET['date_search'])){
    $date_search = isset($_GET['date_search'])?$_GET['date_search']:date( "Y-m-d");
    $db->where('inpo.po_delivery_date',$date_search);
}
$db->distinct();
$db->select('inpo.po_id,inpo.po_delivery_date,inpo.po_create,ins.status');
$db->join('inbound_po inpo','ins.inbound_id = inpo.po_id');
$db->where('ins.status ','1');
$sqlPo = $db->get('inbound_status ins');
//echo $db->last_query();
?>
<div class="ibox-title">
    <table id="table-inbound-po" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>วันที่สร้าง</th>
                <th>กำหนดวันส่ง</th>
                <th>PO Reference No.</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
            foreach($sqlPo->result_array() as $arr){
        ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $arr['po_create']; ?></td>
                <td><?php echo $arr['po_delivery_date']; ?></td>
                <td><?php echo $arr['po_id']; ?></td>
                <td>
                    <a href="?page=inbound_items&&poID=<?php echo $arr['po_id']; ?>" class="btn btn-xs btn-primary">ดูรายการสินค้า</a>
                    <?php echo getStatus_PO($arr['status'])?>
                </td>
            </tr>
        <?php
        $i++;
            }
        ?>
        </tbody>
    </table>
</div>
<script>
$(function(){
	$('#nav-inbound').parent().addClass('active');
	$('#nav-inbound').addClass('in');
	$('#inbound_start').addClass('active');

	var oTable = $('#table-inbound-po').dataTable({
		"pageLength": 50,
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $date_search; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var label = $('<label />').html(' วันที่ส่งของ: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_start&date_search='+search_date;
	})
	$('#table-inbound-po_filter').append(label);
	$('#table-inbound-po_filter').append(button);
})
</script>

