<?php
require_once('../config.php');
require_once('../class_my.php');
require_once('../func.php');
$db = DB();
$userID = $_SESSION['userID'];
if(isset($_POST['method']) && $userID != ''){
    $action = $_POST['method'];
    $actionBack = $_SERVER['HTTP_REFERER'];
    if($action == 'put_pallet_in_location'){
        $palletID = $_POST['palletID'];
        $locationID = $_POST['locationID'];

        // select zone id
        $db->join('tb_zone zone','zone.zone_id = address.zone_id');
        $sqlZone = $db->get_where('tb_address address','address.add_id = '.$locationID);
        $rsZone = $sqlZone->row_array();

        // select inbound_pallet_item
        $db->select('ipt.*,po.product_no,po.po_id,po.ibp_id');
        $db->join('inbound_po po','po.inbound_id = ipt.inbound_key');
        $sqlGetItem = $db->get_where('inbound_pallet_item ipt',array('ipt.pallet_id'=>$palletID));
        foreach($sqlGetItem->result_array() as $arr){
            $inboundKey = $arr['inbound_key'];
            $dataIn = array(
                'inbound_id' => $inboundKey,
                'barcode' => $arr['product_no'],
                'location_id' => $locationID,
                'qty' => $arr['qty'],
                'qty_remain' => $arr['qty'],
                'time' => _DATE_TIME_,
                'user_id' => $userID,
                'action_status' => 1
            );
            $db->insert('inbound_location',$dataIn);
            if($db->affected_rows()>0){
                $dataMovement = array(
                    'barcode' => $arr['product_no'],
                    'location_id' => $locationID,
                    'zone_id' => $rsZone['zone_id'],
                    'doc_no' => $arr['ibp_id'],
                    'qty' => $arr['qty'],
                    'action_status' => 1,
                    'create_time' => _DATE_TIME_,
                    'user_id' => $userID
                );
                $db->insert('product_movement',$dataMovement);
                //ยืนยันสินค้าในพาเลทนี้เก็บเข้า location แล้ว
                $db->update('inbound_pallet_item',array('status'=>1),array('inbound_key' => $inboundKey,'pallet_id' => $palletID));

                $db->select('product_qty,po_id');
                $sqlGetIn = $db->get_where('inbound_po',array('inbound_id'=>$inboundKey));
                $rsGetIn = $sqlGetIn->row_array();// ดึงจำนวนที่รับเข้า
                $db->select('SUM(qty) as qty');
                $sqlNumIn = $db->get_where('inbound_location',array('inbound_id'=>$inboundKey));
                $rsNumIn = $sqlNumIn->row_array();// ดึงจำนวนสินค้านั้นๆ ของ po นั้นๆ ที่ได้นำเข้าตำแหน่งแล้ว
                //เช็คจำนวน ที่รับเข้า กับ จำนวนที่เก็บในคลัง เท่ากันหรือไม่ เพื่อ ยืนยันสถานะว่าเก็บเข้าคลังเรียบร้อย
                if($rsGetIn['product_qty'] == $rsNumIn['qty'] && $rsGetIn['product_qty'] != 0){
                    //เปลี่ยน status เก็บเข้าที่แล้ว
                    $db->edit('inbound_po',array('dateupdate'=>_DATE_TIME_,'po_status'=>2,'user_update'=>$userID),array('inbound_id'=>$inboundKey));
                    //echo 'udate inbound_po status = 2 ]]]]'.$db->last_query();
                    checkStatusInbound($rsGetIn['po_id']);
                }
            }
        }
        checkPalletStatus($palletID);
    }
}
header('location:'.$actionBack);

function checkPalletStatus($palletID){
    $db = DB();
    $sqlGetItem = $db->get_where('inbound_pallet_item',array('pallet_id'=>$palletID , 'status !=' => 1));
    $rsStatus = $sqlGetItem->num_rows();
    if($rsStatus ==  0){
        $db->update('inbound_pallet',array('pallet_status'=>2,'put_time'=>_DATE_TIME_,'user_put'=>$userID),array('pallet_id' => $palletID));
    }
}
//เช็ค รายการใน ใบ po ว่ามีการนำเข้าทั้งหมดแล้วหรือยัง เพื่อ ยืนสถานะ po น้น เก็บของทั้งหมดแล้ว เปลี่ยนสถานะ เก็บของเรียบร้อย
function checkStatusInbound($poID){
    $db = DB();
    $sql = $db->get_where('inbound_po',array('po_id'=>$poID,'po_status'=>1));
    $num = $sql->num_rows();
    if($num == 0){
        $db->edit('inbound_status',array('status'=>2,'time_in_stock'=>_DATE_TIME_),array('inbound_id'=>$poID));
    }
}
?>