<button class="btn btn-success btn-facebook" onclick="$('#add_user_form').toggle('1000'); $('#div-table-user').toggle('1000');">
	<i class="fa fa-user-plus"> </i> เพิ่มผู้ใช้งาน
</button>
<?php
if($_POST['regist_username']){
	$db->select('user_username')->from('user')->where(array('user_username'=>$_POST['regist_username'],'status'=>0));
	$sql = $db->get();

	$row = $sql->num_rows();
	if($row==0){
        $data = array(
            'user_pname' => $_POST['regist_pname'],
			'user_fname' => $_POST['regist_fname'],
			'user_lname' => $_POST['regist_lname'],
			'user_username' => $_POST['regist_username'],
			'user_password' => md5($_POST['regist_password']),
			'user_permit_level' => $_POST['regist_level'],
			'datecreate' => date('Y-m-d H:i:s'),
			'dateupdate' => date('Y-m-d H:i:s'),
			'status' => 0);
        if($_POST['regist_level'] == 6){ $data['car_id'] = $_POST['car_id'];}
        if($_POST['regist_level'] == 7){ $data['branch_id'] = $_POST['branch_id'];}
		$db->insert('user',$data);
	}
}
?>
<div class="ibox-content" id="add_user_form" style="display:none">
    <form method="post" action="?page=user" class="form-horizontal" id="form_add_user">
        <div class="form-group">
        	<label class="col-sm-2 control-label">ชื่อ - นามสกุล</label>
            <div class="col-sm-10">
            	<div class="col-md-2"><input type="text" class="form-control" placeholder="คำนำหน้า" name="regist_pname" required=""></div>
            	<div class="col-md-4"><input type="text" class="form-control" placeholder="ชื่อ" name="regist_fname" required=""></div>
            	<div class="col-md-4"><input type="text" class="form-control" placeholder="นามสกุล" name="regist_lname" required=""></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">Username</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="text" class="form-control" placeholder="Username" name="regist_username" id="regist_username" required="" onblur="$.chk_username()"></div>
            	<div id="regist_username_error" style="margin-left:15px"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">Password</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="password" class="form-control" placeholder="Password" name="regist_password" id="regist_password" required=""></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">Re Password</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="password" class="form-control" placeholder="Re Password" name="regist_repassword" required=""></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

  		<div class="form-group">
        	<label class="col-sm-2 control-label">ระดับการใช้งาน</label>
        	<div class="col-sm-10">
                <?php
                $sqlPer = $db->get('user_permission');
                foreach($sqlPer->result_array() as $rsPer){
                ?>
	            <div class="radio">
	            	<label>
                        <input type="radio" value="<?php echo $rsPer['permission_id']?>" name="regist_level" required="" onclick="openInput(<?php echo $rsPer['permission_id']?>);"> <?php echo $rsPer['permission_name'];?>
                        <?php
                        if($rsPer['permission_id'] == '7'){
                            $sqlCar = $db->get('car_list');
                            echo '<select name="car_id" id="selectCar" >';
                                foreach($sqlCar->result_array() as $rsCar){
                                    echo "<option value='".$rsCar['car_id']."'>".$rsCar['car_code']."</option>";
                                }
                            echo '</select>';
                        }
                        if($rsPer['permission_id'] == '8'){
                            //$sqlCar = $db->get('car_list');
                            echo '<select name="branch_id" id="selectCar" >';
                                    echo "<option value='1'>สาขาทดสอบ</option>";
                            echo '</select>';
                        }
                        ?>
                    </label>
	            </div>
                <?php
                }?>
	        </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2">
                <button class="btn btn-primary" type="submit">บันทึก</button>
            </div>
        </div>
    </form>
</div>

<div id="div-table-user" style="background-color:#fff;border:1px solid #ccc">
<table id="table-user" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>#</th>
		<th>ชื่อ - นามสกุล</th>
		<th>Username</th>
		<th style="width:150px"></th>
	</tr>
</thead>
<tbody>
<?php
	$db->select('*')->from('user')->where(array('status'=>0));
	$sql = $db->get();
	$row_count = 0;
	foreach($sql->result_array() as $arr){ $row_count++;
?>
	<tr>
		<td><?php echo $row_count; ?></td>
		<td><?php echo $arr['user_pname'].$arr['user_fname'].' '.$arr['user_lname']; ?></td>
		<td><?php echo $arr['user_username']; ?></td>
		<td>
			<a href="?page=user_edit&id=<?php echo $arr['user_id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
			<a href="?page=user_action&action=del&id=<?php echo $arr['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนนัยการลบข้อมูล')">ลบ</a>
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
	$('#nav-setting').parent().addClass('active');
	$('#nav-setting').addClass('in');
	$('#user').addClass('active');
	
	$.chk_username = function(){
		var regist_username = $('#regist_username').val();
		if(regist_username!=''){
			$.post('show/user/chk_username.php',{ regist_username:regist_username } ,function(rs){
				console.log(rs);
				if(rs.row==1){
					$('#regist_username').addClass('error');
					$('#regist_username_error').html('<label id="regist_password-error" class="error" for="regist_password">This Username aleady exist</label>');
				}else if(rs.row==0){
					$('#regist_username_error').html('');
				}
			},'json');
		}
	}

	var oTable = $('#table-user').dataTable({
		"pageLength": 50,
	});

	$("#form_add_user").validate({
		rules: {
         	regist_password: {
             	required: true,
             	minlength: 6
         	},
         	regist_repassword: {
             	required: true,
             	minlength: 6,
             	equalTo: '#regist_password'
         	}
		}
 	});
})
</script>