<?php
require_once('../config.php');
require_once('../class_my.php');
require_once('../func.php');
$db = DB();
// ยังไม่ได้เช็ค ว่า id ที่อยู่ถูกต้อหรือไม่
//$userID = $_SESSION['userID'];
$userID = $_SESSION['userID'];
if(isset($_POST['actionType']) && $userID != ''){
    $action = $_POST['actionType'];
    if($action == 'addLocation'){
        $inboundID = $_POST['inboundID'];
        $actionBack = $_POST['actionBack'];
        $productNo = $_POST['productNo'];
        $productName = $_POST['productName'];
        foreach($_POST['locationID'] as $key => $val){
            $sqlLocationID = $db->get_where('tb_address',array('add_name'=>$val));
            $rsLocationID = $sqlLocationID->row_array();
            $locationID = $rsLocationID['add_id'];
            $qty = $_POST['numIn'][$key];
            $data = array(
                'inbound_id' => $inboundID,
                'location_id' => $locationID,
                'qty' => $qty,
                'time' => _DATE_TIME_,
                'user_id' => $userID
            );
            $db->insert('inbound_location',$data);
            $sqlNumStock = $db->get_where('stock_product',array('product_id'=>$productNo));
            //echo $db->last_query();
            $numStock = $sqlNumStock->num_rows();

            if($numStock > 0){// หากมีรายการแล้ว
                $rsStock = $sqlNumStock->row_array();
                $qtySum = $qty+$rsStock['product_qty'];
                echo $qtySum;
                $dataStock=array(
                    'product_qty' => $qtySum
                );
                $whereStock = array(
                    'product_id'=>$productNo
                );
                $db->edit('location_zone',$dataStock,$whereStock);
            }else if($numStock == 0){ //หากยังไม่มีรายการ
                $dataStock = array(
                    'product_id' => $productNo,
                    'product_name' => $productName,
                    'product_unit' => $productUnit,
                    'product_qty' => $qty
                );
                $db->insert('stock_product',$dataStock);
            }
            $db->update('inbound_po',array('product_date_in'=>_DATE_TIME_,'po_status'=>1),array('inbound_id'=>$inboundID));
        }
    }else if($action == 'stayBranch'){
        if($_POST['branchID'] != '' && $_POST['carID'] != ''){
            $actionBack = $_SERVER['HTTP_REFERER'];
            $branchID = $_POST['branchID'];
            $carID = $_POST['carID'];
            $db->select('ors.rt_id');
            $db->join('outbound_car oc','oc.outbound_rt = ors.rt_id');
            $sql = $db->get_where('outbound_rt_status ors',array('ors.rt_branch'=>$branchID,'ors.status'=>5,'oc.car_id'=>$carID,'oc.status_process'=>1));
            echo $db->last_query();
            foreach($sql->result_array() as $rs){
                $db->update('outbound_car',array('status_process'=>2),array('car_id'=>$carID,'outbound_rt'=>$rs['rt_id']));
                $db->update('outbound_rt_status',array('status'=>6),array('rt_branch'=>$branchID,'rt_id'=>$rs['rt_id']));
            }
        }
    }
    header('location:'.$actionBack);
}
?>