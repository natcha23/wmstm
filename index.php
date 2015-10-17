<!DOCTYPE html>
<?php
require_once('config.php');
require_once('class_my.php');
require_once('func.php');
// require_once('helper/class_upload.php');
// require_once('helper/SimpleImage.php');
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
            $_SESSION['fname'] = $rsLogin['user_pname'] . $rsLogin['user_fname'];
            $_SESSION['lname'] = $rsLogin['user_lname'];
            $_SESSION['level'] = $rsLogin['user_permit_level'];
            if($rsLogin['car_id'] != 'NULL'){
                $_SESSION['user_car_id'] = $rsLogin['car_id'];
            }
            if($rsLogin['user_permit_level']>4){
                header("location:handheld/");
            }
        }
    }
}
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thaimart</title>
        <?php include ('component/inc_script.php');?>
        <?php @require("js.php"); ?>
    </head>


    <body>
    <?php
    if(isset($_SESSION['username']) && isset($_SESSION['password']) && !empty($_SESSION['username']) && !empty($_SESSION['password'])){
    ?>
    <div id="wrapper">
        <!-- left menu -->
        <?php include('component/menu_left.php'); ?>
        <!-- menu top -->
        <div id="page-wrapper" class="gray-bg dashbard-1">
            <div>
            <?php include('component/menu_top.php'); ?>
            </div>
            <div id="main_content">
                <?php
                    include_once ('show/index.php');
                ?>
            </div>
        </div>
        <div id="divLoader" class="popupLoader ibox-title" style="display: none;">
            <div class="sk-spinner sk-spinner-double-bounce">
                <div class="sk-double-bounce1"></div>
                <div class="sk-double-bounce2"></div>
            </div>
        </div>
        <div id="saveSuccess" class="popupLoaderSave" style="display:none;">
            <span style="color:red; font-size: 36px; background-color: lavender; width:400px; height: 80px; position: absolute; padding-top: 15px; text-align: center; border">
                <span>บันทึกข้อมูลเรียบร้อย</span>
            </span>
        </div>
    </div>

    <?php
    }else {
        include_once ('login.php');
    }
    ?>
    </body>

</html>
<?php
exit();
?>