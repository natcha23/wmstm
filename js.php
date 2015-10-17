<?php
$page = '';
if(isset($_GET['page'])){
    $page = $_GET['page'];
}
echo '<script language="javascript" src="func.js"></script>';
//////////////////////////////  ** LOCATION  ** /////////////////////////////
if($page == 'location_setting' || $page == 'set_zone' || $page == 'set_row' || $page == 'set_shelf' ){
    echo '<script language="javascript" src="js/setting_location.js"></script>';
}
?>


