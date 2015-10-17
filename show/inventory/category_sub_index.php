<?php
$parent = $_GET['parent'];

$db->select('*')->from('tb_cat')->where(array('cat_id'=>$parent,'status'=>0));
$sql_parent = $db->get();
$arr_parent = $sql_parent->row_array();

$db->select('*')->from('tb_cat')->where(array('cat_parent'=>$parent,'status'=>0));
$sql_sub = $db->get();
$row_sub = $sql_sub->num_rows();
?>

<button class="btn btn-success btn-facebook" onclick="$('#catsub-form-div').toggle('1000'); $('#catsub-list-div').toggle('1000');">
	<i class="fa fa-tag"> </i> เพิ่มประเภทสินค้าย่อยของ <?php echo $arr_parent['cat_name']; ?>
</button>

<div class="ibox-content" id="catsub-form-div" style="display:none">
	<form method="post" class="form-horizontal" id="catsub-form">
		<div class="form-group">
			<label class="col-sm-2 control-label">ชื่อประเภทสินค้า</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" required="" name="cat_name" id="cat_name" value="<?php echo $row['cat_name']; ?>" placeholder="ชื่อประเภทสินค้า">
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
		<button class="btn btn-primary" type="submit">บันทึก</button>
		<!-- onClick="return $.checkForm();" -->
	</form>
</div>

<div class="ibox-title" id="catsub-list-div">
<table class="table table-striped table-bordered table-hover" id="table-sub-category">
<thead>
	<tr>
		<th style="width:5px">#</th>
		<th>Sub Catagory</th>
		<th style="width:25%"></th>
	</tr>
</thead>
<tbody>
<?php
if($row_sub==0){
?>
	<tr>
		<td></td>
		<td style="text-align:center;font-weight:bold">ไม่มีข้อมูล</td>
		<td></td>
	</tr>
<?php
}else{
	$i = 0;
	foreach($sql_sub->result_array() as $arr_sub){ $i++;
?>
	<tr>
		<td><?php echo $i; ?></td>
		<td><?php echo $arr_sub['cat_name']; ?></td>
		<td></td>
	</tr>
<?php	
	}
}
?>
</tbody>
</table>
</div>

<script>
$(function(){
	
	$('#catsub-form-div').toggle('1000'); 
	$('#catsub-list-div').toggle('1000');

	var oTable = $('#table-sub-category').dataTable({
		"pageLength": 50,
		"ordering": false
	});

	$("#catsub-form").validate({
		rules: {
         	cat_name: {
         		required: true,
         		minlength: 3,
         	}
		}
 	});

 	$('#catsub-form').on('submit', function(e) {
        e.preventDefault();
    });

 	$.checkForm = function(){
 		// var row = $('.col-sm-10'); 
 		// var count = 0;
 		// $(row).each(function(i,e){
 		// 	var label = $(e).find('label.error')//.html();

 		// 	if(typeof label !== 'undefined'){
 		// 		console.log(label);
 		// 		// if(label=='')
 		// 		// 	return false;
 		// 		// else
 		// 		// 	return true;
 		// 	}
 		// })
 		// return false;
 	}

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
	
});
</script>