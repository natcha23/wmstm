<?php
session_start();
    $_SESSION['username'] = '';
    $_SESSION['password'] = '';
?>
<script type="text/javascript">
function chk_form(){
    var j_keep_login=document.form_login.remember_me;
    var i_username=document.form_login.username.value;
    var i_password=document.form_login.password.value;
    if(j_keep_login.checked==true){
        var days=10; // กำหนดจำนวนวันที่ต้องการให้จำค่า
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
        document.cookie = "CK_username=" +i_username+ "; expires=" + expires + "; path=/";
        document.cookie = "CK_password=" +i_password+ "; expires=" + expires + "; path=/";
    }else{
        var expires="";
        document.cookie = "CK_username="+expires+";-1;path=/";
        document.cookie = "CK_password="+expires+";-1;path=/";
    }
}
</script>
    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <h2>Login</h2>
            <form class="m-t" role="form" name="form_login" method="post" action="?page=home" onsubmit="return chk_form()">
                <div class="form-group">
                    <input type="text" class="form-control" name="username" placeholder="username" required="" value="<?php echo isset($_COOKIE['CK_username'])?$_COOKIE['CK_username']:'';?>">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="password" required="" value="<?php echo isset($_COOKIE['CK_password'])?$_COOKIE['CK_password']:'';?>">
                </div>
                <div class="form-group">
                    <label> <input type="checkbox" class="i-checks" id="remember_me" name="remember_me" <?php echo(isset($_COOKIE['CK_username']) && $_COOKIE['CK_username']!="")?"checked":"";?>> Remember me </label>
                </div>
                <input type="submit" class="btn btn-primary block full-width m-b" value="Login">

                <a href="index.php"><small>Forgot password?</small></a>
            </form>
            <p class="m-t"> <small>Thaimart 2015</small> </p>
        </div>
    </div>



