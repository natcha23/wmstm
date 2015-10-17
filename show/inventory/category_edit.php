<?php
if(!isset($_REQUEST['id'])){
	echo '
	<script>
		window.location.href = "?page=catagory"
	</script>
	';
	exit();
}

if(isset($_POST['data'])){
	$data = $_POST['data'];
	$now = date('Y-m-d H:i:s');
	$db->select('*')->from('tb_cat')->where(array('cat_id !='=>$data['cat_id'],'cat_name'=>$data['cat_name'],'status'=>0));
	$sql = $db->get();
	$row = $sql->num_rows();
	if($row==0){
		$data['dateupdate'] = $now;
		$db->update('tb_cat',$data,array('cat_id'=>$data['cat_id']));
		$db->update('tb_cat_address',array('status'=>1,'dateupdate'=>$now),array('cat_id'=>$data['cat_id']));

		if(isset($_POST['chk']) && count($_POST['chk'])>0){
			foreach($_POST['chk'] as $add_id){
				$db->select('id')->from('tb_cat_address')->where(array('cat_id'=>$data['cat_id'],'add_id'=>$add_id));
				$sql = $db->get();
				$add_row = $sql->num_rows();
				if($add_row==0){
					$db->insert('tb_cat_address',array(
						'cat_id' => $data['cat_id'],
						'add_id' => $add_id,
						'datecreate' => $now,
						'dateupdate' => $now,
						'status' => 0
					));
				}else{
					$add_arr = $sql->row_array();
					$db->update('tb_cat_address',array('status'=>0,'dateupdate'=>$now),array('id'=>$add_arr['id']));
				}
			}
		}
	}

	
	echo '<script>window.location.href="?page=catagory";</script>';
}

$db->select('*')->from('tb_cat')->where(array('cat_id'=>$_REQUEST['id']));
$sql = $db->get();
$row = $sql->row_array();
// echo '<pre>' . print_r($row,1) . '</pre>';
?>
<style>
	.style1{
		text-align: center;
		position: relative;
		padding: 10.5px 20px;
		margin: 0px;
	}
	.m-b{
		margin-bottom: 2px;
	}
	.cat_row_all{
		text-align: left;
		margin-top: 1px;
		padding: 2px;
	}
	.cat_row{
		text-align: left;
		padding: 2px;
	}
</style>

<div class="ibox-content">
	<form method="post" class="form-horizontal" role="form" action="" id="cat-form">
		<div class="form-group">
			<label class="col-sm-2 control-label">Category Name</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" name="data[cat_name]" id="cat_name" value="<?php echo $row['cat_name']; ?>">
				<div id="cat_name_error" style="margin-left:15px"></div>
				<input type="hidden" name="data[cat_id]" id="cat_id" value="<?php echo $row['cat_id']; ?>">
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">เลือกสถานที่เก็บ</label>
			<div class="col-sm-10">
				<button type="button" class="btn btn-sm btn-primary" onclick="$.locationDialog();">กดเพื่อเลือกสถานที่เก็บ</button>
				<div id="location_list">
					<?php
						$db->select('tb_address.add_id,tb_address.add_name')->from('tb_cat_address');
						$db->join('tb_address','tb_address.add_id = tb_cat_address.add_id','inner');
						$db->where(array('tb_cat_address.cat_id'=>$_GET['id'],'tb_cat_address.status'=>0));
						$sql = $db->get();
						foreach($sql->result_array() as $al_p){
					?>
					<div class="btn btn-block btn-outline btn-primary cat_row" rel="<?php echo $al_p['add_name']; ?>" style="margin-top: 1px;">
						<label for="chk_<?php echo $al_p['add_id']; ?>">
							<input type="checkbox" id="chk_<?php echo $al_p['add_id']; ?>" checked="checked" name="chk[]" value="<?php echo $al_p['add_id']; ?>"> 
							<span><?php echo $al_p['add_name']; ?></span>
						</label>
					</div>
					<?php
						}
					?>
				</div>
			</div>
		</div>

		<div class="hr-line-dashed"></div>
		
		<button class="btn btn-white" onclick="javascript:window.location.href='?page=catagory'">ยกเลิก</button>
		<button class="btn btn-primary" type="submit" onclick="return $.chkSubmit()">บันทึก</button>
		<input type="hidden" name="id" value="<?php echo $row['cat_id']; ?>">
			
	</form>
</div>

<script>
$(function(){
	var location_dialog = $('<div />',{ id:'location_dialog' });
	$.locationDialog = function(){
		var chk = $('#location_list input[name="chk[]"]');
		
		var data = new Array;
		$(chk).each(function(i,e){
			data.push(e.value);
		})

		$.post('show/inventory/category_select.php',{ data:data },function(html){
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

	$.chkSubmit = function(event){
		var html = $('#cat_name_error').html();
		if(html!='')
			return false;
		else 
			return true;
	}

	$("#cat-form").validate({
		rules: {
         	cat_name: {
         		required: true
         	}
		}
 	});
})
</script>