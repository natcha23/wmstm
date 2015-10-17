<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();

if($_POST['method']=='add'){
    $data = array(
        'car_code'=>$_POST['carCode'],
        'car_detail'=>$_POST['carDetail']
    );
    $db->insert('car_list',$data);
}else if($_POST['method']=='edit'){
    if($_POST['carID']!=''){
        $data = array(
            'car_code'=>$_POST['carCode'],
            'car_detail'=>$_POST['carDetail']
        );
        $db->update('car_list',$data,array('car_id'=>$_POST['carID']));
    }
}
header('location:'.$_SERVER['HTTP_REFERER']);
?>
