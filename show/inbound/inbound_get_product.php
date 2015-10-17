<?php
//SELECT * FROM TB WHERE CONVERT(DATE,booking_date)='2015-09-22' OR ((booking_date IS NULL OR booking_date='') AND CONVERT(DATE,po_delivery_date)='2015-09-21')
$date_delivery = isset($_GET['date_delivery'])?$_GET['date_delivery']:date( "Y-m-d");
//$db->distinct();
$db->select('inpo.po_id,ib.booking_date,inpo.po_delivery_date,date(inpo.po_create)as po_create,ins.status,inpo.po_supplier,inpo.ibp_id');
$db->join('inbound_po inpo','ins.inbound_id = inpo.po_id');
$db->join('inbound_booking ib','ib.po_id = ins.inbound_id','left');
$db->where('inpo.ibp_id !=','');
$db->where('ins.status !=',2);
$db->where('date(ib.booking_date)',$date_delivery);
$whereDelivery = "((date(ib.booking_date) IS NULL OR date(ib.booking_date) = '')AND date(inpo.po_delivery_date) = '".$date_delivery."')";
$db->or_where($whereDelivery);
$db->group_by('ins.inbound_id');
$db->group_by('inpo.ibp_id');

//$order = "ORDER BY CASE WHEN ib.booking_date IS NULL Then 1 Else 0 End,ib.booking_date";
//$db->query($order);
$sqlPo = $db->get('inbound_status ins');
//echo $db->last_query();
?>

<div class="ibox-title">
    <div class="tab-content">
        <table id="table-inbound-po" class="table table-striped table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>วันที่สร้าง</th>
                    <th>กำหนดวันส่ง</th>
                    <th>PO Reference No.</th>
                    <th> IBP NO </th>
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
                    <td><?php if($arr['booking_date'] != NULL){echo $arr['booking_date'];}else{echo $arr['po_delivery_date'];} ?></td>
                    <td><?php echo $arr['po_id']; ?></td>
                    <td><?php echo $arr['ibp_id']; ?></td>
                    <td>
                        <?php if($arr['status'] == 1 && $arr['ibp_id'] != ''){?>
                        <a href="?page=inbound_pallet_item&date_delivery=<?php echo $date_delivery;?>&poID=<?php echo $arr['po_id'];?>&supplier_id=<?php echo $arr['po_supplier'];?>" class="btn btn-xs btn-primary">จัดพาเลท</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php
            $i++;
                }
            ?>
            </tbody>
        </table>
    </div>
</div>
<script>
$(function(){
	$('#nav-inbound').parent().addClass('active');
	$('#nav-inbound').addClass('in');
	$('#inbound_get_product').addClass('active');

    $( document ).ready(function() {
        // sync IBP by date
        $.post('show/inbound/get_import_ibp.php',{'method':'dateImport','dataGet':'<?php echo date( "Ymd");?>'});
    });

	var oTable = $('#table-inbound-po').dataTable({
		"pageLength": 50,
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $date_delivery; ?>'
            );
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var label = $('<label />').html(' วันที่ส่งของ: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_get_product&date_delivery='+search_date;
	})
	$('#table-inbound-po_filter').append(label);
	$('#table-inbound-po_filter').append(button);
})
</script>

