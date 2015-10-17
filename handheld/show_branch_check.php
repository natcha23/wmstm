<?php

$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_REQUEST['todate'])?$_REQUEST['todate']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

?>
<div><h3>สาขาตรวจนับ</h3></div>
<form action="" method="POST">
	<input type="text" name="todate" size="15" value="<?php echo $today; ?>"/>
	<input type="submit" value="ค้นหา"/>
	<input type="button" value="เมนู" onclick="javascript:window.location.href='?'"/>
</form>
	<table id="table-outbound-list" class="table">
		<thead>
			<tr>
				<th>RT Date</th>
				<th>RT No.</th>
				<th>Action</th>
			</tr>
		</thead>
	<tbody>
<?php
/* Query select */
$db->select("*")->from("outbound_rt_status AS status");
$db->join("outbound_rt AS rt", "status.rt_id = rt.rt_refid", "LEFT");
$db->where("status.status = '5'");
$db->where("rt.status = 0");
$db->where("rt.rt_date BETWEEN '" .$yesterday. "' AND '" .$eoftoday. "'");
$db->group_by("rt.rt_refid")->order_by("rt.rt_date");

$sql = $db->get();
$result = $sql->result_array();
$i=0;

foreach ($result as $row) {
	$i++;

?>
	<tr>
		<td><?php echo date('Y-m-d', strtotime( $row['rt_date'] )); ?></td>
		<td><?php echo $row['rt_refid']; ?></td>
		<td>
            <?php
            	$db->select("*")->from("outbound_rt AS ort");
            	$db->join("outbound_check AS ochk", "ort.id = ochk.outbound_id", "LEFT");
            	$db->where(array("ort.rt_refid" => $row['rt_refid'],
            			"ochk.check_status" => 0
            	));
            	$db->group_by("barcode");
            	$query = $db->get();
            	$count_chk = $query->num_rows();
            	
            	$db->select("*")->from("outbound_rt_status")->where(array("rt_id" => $row['rt_refid']));
            	$query = $db->get();
            	
            	$rows = $query->row();
            	$rtlist = $rows->rt_product_amount;
            	$rtstatus = $rows->status;
            	
            	$message = "ดำเนินการ";
            	
            	if( !empty($rtstatus) && $rtstatus == 3 ) {
            		$message = "สำเร็จ";
            	}else{
            		$message = "ดำเนินการ";
            	}
            	
//             	if($count_chk == $rtlist) {
//             		$message = "สำเร็จ";
//             	} else {
//             		if( ($count_chk > 0) && ($count_chk < $rtlist) ) {
//             			$message = "ดำเนินการ";
//             		}
//             	}
            	
            	if($message == "สำเร็จ") {
					echo '<span style="color: #009933; font-weight: bold;">'. $message .'</span>';
				} else {
            	
            ?>
                <!-- <a href="?page=check_items&refid=<?php echo $row['rt_refid']; ?>&todate=<?php echo $today; ?>"><?php echo $message; ?></a> -->
                <a href="" id="<?php echo $row['rt_refid']; ?>" onclick="$.toItems('<?php echo $row['rt_refid']; ?>','<?php echo $today; ?>');"><?php echo $message; ?></a>
                
                <?php } ?>
        </td>
	</tr>
<?php
	}
?>
</tbody>
</table>
<script>
$(function(){

	$.toItems = function(refid, todate) {

		var params = {
				refid: refid,
				todate: todate
			};
		
		$.postAndRedirect('?page=branch_check_items', params);
	}

	$.postAndRedirect = function(url, postData)
	{
	    var postFormStr = "<form method='POST' action='" + url + "'>\n";
	    
	    for (var key in postData)
	    {
	        if (postData.hasOwnProperty(key))
	        {
	            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'/>";
	            
	        }
	    }

	    postFormStr += "</form>";
	    var formElement = $(postFormStr);
		event.returnValue=false;
	    $('body').append(formElement);
	    $(formElement).submit();
	}
	
});
</script>
