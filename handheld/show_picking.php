<?php
$search = isset($_REQUEST['search'])?$_REQUEST['search']:'';
if ( empty($search) && empty($_REQUEST['todate']) ) {
	$_REQUEST['todate'] = date('Y-m-d');
}
$today = isset($_REQUEST['todate'])?$_REQUEST['todate']:'';
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

?>
<div><h3>นำสินค้าออก</h3></div>
<form action="" method="POST">
	<input type="text" name="search" size="10" value="<?php echo $search; ?>"/><br>
	<input type="text" name="todate" size="10" value="<?php echo $today; ?>"/>
	<input type="submit" value="ค้นหา"/>
	<input type="button" value="เมนู" onclick="javascript:window.location.href='?'"/>
</form>

<form id="frm-picking">
	<table id="table-outbound-list" class="table">
		<thead>
			<tr>
				<th>RT Date</th>
				<th>RT No.</th>
				<th>เสร็จแล้ว</th>
			</tr>
		</thead>
	<tbody>
<?php
/* Query select */
$db->select("*")->from("outbound_rt AS rt");
$db->join("outbound_rt_status AS status", "rt.rt_refid = status.rt_id", "LEFT");
$db->where("status.status = '1'");
$db->where("rt.status = 0");

if( !empty($today) ) {
	$db->where("date(rt.rt_date) = '$today'");
}
if ( !empty($search) ) {
	$db->where("rt.rt_refid LIKE '%$search%'");
}
$db->group_by("rt.rt_refid")->order_by("rt.rt_date");
$sql = $db->get();

$result = array();
$result = $sql->result_array();

$i=0;
foreach ($result as $row) {
?>
	<tr>
		<td><?php echo date('Y-m-d', strtotime( $row['rt_date'] )); ?> | </td>
		<td><?php echo $row['rt_refid']; ?></td>
		<td><input type="checkbox" id="pick_<?php echo $i; ?>" value="<?php echo $row['rt_refid']; ?>" name="picking[]"/></td>
	</tr>
<?php
		$i++;
	}
?>
</tbody>
</table>
</form>
<input type="button" value="บันทึก" onclick="$.pickevt();"/>
<script>
$(function(){

	$.pickevt = function () {
		
		event.returnValue = false;

		if( $("input[id^='pick_']:checked").length <=0 ) {
			alert('กรุณาเลือกรายการเพื่อบันทึก ');
	        event.returnValue = false;
	    } else {

	    	 if (confirm('ต้องการบันทึกข้อมูลใช่หรือไม่') == true) {

				var refid = '<?php echo $_POST['refid']; ?>';
				var todate = '<?php echo $_POST['todate']; ?>';
				var keyword = '<?php echo $_POST['search']; ?>';

				var data = new Array();
				var temp_id = $("input[id^='pick_']:checked");
				var params = {
//     					refid: refid,
    					todate: todate,
    					search: keyword 
    				};

				$(temp_id).each(function (index, element) {
		            data.push({
		                name: $(element).attr('name'),
		                value: $(element).val()
		            });
		        });
				data.push({
		            name: 'mode',
		            value: 'save_list'
		        });

					$.ajax({
						type: 'post',
						url: '?page=pick_process',
						data: data,
						success: function (html) {
	// 						alert('บันทึกข้อมูลแล้ว');
// 							console.log(html);
							/* Redirect page */
			    			$.postAndRedirectNoEventReturn('?page=show_picking', params);
						}
				    });

				event.returnValue = false;
			} else {
				event.returnValue = false;
			}
	    }
	}
	
	$.toItems = function(refid, todate, keyword) {

		var params = {
				refid: refid,
				todate: todate,
				search: keyword 
			};
		
		$.postAndRedirect('?page=check_items', params);
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

	$.postAndRedirectNoEventReturn = function(url, postData)
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
	    $('body').append(formElement);
	    $(formElement).submit();
	}
	
});
</script>
