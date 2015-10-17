<?php 
$search = isset($_GET['search'])?$_GET['search']:'';
$finddate = isset($_GET['finddate'])?$_GET['finddate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$finddate -1 day" ) ).' 07:00:00';
$eoftoday = $finddate.' 06:59:59';

$user_id = $_SESSION['userID'];

?>

	<table id="table-report-list" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>RT Number</th>
				<th>RT Date</th>
				<th>Product</th>
				<th>Disappear</th>
				<th>Worn-out</th>
				<th>Expire</th>
				<th>Operator</th>
				<th style="display:block">Action</th>
			</tr>
		</thead>
		<tbody>
<?php 

$refid = 'DBV255807/0060';
/* Select report*/

// $db->select_sum('qty_disappear, qty_expire, qty_wornout', 'disa,exp,worn');
$db->select_sum('rep.qty_disappear', 'sum_disappear');
$db->select_sum('rep.qty_expire', 'sum_expire');
$db->select_sum('rep.qty_wornout', 'sum_wornout');

$db->select("user.user_fname, user.user_lname");
$db->select("rt_refid, rep.user_id")->from("report_missing AS rep");
$db->join("user AS user", "rep.user_id = user.user_id", "LEFT");
$db->where(array(
// 		"rep.rt_refid" => $refid,
// 		"date(rep.rt_date)" => $finddate,
		"rep.status" => 0
));
$db->where("(rep.qty_disappear > 0 OR rep.qty_expire > 0 OR rep.qty_wornout > 0)");
$db->group_by("rep.barcode");
$query = $db->get();
// _print($db->last_query());//exit;
// _print($query->result_array());
foreach($query->result_array() as $val) {
	
	if(empty($val['user_id'])) {
		$val['user_fname'] = $_SESSION['fname'];
		$val['user_lname'] = $_SESSION['lname'];
	}
	$db->select("ort.rt_date, ort.goods_name")->from("outbound_rt AS ort");
	$db->where(array("ort.rt_refid" => $val['rt_refid']));
	$sql = $db->get();
// 	$rs = $sql->row_array();
	$result[] = array_merge($val, $sql->row_array());
}

$rNum = 1;
foreach ($result as $row) {
?>
			<tr>
				<td><?php echo $rNum; ?></td>
				<td><?php echo $row['rt_refid']; ?><span class="label <?php echo $label; ?> pull-right"><?php echo $message; ?></span></td>
				<td><?php echo $row['rt_date']; ?></td>
				<td><?php echo $row['goods_name']; ?></td>
				<td><?php echo $row['sum_disappear']; ?></td>
				<td><?php echo $row['sum_wornout']; ?></td>
				<td><?php echo $row['sum_expire']; ?></td>
				<td><?php echo $row['user_fname'] . " " . $row['user_lname']; ?></td>
				
				<td style="display:block">
				<a href="#"><button class="btn btn-primary">จัดการสินค้า</button></a>
					<!-- a href="?page=report_missing&rtid=<?php echo $row['rt_refid']; ?>&finddate=<?php echo $finddate; ?>"><button class="btn btn-primary">จัดการสินค้า</button></a-->
					<!-- button class="btn btn-danger" onclick="$.delchecking('<?php echo $row['rt_refid']; ?>');">ลบ</button-->
				</td>
			</tr>
<?php 
	$rNum++;
}

?>			
		</tbody>
	</table>
<script>

$(function() {

	$.chkName = function(){
		var cat_name = $('#cat_name').val();
		var id = $('#cat_id').val();
		if(regist_username!=''){
			$.post('show/inventory/category_check.php',{ cat_name:cat_name, id:id } ,function(rs){
				if(rs.row>0){
					$('#cat_name').addClass('error');
					$('#cat_name_error').html('<label id="cat_name-error" class="error" for="cat_name">This category name aleady exist</label>');
				}else if(rs.row==0){
					$('#cat_name_error').html('<label id="cat_name-error" class="error" for="cat_name"></label>');
				}
			},'json');
		}	
	}

	
	var oTable = $('#table-report-list').dataTable({
		"pageLength": 100,
	});

	var search = $('#table-report-list_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

// 	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
//	$(input).val('<?php echo $finddate; ?>');
// 	$(input).datepicker({
// 	 	format: 'yyyy-mm-dd',
// 		autoclose: true
// 	});
	
// 	var label = $('<label />').html(' Date: ');
// 	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){ 
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=report_missing&finddate='+search_date;
	})
	$('#table-report-list_filter').append(label);
	$('#table-report-list_filter').append(button);
	
});

</script>

