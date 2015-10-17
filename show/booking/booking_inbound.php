
<!-- DateTime picker -->
<script src="<?php echo _BASE_URL_; ?>lib/bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js"></script>
<link href="<?php echo _BASE_URL_; ?>lib/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

<?php
$finddate = (isset($_GET['finddate']))? $_GET['finddate']:date("Y-m-d");
$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['today'])?$_GET['today']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 12:00:00';
$eoftoday = $finddate.' 11:59:59';

$db->distinct();
$db->select("book.book_id, book.booking_date, po.inbound_id, po.po_id, po.po_delivery_date, po.po_create, po.po_supplier,sup.name");
$db->from("inbound_po AS po");
$db->join("inbound_booking AS book", "po.po_id = book.po_id", "LEFT");
$db->join("tb_supplier AS sup", "sup.supplier_id = po.po_supplier", "LEFT");

if($search != ''){
    $db->where('po.po_id',$search);
    $db->or_where('po.po_supplier',$search);
    $db->or_where('sup.name',$search);
}else{
    $db->where("DATE(po.po_delivery_date) = '$finddate'");
}
// $db->where("book.booking_status = 0");
$db->group_by("po.po_id");
$sql = $db->get();
?>

<div class="ibox-title">
	<table id="table-booking-inbound" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>ชื่อ Sup</th>
                <th>PO Reference No.</th>
                <th>วันที่สร้าง</th>
                <th>วันกำหนดส่ง</th>
                <th>นัดวันส่งของ</th>
            </tr>
        </thead>
        <tbody>
        <?php
        	$loop = 1;
            foreach($sql->result_array() as $row){

            	$datetime = $row['booking_date'];
            	$split = explode(" ", $row['booking_date']);
            	$date = $split[0];
            	$time = substr($split[1], 0, 5);
//             	echo $time;
		?>
        	<tr>
            	<td><?php echo $loop; ?></td>
            	<td><?php echo $row['po_supplier'];?></td>
                <td><?php echo $row['name'];?></td>
            	<td><?php echo $row['po_id'];?></td>
            	<td><?php echo $row['po_create'];?></td>
            	<td><?php echo $row['po_delivery_date'];?></td>
            	<td align="right">
            	<div class="form-group" style=" border: 0px solid red;">

			        	<div id="datepicker_<?php echo $row['inbound_id']; ?>" class="input-group date">
                            <input data-format="yyyy-MM-dd" type="text" id="date_<?php echo $row['inbound_id']; ?>" value="<?php echo $date; ?>"style="width:100px;"></input>
 						    <span class="add-on">
 						      <i data-time-icon="fa fa-calendar" data-date-icon="fa fa-calendar">
 						      </i>
 						    </span>
 						  </div>

					  <div id="timepicker_<?php echo $row['inbound_id']; ?>" class="input-group date">
 					    <input data-format="hh:mm" type="text" id="time_<?php echo $row['inbound_id']; ?>" value="<?php echo $time; ?>" style="width:100px;"></input>
 					    <span class="add-on">
 					      <i data-time-icon="fa fa-clock-o" data-date-icon="fa fa-clock-o">
 					      </i>
 					    </span>
 					  </div>
 					</div>
 					<script type="text/javascript">
					  $(function() {
					    $('#timepicker_'+<?php echo $row['inbound_id']; ?>).datetimepicker({
// 							maskInput: true,
					      	pickDate: false,
					      	language: 'en',
					    });

					    $('#datepicker_'+<?php echo $row['inbound_id']; ?>).datetimepicker({
					        pickTime: false,
					        language: 'en',
					        pickSeconds: false
					      });
					  });
					</script>
            		<button id="bS_<?php echo $row_id; ?>" <?php if($product_fefo==1){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-success" onclick="$.bookingAction(<?php echo $row['inbound_id']; ?>)"><i class="fa fa-floppy-o"></i></button>
            		<input type="hidden" id="po_<?php echo $row['inbound_id']; ?>" value="<?php echo $row['po_id']; ?>">
            		<input type="hidden" id="bookid_<?php echo $row['inbound_id']; ?>" value="<?php echo $row['book_id']; ?>">

            	</td>
            </tr>

		<?php
				$loop++;
            }
		?>

        </tbody>
	</table>
</div>

<script>
$(function(){
	$('#nav-booking').parent().addClass('active');
	$('#nav-booking').addClass('in');
	$('#booking_inbound').addClass('active');

	var oTable = $('#table-booking-inbound').dataTable({
		"pageLength": 50,
		"columns": [
		            null,
		            null,
		            null,
		            null,
		            null,
                    null,
		            { "width": "300px" }
		          ]
	});
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $finddate; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd',
	 	autoclose: true
	});

    $('.dataTables_filter input').unbind().keypress(function(e) {
        if(e.which == 13) {
            window.location.href = '?page=booking_inbound&search='+$(this).val();
        }
    });

	var label = $('<label />').html(' วันที่: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=booking_inbound&finddate='+search_date;
	})
	$('#table-booking-inbound_filter').append(label);
	$('#table-booking-inbound_filter').append(button);

	$.bookingAction = function(id){
// 		var currentdate = new Date();
// 		var datetime = "Last Sync: " + currentdate.getFullYear() + "-"
// 		                + (currentdate.getMonth()+1) + "-"
// 		                + currentdate.getDate() + " "
// 		                + currentdate.getHours() + ":"
// 		                + currentdate.getMinutes() + ":"
// 		                + currentdate.getSeconds();
// 		console.log(datetime);return;
		var book = $('#bookid_'+id).val();
		var po = $('#po_'+id).val();
		var date = $('#date_'+id).val();
		var time = $('#time_'+id).val();
		var booking_date = date + " " + time + ":00";
		var data = new Array();
		data.push({
            booking: booking_date,
            po_id: po,
            book_id: book,
            id: id
        });
		if(booking_date!=''){
			$.post('show/booking/booking_action.php',{ method:'bookingact', data },function(rs) {
				if(rs!="") {
					$('#bookid_'+id).val(rs);
				}
				alert("บันทึกข้อมูลแล้ว");
			})
		}
	}

})

</script>