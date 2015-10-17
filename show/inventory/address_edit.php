<?php
$user_id = $_SESSION['userID'];
if(!isset($_REQUEST['id'])){
	echo '
	<script>
		window.location.href = "?page=address"
	</script>
	';
	exit();
}
// echo '<pre>' . print_r($_POST,1).'</pre>';
if(isset($_POST['id'])){
	$data = $_POST['data'];
	$now = date('Y-m-d H:i:s');
		$data = array(
			'add_name' => $data['add_name'],
            'user_id' => $user_id,
			'dateupdate' => $now,
            'zone_id' => $data['zone_id']
		);
		$db->edit('tb_address',$data,array('add_id'=>$_POST['id']));
		echo '<script>window.location.href="?page=address";</script>';
		exit;
}

$db->select('*')->from('tb_address')->where(array('add_id'=>$_REQUEST['id']));
$sql = $db->get();
$row = $sql->row_array();
//echo $db->last_query();
?>


<div class="ibox-content">
	<form method="post" class="form-horizontal" id="address-form">
		<div class="form-group">
			<label class="col-sm-2 control-label">Address Name</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" name="data[add_name]" id="add_name" value="<?php echo $row['add_name']; ?>" onblur="$.chkName()">
				<div id="add_name_error" style="margin-left:15px"></div>
				<input type="hidden" name="data[add_id]" value="<?php echo $row['add_id']; ?>">
			</div>
		</div>
        <div class="form-group">
        <label class="col-sm-2 control-label">โซนสถานที่เก็บ</label>
        <div class="col-sm-10">
            <select class="form-control" name="data[zone_id]" id="formZoneID">
                <option value="">-- เลือก --</option>
                <?php
                $sqlZone = $db->get('tb_zone');
                foreach($sqlZone->result_array() as $rsZone){
                    $zoneChoice = '';
                    echo $rsZone['zone_id'].'--'.$row['zone_id'];
                    if($rsZone['zone_id'] == $row['zone_id']){
                        $zoneChoice = 'selected';
                    }
                    echo "<option value='$rsZone[zone_id]' $zoneChoice>$rsZone[zone_name]</option>";
                }
                ?>
            </select>
        </div>
		<div class="hr-line-dashed"></div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<button class="btn btn-white" onclick="javascript:window.location.href='?page=address'">ยกเลิก</button>
				<button class="btn btn-primary" type="submit" onclick="return $.chkSubmit()">บันทึก</button>
				<input type="hidden" name="id" value="<?php echo $row['add_id']; ?>">
			</div>
		</div>
	</form>

</div>

<script>
$(function(){
	$.chkName = function(){
		var add_name = $('#add_name').val();
		var id = $('#add_id').val();
        var zone_id = $('#formZoneID').val();
		if(add_name!=''){
			$.post('show/inventory/address_check.php',{ add_name:add_name, id:id zone_id:zone_id} ,function(rs){
				if(rs.row>0){
					$('#add_name').addClass('error');
					$('#add_name_error').html('<label id="add_name-error" class="error" for="add_name">This address name aleady exist</label>');
				}else if(rs.row==0){
					$('#add_name_error').html('<label id="add_name-error" class="error" for="add_name"></label>');
				}
			},'json');
		}
	}

	$.chkSubmit = function(e){
		var html = $('#add_name-error').html();
// 		console.log(html);
		event.preventDefault();
		if(html!='') {
			event.preventDefault();
			//event.returnValue=false;
		} else {
			event.preventDefault();
			$("#address-form").submit();
// 			return true;
		}
	}

	$("#address-form").validate({
		rules: {
         	add_name: {
         		required: true
         	}
		}
 	});
})
</script>