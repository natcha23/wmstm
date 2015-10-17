<?php 

$finddate = (isset($_GET['finddate']))? $_GET['finddate']:date("Y-m-d");
$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['today'])?$_GET['today']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 12:00:00';
$eoftoday = $finddate.' 11:59:59';
// echo '<pre>' . print_r($_REQUEST, 1) . '</pre>';
$db->distinct();
$db->select("book.book_id, book.booking_date, book.delivery_status, po.inbound_id, po.po_id, po.po_delivery_date, po.po_create, po.po_supplier,status.status AS inbound_status");
$db->from("inbound_po AS po");
$db->join("inbound_status AS status", "po.po_id = status.inbound_id", "LEFT");
$db->join("inbound_booking AS book", "po.po_id = book.po_id", "LEFT");
$db->where("DATE(book.booking_date) = '$finddate'");
// $db->where("po.po_create between '$yesterday' AND '$eoftoday'");
$db->where(array("book.booking_status" => 0));
$db->group_by("po.po_id")->order_by("book.booking_date ASC");
$sql = $db->get();

?>

<div class="ibox-title">
<table id="table-booking-list" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>PO Reference No.</th>
                <th>วันที่สร้าง</th>
                <th>กำหนดวันส่ง</th>
                <th>นัดวันส่งของ</th>
                <th>สถานะรับสินค้า</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        	$loop = 1;
            foreach($sql->result_array() as $row){
            	if(empty($row['inbound_status']) ) {
            		$row['inbound_status'] = -1;
            	}
            	switch ($row['inbound_status']) {
            		case -1:
            			$msgstatus = "รอรับสินค้า";
            			break;
            		case 1:
            			$msgstatus = "รับสินค้าแล้ว";
            			break;
            		default:
            			break;
            	}
            	
            	
		?>
        	<tr>    	
            	<td><?php echo $loop; ?></td>
            	<td><?php echo $row['po_supplier']; ?></td>
            	<td><?php echo $row['po_id']; ?></td>
            	<td><?php echo $row['po_create']; ?></td>
            	<td><?php echo $row['po_delivery_date']; ?></td>
            	<td><?php echo $row['booking_date']; ?></td>
            	<td><?php echo $msgstatus; ?></td>
            	<!-- 
            	<td><input type="checkbox" <?php if($row['delivery_status'] == 0) { ?> checked <?php } ?> id="delivery_<?php echo $row['inbound_id']; ?>" onclick="$.handleClick('<?php echo $row['inbound_id']; ?>', this);"></td>
            	 -->
            	<input type="hidden" id="bookid_<?php echo $row['inbound_id'];?>" value="<?php echo $row['book_id']; ?>">
            </tr>
		<?php
				$loop++;
            }
		?>
        

</div>



<script>
$(function(){
	$('#nav-booking').parent().addClass('active');
	$('#nav-booking').addClass('in');
	$('#booking_list').addClass('active');	
	
	var oTable = $('#table-booking-list').dataTable({
		"pageLength": 50
		
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $finddate; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd',
	 	autoclose: true
	});

	var label = $('<label />').html(' วันที่: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=booking_list&finddate='+search_date;
	})
	$('#table-booking-list_filter').append(label);
	$('#table-booking-list_filter').append(button);

	$.handleClick = function(id, cb) {
// 		alert("Clicked, new value = " + cb.checked);
		var chkstatus = 0;
		if(!cb.checked) {
			chkstatus = 1;
		}
		var book = $('#bookid_'+id).val();
		var data = new Array();
		data.push({
			book_id: book,
            id: id,
            checked: chkstatus
        });
		if(book!=''){
			$.post('show/booking/booking_action.php',{ method:'delivery_update', data },function(rs) {
				if(rs!="") {
					$('#bookid_'+id).val(rs);
				}
			})
		}
	}

	
})

</script>