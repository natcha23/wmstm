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
		$db->select('*')->from('tb_cat')->where(array('cat_name'=>$data['cat_name'],'status'=>0));
		$sql = $db->get();
		$row = $sql->num_rows();
		if($row==0){
			$db->insert("tb_cat", $field);
			$cat_id = $db->insert_id();

			if(isset($_POST['chk']) && count($_POST['chk'])>0){
				foreach($_POST['chk'] as $add_id){
					$db->insert('tb_cat_address',array(
						'cat_id' => $cat_id,
						'add_id' => $add_id,
						'datecreate' => date('Y-m-d H:i:s'),
						'dateupdate' => date('Y-m-d H:i:s'),
						'status' => 0
					));
				}
			}
		}
	}else{
		$db->select('*')->from('tb_cat')->where(array('cat_id'=>$data['cat_id'],'cat_name'=>$data['cat_name'],'status'=>0));
		$sql = $db->get();
		$row = $sql->num_rows();
		if($row==0){
			unset($field['datecreate']);
			$db->update("tb_cat", $field, array("cat_id" => $data['cat_id']));
		}
	}
	
}

$db->select('*')->from('tb_cat')->where(array('status'=>0));
$sql = $db->get();

?>


<div class="ibox-content" id="cat-form-div" style="display:none">
	<form method="post" class="form-horizontal" id="cat-form">
		<div class="form-group">
			<label class="col-sm-2 control-label">ชื่อประเภทสินค้า</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" required="" name="data[cat_name]" id="cat_name" value="<?php echo $row['cat_name']; ?>" placeholder="ชื่อประเภทสินค้า" onblur="$.chkName()" onkeyup="$.chkName()">
				<div id="cat_name_error" style="margin-left:15px"></div>
				<input type="hidden" name="data[cat_id]" value="<?php echo $row['cat_id']; ?>">
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">เลือกสถานที่เก็บ</label>
			<div class="col-sm-10">
				<button type="button" class="btn btn-sm btn-primary" onclick="$.locationDialog();">กดเพื่อเลือกสถานที่เก็บ</button>
				<div id="location_list"></div>
			</div>
		</div>

		<div class="hr-line-dashed"></div>

		<button class="btn btn-white" onclick="$('#cat-form-div').toggle('1000'); $('#cat-list-div').toggle('1000');">ยกเลิก</button>
		<button class="btn btn-primary" type="submit" onclick="return $.chkSubmit()">บันทึก</button>
	</form>
</div>


<div class="ibox-title" id="cat-list-div">
<table class="table table-striped table-bordered table-hover" id="table-category">
	<thead>
		<tr>
			<th style="width:5px">#</th>
			<th>Catagory</th>
			<th style="width:25%"></th>
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
			<a href="?page=category_sub_index&parent=<?php echo $arr['cat_id']; ?>" class="btn btn-sm btn-info">เพิ่มประเภทสินค้าย่อย</a>
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
// 	$('#nav-setting').parent().addClass('active');
	$('#nav-setting').addClass('in');
	$('#li-category').addClass('in active');
// 	$('#li-category').parent().addClass('in');
	$('#catagory').addClass('active');
	
	var oTable = $('#table-category').dataTable({
		"pageLength": 50,
		"ordering": false
	});

	var location_dialog = $('<div />',{ id:'location_dialog' });
	$.locationDialog = function(){
		var chk = $('#location_list input[name="chk[]"]');
		
		var data = new Array;
		$(chk).each(function(i,e){
			data.push(e.value);
		})

		$.post('show/inventory/address_select.php',{ data:data },function(html){
			$('#location_list').html('');
			$(location_dialog).html(html).dialog({
				title:'เลือกสถานที่เก็บสำหรับประเภทสินค้านี้', modal:true, resizable:false, width:650, maxHeight:$(window).height(), buttons:{
					'บันทึก': function(){ 
						var row = $('#div_right > .cat_row');
						$(row).appendTo('#location_list');
						$('#location_list > .cat_row input[name="chk[]"]').each(function(e){
							if($(this).is(':visible')==true){
								this.checked = true
							}
						});
						$(location_dialog).dialog('close');
					},
					'ปิด': function(){ $(location_dialog).dialog('close'); }
				}
			})
		})
	}

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

		if(html == undefined){
			$("#cat-form").submit();
		}else{
			if(html == ''){
				$("#cat-form").submit();
			}else{
				return false;
			}
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