<button class="btn btn-success btn-facebook" onclick="$('#cat-form-div').toggle('1000'); $('#cat-list-div').toggle('1000');">
	<i class="fa fa-tag"> </i> เพิ่มประเภทสินค้า
</button>

<?php

if($_POST['data']) {
	$data = $_POST['data'];
	$now = date('Y-m-d H:i:s');
	
	$field = array();
	$field['cat_name'] = $data['cat_name'];
	$field['datecreate'] = $now;
	$field['dateupdate'] = $now;
	
	if( empty($data['cat_id']) ) {
		$db->insert("tb_cat", $field);
	}else{
		unset($field['datecreate']);
		$db->update("tb_cat", $field, array("cat_id" => $data['cat_id']));
	}
}

$db->select('*')->from('tb_cat')->where(array('status'=>0));
$sql = $db->get();

?>


<div class="ibox-content" id="cat-form-div" style="display:none">
	<form method="post" class="form-horizontal" id="cat-form">
		<div class="form-group">
			<label class="col-sm-2 control-label">Category Name</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" name="data[cat_name]" id="cat_name" value="<?php echo $row['cat_name']; ?>" onblur="$.chkName()">
				<div id="cat_name_error" style="margin-left:15px"></div>
				<input type="hidden" name="data[cat_id]" value="<?php echo $row['cat_id']; ?>">
			</div>
		</div>
		<div class="hr-line-dashed"></div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				
				
			</div>
		</div>
	</form>
	<button class="btn btn-white" onclick="$('#cat-form-div').toggle('1000'); $('#cat-list-div').toggle('1000');">ยกเลิก</button>
	<button class="btn btn-primary" type="submit"  onclick="return $.chkSubmit()">บันทึก</button>
</div>


<div class="ibox-title" id="cat-list-div">
<table class="table table-striped table-bordered table-hover" id="table-category">
	<thead>
		<tr>
			<th><input type="checkbox" id="chk_all"	onchange="$.checkAll('#chk_all','chk_list[]')" style="display: none">#</th>
			<th>Catagory</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$row = 1;
foreach ( $sql->result_array () as $arr ) {
	?>
	<tr>
		<td><?php echo $row; ?></td>
		<td><?php echo $arr['cat_name']; ?></td>
		<td>
			<a href="?page=category_edit&id=<?php echo $arr['cat_id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
			<a href="?page=category_action&action=del&id=<?php echo $arr['cat_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบข้อมูล')">ลบ</a>
		</td>
	</tr>
<?php
	$row++;
}
?>
</tbody>
</table>
</div>
<script>
$(function(){
	
	var oTable = $('#table-category').dataTable({
		"pageLength": 50,
		"ordering": false
	});

	$.chkName = function(){
		var cat_name = $('#cat_name').val();
		var id = $('#cat_id').val();
		if(cat_name!=''){
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
	
	$.chkSubmit = function(e){
		var html = $('#cat_name-error').html();
		console.log(html);
		event.preventDefault();
		if(html!='') {
			console.log('xxx');
			event.preventDefault();
			//event.returnValue=false;
		} else {
			event.preventDefault();
			$("#cat-form").submit();
// 			return true;
		}
	}

	$("#cat-form").validate({
		rules: {
         	cat_name: {
         		required: true
         	}
		}
 	});
});
</script>