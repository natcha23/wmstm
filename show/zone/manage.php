<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();
$userID = $_SESSION['userID'];
if(isset($_POST['actionType']) && $_SESSION['userID'] != ''){
    $action = $_POST['actionType'];
    if($action == 'addZone' || $action == 'updateZone' || $action == 'delZone'){//---------------- ZONE
        if($action == 'addZone'){
            $name = $_POST['nameZone'];
            $note = $_POST['note'];
            $data = array(
                'zone_name' => $name,
                'note' => $note,
                'user_create' => $userID,
                'create_time' => _DATE_TIME_
            );
            $db->insert('tb_zone',$data);
            echo $db->last_query();
        }else if($action == 'updateZone'){
            if(isset($_POST['zoneID']) && !empty($_POST['zoneID'])){
                $name = $_POST['nameZone'];
                $note = $_POST['note'];
                $zoneID = $_POST['zoneID'];
                $data = array(
                    'zone_name' => $name,
                    'note' => $note,
                    'user_update' => $userID,
                    'update_time' => _DATE_TIME_
                );
                $where = array(
                    'zone_id' => $zoneID
                );
                $db->edit('tb_zone',$data,$where);
                //echo $db->last_query();
            }
        }else if($action == 'delZone'){
            if(isset($_POST['zoneID']) && !empty($_POST['zoneID'])){
                $zoneID = $_POST['zoneID'];
                $row = 0;
                if($row == 0){
                    echo 1;
                }else if($row > 0){
                    echo 2;
                }
            }else{
                echo 2;
            }
        }
    }
    //----------------------------------------------------   ROW    --------------------------------------
    else if($action == 'addRow' || $action == 'updateRow' || $action == 'delRow'){//-------------------------  ROW  ------------------
        $zoneID = $_POST['inputZone'];
        $nameRow = $_POST['nameRow'];
        $note = $_POST['note'];
        $rowID = $_POST['rowID'];
        if($action == 'addRow'){
            $data = array(
                'row_name' => $nameRow,
                'zone_id' => $zoneID,
                'note' => $note,
                'user_create' => $userID,
                'create_time' => _DATE_TIME_
            );
            if($db->insert('location_row',$data)){// ถ้ามีการ Insert จะทำการ สร้าง ตาราง ชั้นวางโดยอัตโนมัติ ตามจำนวน ที่ตั้งค่าไว้ตอนสร้าง zone
                $insertID = $db->insert_id();
                $sqlZone = $db->get_where('location_zone',array('zone_id'=>$zoneID));
                $rsZone = $sqlZone->row_array();
                $rowID = $insertID;
                $xMax = $rsZone['num_column'];
                $yMax = $rsZone['num_row'];
                for($x = 1 ; $x <= $xMax ; $x++){
                    for($y = 1 ; $y <= $yMax ; $y++){
                        $data = array(
                            'shelf_column' => $x,
                            'shelf_row' => $y,
                            'row_id' => $rowID
                        );
                        $db->insert('location_shelf',$data);
                    }
                }
            }
        }else if($action == 'updateRow'){
            $nameRow = $_POST['nameRow'];
            $note = $_POST['note'];
            $data = array(
                'row_name' => $nameRow,
                'note' => $note,
                'user_update' => $userID,
                'update_time' => _DATE_TIME_
            );
            $where = array(
                'row_id' => $rowID
            );
            $db->edit('location_row',$data,$where);
        }
    }else if($action == 'shelfUpdate' && !empty($_POST['shelfEditID'])){
        $shelfID = $_POST['shelfEditID'];
        $name = $_POST['inputShelfName'];
        $note = $_POST['note'];
        $shelfStatus = $_POST['inputShelfStatus'];
        if($action == 'shelfUpdate'){
            $data = array(
                'shelf_name' => $name,
                'note' => $note,
                'shelf_status' => $shelfStatus,
                'user_update' => $userID,
                'update_time' => _DATE_TIME_
            );
            $where = array(
                'shelf_id' => $shelfID
            );
            $db->edit('location_shelf',$data,$where);
            //echo $db->last_query();
        }
    }else if($action == 'shelfStatusUpdate' && !empty($_POST['statusUpdate']) && count($_POST['shelfID'])>0 ){
        $data = array(
            'shelf_status' => $_POST['statusUpdate'],
            'user_update' => $userID,
            'update_time' => _DATE_TIME_
        );
        $db->where_in('shelf_id',$_POST['shelfID']);
        $db->update('location_shelf',$data);
    }
}
header('location:'.$_SERVER['HTTP_REFERER']);
?>
