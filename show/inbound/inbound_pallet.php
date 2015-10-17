<?php
$date_delivery = isset($_GET['date_delivery'])?$_GET['date_delivery']:date( "Y-m-d");
$db->distinct();
$db->select('inpo.po_id,inpo.po_delivery_date,inpo.po_create,ins.status,inpo.po_supplier');
$db->join('inbound_po inpo','ins.inbound_id = inpo.po_id');
$sqlPo = $db->get_where('inbound_status ins',array('ins.status'=>'1'));
?>

<div class="ibox-title">
    <div class="panel-options" style="padding-bottom: 10px;">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true">First Tab</a></li>
            <li class=""><a data-toggle="tab" href="#tab-2" aria-expanded="false">Second Tab</a></li>
        </ul>
    </div>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <table id="table-inbound-po" class="table table-striped table-bordered table-hover dataTable">
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
                            <a href="?page=inbound_pallet_item&date_delivery=<?php echo $date_delivery;?>&poID=<?php echo $arr['po_id'];?>&supplier_id=<?php echo $arr['po_supplier'];?>" class="btn btn-xs btn-primary">ดูรายการสินค้า</a>
                        </td>
                    </tr>
                <?php
                $i++;
                    }
                ?>
                </tbody>
            </table>
        </div>
        <div id="tab-2" class="tab-pane">

        </div>
    </div>
</div>
<script>
$(function(){
	var oTable = $('#table-inbound-po').dataTable({
		"pageLength": 50,
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $date_delivery; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var label = $('<label />').html(' วันที่ส่งของ: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_pallet&date_delivery='+search_date;
	})
	$('#table-inbound-po_filter').append(label);
	$('#table-inbound-po_filter').append(button);
})
</script>

