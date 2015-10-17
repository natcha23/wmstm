<?php 

$finddate = (isset($_GET['finddate']))? $_GET['finddate']:date("Y-m-d");
$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['today'])?$_GET['today']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 12:00:00';
$eoftoday = $finddate.' 11:59:59';

$db->distinct();
$db->select("book.delivery_status, book.book_id, book.booking_date, status.status AS inbound_status");
$db->select("po.inbound_id, po.po_id, po.po_delivery_date, po.po_create, po.po_supplier, po.product_date_in");
$db->from("inbound_po AS po");
$db->join("inbound_status AS status", "po.po_id = status.inbound_id", "LEFT");
$db->join("inbound_booking AS book", "po.po_id = book.po_id", "LEFT");
$db->where("po.po_create BETWEEN '$yesterday' AND '$eoftoday'");
// $db->where(array("book.booking_status" => 0));
$db->group_by("po.po_id")->order_by("book.booking_date ASC");
$sql = $db->get();

?>

<div class="ibox-title">
<table id="table-report-perform-list" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>PO Reference No.</th>
                <th>วันที่สร้าง PO</th>
                <th>กำหนดวันส่ง</th>
                <th>วันนัดส่งของ</th>
                <th>วันที่รับสินค้า</th>
                <th>Performance</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        	$loop = 1;
            foreach($sql->result_array() as $row) {
            	
            	if( empty($row['product_date_in']) || $row['product_date_in'] == "0000-00-00 00:00:00" ) {
            		$product_date_in = date("Y-m-d");
            	} else {
            		$product_date_in = $row['product_date_in'];
            	}
            	
            	if( empty($row['booking_date']) || $row['booking_date'] == "0000-00-00 00:00:00" ) {
            		$booking_date = $row['po_delivery_date'];
            	} else {
            		$booking_date = $row['booking_date'];
            	}
            	
            	$bookingDate = date("Y-m-d", strtotime($booking_date));
            	$receiveDate = date("Y-m-d", strtotime($product_date_in));
            	
            	$book = explode("-", $bookingDate);
            	$receive = explode("-", $receiveDate);
            	
            	$date1 = mktime(0,0,0,$book[1],$book[2],$book[0]); //15 กันยายน 2540
            	$date2 = mktime(0,0,0,$receive[1],$receive[2],$receive[0]); //1 พฤศจิกายน 2550
            	//หาผลต่าง
            	$diff = $date1-$date2;
            	//ทำการแปลงจากผลต่างเป็นวินาทีเป็นระยะเวลา
            	$Days = floor($diff / 86400);
//             	$Hour = floor(($diff - ($Days * 86400)) / 3600);
//             	$Minute = floor(($diff - (($Days * 86400) + ($Hour * 3600))) / 60);
//             	$Second = floor(($diff - (($Days * 86400) + ($Hour * 3600) + ($Minute * 60))));

		?>
        	<tr>    	
            	<td><?php echo $loop; ?></td>
            	<td><?php echo $row['po_supplier']; ?></td>
            	<td><?php echo $row['po_id']; ?></td>
            	<td><?php echo $row['po_create']; ?></td>
            	<td><?php echo $row['po_delivery_date']; ?></td>
            	<td><?php 
	            	if( empty($row['booking_date']) || $row['booking_date'] == "0000-00-00 00:00:00" ) {
	            		echo "-";
	            	} else {
	            		echo date( "Y-m-d", strtotime($row['booking_date']) );
	            	}
	            ?>
	            </td>
            	<td><?php 
            		if( empty($row['product_date_in']) || $row['product_date_in'] == "0000-00-00 00:00:00" ) {
            			echo "<i>ยังไม่ได้รับสินค้า</i>";
            		} else {
            			echo '<span style="color:#3366CC; font-weight: bold;">'.date( "Y-m-d", strtotime($row['product_date_in']) ).'</span>';
            		}
            	?>
            	</td>
            	<td align="right" class="<?php if($Days > -1) { echo "green-font"; } else { echo "red-font"; }?>"><?php echo $Days; ?>&nbsp;วัน</td>
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
	$('#report_supplier_perform').addClass('active');
	
	var oTable = $('#table-report-perform-list').dataTable({
		"pageLength": 100
		
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
	 	window.location.href = '?page=report_supplier_perform&finddate='+search_date;
	})
	$('#table-report-perform-list_filter').append(label);
	$('#table-report-perform-list_filter').append(button);

	
})

</script>