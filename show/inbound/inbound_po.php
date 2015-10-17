<?php
//$todate = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
//$start = date( "Y-m-d", strtotime( "$todate -1 day" ) ).' 12:00:00';
//$stop = $todate.' 11:59:59';
$date_search = isset($_GET['date_search'])?$_GET['date_search']:date( "Y-m-d");

$db->distinct();
$db->select('po_id,date(po_delivery_date) as booking_date,date(po_create)as po_create,po_supplier');
//$db->where(array('po_create >='=>$start,'po_create <='=>$stop));
$db->where(array('date(po_delivery_date)' => $date_search));
// $db->group_by('po_id'); // เอาออกด้วย
$sqlPo = $db->get('inbound_po');
?>
<div class="ibox-title">
    <table id="table-inbound-po" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>PO Reference No.</th>
                <th>วันที่สร้าง</th>
                <th>กำหนดวันส่ง</th>
                <th>นัดวันส่งของ</th>
                <th>บริษัท</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
            foreach($sqlPo->result_array() as $arr){
                $db->select('status');
                $sqlStatus = $db->get_where('inbound_status',array('inbound_id'=>$arr['po_id']));
                $numStatus  = $sqlStatus->row_array();
        ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $arr['po_id']; ?></td>
                <td><?php echo $arr['po_create']; ?></td>
                <td><?php echo $arr['booking_date']; ?></td>
                <td></td>
                <td><?php echo $arr['po_supplier'];?></td>

                <td>
                    <a href="?page=inbound_items_show&date_delivery=<?php echo $date_search; ?>&poID=<?php echo $arr['po_id']; ?>" class="btn btn-xs btn-primary">ดูรายการสินค้า</a>
                     <?php echo getStatus_PO($numStatus['status'])?>
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
	$('#inbound_po').addClass('active');

	var oTable = $('#table-inbound-po').dataTable({
		"pageLength": 50,
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $date_search; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var label = $('<label />').html(' วันส่งสินค้า: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_po&date_search='+search_date;
	})
	$('#table-inbound-po_filter').append(label);
	$('#table-inbound-po_filter').append(button);
})
</script>

