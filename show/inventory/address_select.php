<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');

$db = DB();
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
<div class="row" style="margin:0px">
	<div class="col-lg-6">
		<div class="widget style1 lazur-bg" style="">
			<button style="position:absolute;top:0px;right:0px" class="btn btn-lg btn-warning" onclick="$.changeSideLocation('#div_left','#div_right')"><i class="fa fa-angle-double-right"></i></button>
			สถานที่เก็บที่ไม่ได้เลือก
		</div>
		<div class="input-group m-b">
			<span class="input-group-addon">ค้นหา</span> 
			<input type="text" placeholder="พิมพ์เพื่อค้นหา" class="form-control" onkeyup="$.searchDiv($(this).val(),'#div_left > .cat_row')">
		</div>
		<div class="cat_row_all btn btn-block btn-outline btn-default" rel="<?php echo $al_p['cat_name']; ?>">
			<label for="chk_all_left">
				<input type="checkbox" id="chk_all_left" onchange="$.checkAll('#chk_all_left','#div_left')"> 
				เลือกทั้งหมด
			</label>
		</div>
		<div id="div_left" style="overflow-y:auto">
		<?php
			$db->select('*')->from('tb_address')->where(array('status'=>0));
			$db->where_not_in('add_id',$_POST['data']);
			$sql = $db->get();
			foreach($sql->result_array() as $al_p){
		?>
		<div class="btn btn-block btn-outline btn-primary cat_row" rel="<?php echo $al_p['add_name']; ?>" style="margin-top: 1px;">
			<label for="chk_<?php echo $al_p['add_id']; ?>">
				<input type="checkbox" id="chk_<?php echo $al_p['add_id']; ?>" name="chk[]" value="<?php echo $al_p['add_id']; ?>"> 
				<span><?php echo $al_p['add_name']; ?></span>
			</label>
		</div>
		<?php
			}
		?>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="widget style1 lazur-bg" style="">
			<button style="position:absolute;top:0px;left:0px" class="btn btn-lg btn-warning" onclick="$.changeSideLocation('#div_right','#div_left')"><i class="fa fa-angle-double-left"></i></button>
			สถานที่เก็บที่เลือก
		</div>
		<div class="input-group m-b">
			<span class="input-group-addon">ค้นหา</span> 
			<input type="text" placeholder="พิมพ์เพื่อค้นหา" class="form-control" onkeyup="$.searchDiv($(this).val(),'#div_right > .cat_row')">
		</div>
		<div class="cat_row_all btn btn-block btn-outline btn-default" rel="<?php echo $al_p['cat_name']; ?>">
			<label for="chk_all_right">
				<input type="checkbox" id="chk_all_right" onchange="$.checkAll('#chk_all_right','#div_right')"> 
				เลือกทั้งหมด
			</label>
		</div>
		<div id="div_right" style="overflow-y:auto">
		<?php
		if(count($_POST['data'])!=0){
			$db->select('*')->from('tb_address')->where(array('status'=>0));
			$db->where_in('add_id',$_POST['data']);
			$sql = $db->get();
			foreach($sql->result_array() as $al_p){
		?>
		<div class="btn btn-block btn-outline btn-primary cat_row" rel="<?php echo $al_p['add_name']; ?>" style="margin-top: 1px;">
			<label for="chk_<?php echo $al_p['add_id']; ?>">
				<input type="checkbox" id="chk_<?php echo $al_p['add_id']; ?>" name="chk[]" value="<?php echo $al_p['add_id']; ?>"> 
				<span><?php echo $al_p['add_name']; ?></span>
			</label>
		</div>
		<?php
			}
		}
		?>
		</div>
	</div>
</div>

<script>
$(function(){
	$('#div_left').height($(window).height() - 250);
	$('#div_right').height($(window).height() - 250);

	$.searchDiv = function(key,div){
		key=$.trim(key);
		if(key!=''){
		var ff=$( div +'[rel*="'+key+'"]').show();
			$( div +':not([rel*="'+key+'"])').hide();
		}else{
			$( div).show();
		}
	}

	$.checkAll = function(chk_all,div,chkbox){
		if($(chk_all).is(':checked')){ 
			var checkboxs = $(div+' input[name="chk[]"]');
			$(checkboxs).each(function(e){
				if($(this).is(':visible')==true){
					this.checked = true
				}
			});
		}else{
			var checkboxs = $(div+' input[name="chk[]"]');
			$(checkboxs).each(function(e){
				if($(this).is(':visible')==true){
					this.checked = false
				}
			});
		}
	}

	$.changeSideLocation = function(from,to){ 
		var checkboxs = $(from+' input[name="chk[]"]');
		$(checkboxs).each(function(e){
			if(this.checked==true){
				$(this).parent().parent().appendTo(to);
			}
		});
	}
})
</script>
