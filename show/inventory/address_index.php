<?php
// $db->select('tb_cat.cat_id,tb_cat.cat_name, tb_address.add_name')->from('tb_cat_address');
// $db->join('tb_cat','tb_cat.cat_id = tb_cat_address.cat_id','left');
// $db->join('tb_address','tb_address.add_id = tb_cat_address.add_id','left');
// $db->where(array('tb_cat_address.status'=>0));
// $db->order_by('tb_cat.cat_id asc, tb_address.add_id asc');

if($_POST['data']) {
	$data = $_POST['data'];
	$now = date('Y-m-d H:i:s');

	$field = array();
	$field['add_name'] = $data['add_name'];
    $field['zone_id'] = $data['zone_id'];
	$field['datecreate'] = $now;
	$field['dateupdate'] = $now;

	if( empty($data['add_id']) ) {
		$db->insert("tb_address", $field);
	}else{
		unset($field['datecreate']);
		$db->update("tb_address", $field, array("add_id" => $data['add_id']));
	}
}

$db->join('tb_zone tz','tz.zone_id = ta.zone_id');
$db->select('ta.*,tz.zone_name')->from('tb_address ta')->where(array('ta.status'=>0))->order_by('ta.add_name asc');
$sql = $db->get();

function getZone($zoneID){
    $db = DB();
    $sql = $db->get_where('tb_zone','zone_id ='.$zoneID);
    $rs = $sql->row_array();
    return $rs['zone_name'];
}
?>
<button class="btn btn-success btn-facebook" onclick="$('#address-form-div').toggle('1000'); $('#address-list-div').toggle('1000');">
	<i class="fa fa-codepen"> </i> เพิ่มสถานที่เก็บ
</button>
<div class="ibox-content" id="address-form-div" style="display:none">
	<form method="post" class="form-horizontal" id="address-form">
		<div class="form-group">
			<label class="col-sm-2 control-label">ชื่อสถานที่เก็บ</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" name="data[add_name]" id="add_name" placeholder="ชื่อสถานที่เก็บ" value="<?php echo $row['add_name']; ?>" onblur="$.chkName()">
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
                        echo "<option value='$rsZone[zone_id]'>$rsZone[zone_name]</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
		<div class="hr-line-dashed"></div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2"></div>
		</div>
	</form>
	<button class="btn btn-white" onclick="$('#address-form-div').toggle('1000'); $('#address-list-div').toggle('1000');">ยกเลิก</button>
	<button class="btn btn-primary" type="submit" onclick="return $.chkSubmit()">บันทึก</button>
</div>

<div class="ibox-title" id="address-list-div">
<table id="table-address" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th style="width:5px">#</th>
		<th>รายการสถานที่เก็บ</th>
        <th>โซน</th>
		<th style="width:10%"></th>
	</tr>
</thead>
<tbody>
<?php
foreach($sql->result_array() as $i => $arr){
?>
	<tr>
		<td><?php echo $i+1; ?></td>
		<td><?php echo $arr['add_name']; ?></td>
        <td><?php echo $arr['zone_name'];?></td>
		<td>
			<a href="?page=address_edit&id=<?php echo $arr['add_id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
			<!--<a href="?page=address_action&action=del&id=<?php echo $arr['add_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบข้อมูล')">ลบ</a>-->
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>
</div>

<script>
$(function(){
	var oTable = $('#table-address').dataTable({
		"pageLength": 100,
	});

	$.chkName = function(){
		var add_name = $('#add_name').val();
		var id = $('#add_id').val();
		if(add_name!=''){
			$.post('show/inventory/address_check.php',{ add_name:add_name, id:id } ,function(rs){
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
		console.log(html);
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
});
</script>