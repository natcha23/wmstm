<?php
require_once('../config.php');
require_once('../class_my.php');
require_once('../func.php');
$db = DB();
$userID = $_SESSION['userID'];
if(isset($_POST['method']) && $userID != ''){
    $action = $_POST['method'];
    $actionBack = $_SERVER['HTTP_REFERER'];
    if($action == 'move_location'){
        $locationOld = $_POST['locationOldID'];
        $locationNew = $_POST['locationNewID'];
        $qty = $_POST['qty'];
        foreach($_POST['inKey'] as $key => $val){
             // select zone id
            $db->join('tb_zone zone','zone.zone_id = address.zone_id');
            $sqlZone = $db->get_where('tb_address address','address.add_id = '.$locationNew);
            $rsZone = $sqlZone->row_array();

            $db->select('po.po_id,po.ibp_id,il.*');
            $db->join('inbound_po po','po.inbound_id = il.inbound_id');
            $sqlRemain = $db->get_where('inbound_location il',array('il.inbound_id'=>$val,'il.location_id'=>$locationOld));
            $rsRemain = $sqlRemain->row_array();
            $where = array(
                'inbound_id' => $val,
                'location_id' => $locationOld
            );
            $data = array(
                'qty_remain' => $rsRemain['qty_remain'] - $qty[$val],
                'action_status' => 3,
                'user_id_update' => $userID,
                'update_time' => _DATE_TIME_
            );
            $db->edit('inbound_location',$data,$where);
            $dataIn = array(
                'inbound_id' => $val,
                'barcode' => $rsRemain['barcode'],
                'location_id' => $locationNew,
                'qty' => $qty[$val],
                'qty_remain' => $qty[$val],
                'time' => _DATE_TIME_,
                'user_id' => $userID,
                'action_status' => 3
            );
            $db->insert('inbound_location',$dataIn);
            $dataMovement = array(
                'barcode' => $rsRemain['barcode'],
                'location_id' => $locationNew,
                'zone_id' => $rsZone['zone_id'],
                'doc_no' => $rsRemain['ibp_id'],
                'qty' => $qty[$val],
                'action_status' => 3,
                'create_time' => _DATE_TIME_,
                'user_id' => $userID
            );
            $db->insert('product_movement',$dataMovement);
            //echo $db->last_query();
            $dataMove = array(
                'inbound_key' => $val,
                'barcode' => $rsRemain['barcode'],
                'location_id_old' => $locationOld,
                'location_id_new' => $locationNew,
                'move_qty' => $qty[$val],
                'create_time' => _DATE_TIME_,
                'user_id' => $userID
            );
            $db->insert('move_product',$dataMove);
            //echo $val;//inbound_key
            //echo $qty[$val];
        }
    }
}
header('location:'.$actionBack);
?>