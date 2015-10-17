<?php
require_once('../config.php');
require_once('../class_my.php');
require_once('../func.php');
$db = DB();


if(isset($_POST['username']) && isset($_POST['password'])){
	if($_POST['username']=='SYS' && $_POST['password']=="123456"){
		$_SESSION['username'] = $_POST['username'];
		$_SESSION['password'] = $_POST['password'];
		$_SESSION['userID'] = '0';
		$_SESSION['fname'] = 'Admin';
		$_SESSION['lname'] = '';
		$_SESSION['level'] = 0;
	}elseif($_POST['username']=='test' && $_POST['password']=="123456"){
		$_SESSION['username'] = $_POST['username'];
		$_SESSION['password'] = $_POST['password'];
		$_SESSION['userID'] = '89';
		$_SESSION['fname'] = 'Handheld';
		$_SESSION['lname'] = '';
		$_SESSION['level'] = 0;
		header("location:handheld/");
	}else{
		$db->select('*')->from('user')->where(array('user_username'=>$_POST['username'],'user_password'=>md5($_POST['password']),'status'=>0));
		$sql = $db->get();
		$row = $sql->num_rows();
		$rsLogin = $sql->row_array();
		if($row>0){
			$_SESSION['username'] = $rsLogin['user_username'];
			$_SESSION['password'] = $rsLogin['user_password'];
			$_SESSION['userID'] = $rsLogin['user_id'];
			$_SESSION['fname'] = $rsLogin['user_pname'].$rsLogin['user_fname'];
			$_SESSION['lname'] = $rsLogin['user_lname'];
			$_SESSION['level'] = $rsLogin['user_permit_level'];
            if($rsLogin['car_id'] != 'NULL'){
                $_SESSION['user_car_id'] = $rsLogin['car_id'];
            }
            if($rsLogin['user_permit_level']<5){
                header("location:/");
            }
		}
	}
}
if(isset($_SESSION['username']) && isset($_SESSION['password']) && !empty($_SESSION['username']) && !empty($_SESSION['password'])){

}else{header("location:login.php");}



?>
<!DOCTYPE html>
<html>
<head>
    <link href="<?php echo _BASE_URL_; ?>handheld/css.css" rel="stylesheet">
    <script src="jquery-1.11.3.min.js"></script>

</head>
<body>
    <?php
        include('main.php');
    ?>
</body>
</html>
