<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();
$user_id = $_SESSION['userID'];
$actionBack = $_SERVER['HTTP_REFERER'];
if(isset($_POST['actionType']) && $_POST['actionType']=='carOut'){
	if($_POST['carID'] != ''){
		$carID = $_POST['carID'];
		$db->select('oc.outbound_rt');
		$db->join('outbound_rt_status ors','ors.rt_id = oc.outbound_rt');
		$sqlGetRt = $db->get_where('outbound_car oc',array('oc.car_id'=>$carID,'ors.status'=>4));

		foreach($sqlGetRt->result_array() as $rsRt){
			$db->update('outbound_rt_status',array('status'=>5,'time_out_car'=>_DATE_TIME_,'user_id'=>$user_id),array('rt_id'=>$rsRt['outbound_rt'],'status'=>'4'));
            $db->update('outbound_car',array('status'=>1),array('outbound_ry'=>$rsRt['outbound_rt']));
		//echo $db->last_query();
		}
	}
}
header('location:'.$actionBack);
?>