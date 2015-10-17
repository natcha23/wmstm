<?php
if(!isset($_REQUEST['id'])){
	echo '
	<script>
		window.location.href = "?page=user"
	</script>
	';
	exit();
}

if(isset($_POST['id'])){
	// $db->select('user_username')->from('user')->where(array('user_id !='=>$_POST['id'],'user_username'=>$_POST['regist_username'],'status'=>0));
	// $sql = $db->get();
	// $row = $sql->num_rows();

	// if($row==0){
		$data = array(
			'user_pname' => $_POST['regist_pname'],
			'user_fname' => $_POST['regist_fname'],
			'user_lname' => $_POST['regist_lname'],
			'user_username' => $_POST['regist_username'],
			'user_permit_level' => $_POST['regist_level'],
			'dateupdate' => date('Y-m-d H:i:s'),
		);
        if($_POST['regist_level'] == 7){ $data['car_id'] = $_POST['car_id'];}else{$data['car_id'] = NULL;}
        if($_POST['regist_level'] == 8){ $data['branch_id'] = $_POST['branch_id'];}else{$data['branch_id'] = NULL;}
		if($_POST['regist_password']!=''){ $data['user_password'] = md5($_POST['regist_password']); }
		$db->update('user',$data,array('user_id'=>$_POST['id']));
	// }
}

$db->select('*')->from('user')->where(array('user_id'=>$_REQUEST['id']));
$sql = $db->get();
$arr = $sql->row_array();
?>
<div class="ibox-content" id="edit_user_form" style="">
    <form method="post" action="?page=user_edit" class="form-horizontal" id="form_edit_user">
        <div class="form-group">
        	<label class="col-sm-2 control-label">ชื่อ - นามสกุล</label>
            <div class="col-sm-10">
            	<div class="col-md-2"><input type="text" class="form-control" placeholder="คำนำหน้า" name="regist_pname" required="" value="<?php echo $arr['user_pname']; ?>"></div>
            	<div class="col-md-4"><input type="text" class="form-control" placeholder="ชื่อ" name="regist_fname" required="" value="<?php echo $arr['user_fname']; ?>"></div>
            	<div class="col-md-4"><input type="text" class="form-control" placeholder="นามสกุล" name="regist_lname" required="" value="<?php echo $arr['user_lname']; ?>"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">Username</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="text" class="form-control" placeholder="Username" name="regist_username" id="regist_username" required="" onblur="$.chkUsername()" onkeyup="$.chkUsername()" value="<?php echo $arr['user_username']; ?>"></div>
            	<div id="regist_username_error" style="margin-left:15px"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">Password</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="password" class="form-control" placeholder="Password" name="regist_password" id="regist_password"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">Re Password</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="password" class="form-control" placeholder="Re Password" name="regist_repassword"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

  		<div class="form-group">
        	<label class="col-sm-2 control-label">ระดับการใช้งาน</label>
        	<div class="col-sm-10">
	            <?php
                $sqlPer = $db->get('user_permission');
                foreach($sqlPer->result_array() as $rsPer){
                    $checked = '';
                    if($arr['user_permit_level']==$rsPer['permission_id']){
                        $checked = 'checked';
                    }
                ?>
	            <div class="radio">
	            	<label>
                        <input type="radio" value="<?php echo $rsPer['permission_id']?>" <?php echo $checked; ?> name="regist_level" required="" onclick="openInput(<?php echo $rsPer['permission_id']?>);"> <?php echo $rsPer['permission_name'];?>
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
            	<input type="hidden" name="id" id="user_id" value="<?php echo $_REQUEST['id']; ?>">
                <button class="btn btn-primary" type="submit"  onclick="return $.chkSubmit()">บันทึก</button>
            </div>
        </div>
    </form>
</div>

<script>
$(function(){
	$.chkUsername = function(){
		var regist_username = $('#regist_username').val();
		var id = $('#user_id').val();
		if(regist_username!=''){
			$.post('show/user/chk_username.php',{ regist_username:regist_username, id:id } ,function(rs){
				console.log(rs);
				if(rs.row>0){
					$('#regist_username').addClass('error');
					$('#regist_username_error').html('<label id="regist_password-error" class="error" for="regist_password">This Username aleady exist</label>');
				}else if(rs.row==0){
					$('#regist_username_error').html('');
				}
			},'json');
		}
	}

	$.chkSubmit = function(event){
		var html = $('#regist_username_error').html();
		if(html!='')
			return false;
		else
			return true;
	}

	$("#form_edit_user").validate({
		rules: {
         	regist_password: {
             	required: false,
             	minlength: 6
         	},
         	regist_repassword: {
             	required: false,
             	minlength: 6,
             	equalTo: '#regist_password'
         	}
		}
 	});
})
</script>