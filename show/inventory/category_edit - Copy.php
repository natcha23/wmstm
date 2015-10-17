<?php
if(!isset($_REQUEST['id'])){
	echo '
	<script>
		window.location.href = "?page=catagory"
	</script>
	';
	exit();
}

if(isset($_POST['id'])){
	$data = $_POST['data']; 
	$now = date('Y-m-d H:i:s');
		$data = array(
			'cat_name' => $data['cat_name'],
			'dateupdate' => $now
		);
		$db->update('tb_cat',$data,array('cat_id'=>$_POST['id']));
		
		echo '<script>window.location.href="?page=catagory";</script>';
		exit;
}

$db->select('*')->from('tb_cat')->where(array('cat_id'=>$_REQUEST['id']));
$sql = $db->get();
$row = $sql->row_array();
// echo '<pre>' . print_r($row,1) . '</pre>';
?>


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
		<div class="hr-line-dashed"></div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<button class="btn btn-white" onclick="javascript:window.location.href='?page=catagory'">Cancel</button>
				<button class="btn btn-primary" type="submit" onclick="return $.chkSubmit()">Save changes</button>
				<input type="hidden" name="id" value="<?php echo $row['cat_id']; ?>">
			</div>
		</div>
	</form>
</div>

<script>
$(function(){
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