<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();
$user_id = $_SESSION['userID'];
//header('Content-Type: text/html; charset=utf-8');
if($_POST['method'] != '' && isset($_POST['dataGet']) && $_POST['dataGet'] != ''){
    $method = $_POST['method'];
    if($method == 'dateImport'){
        $html = getHtml('http://tmintranet.dyndns.org/service.php?service_type=listibp&ibpdate='.$_POST['dataGet']);
    }else if($method == 'poImport'){
        $html = getHtml('http://tmintranet.dyndns.org/service.php?service_type=listibpbypo&poid='.$_POST['dataGet']);
    }else if($method == 'ibpImport'){
        $html = getHtml('http://tmintranet.dyndns.org/service.php?service_type=listibpbyibp&ibp='.$_POST['dataGet']);
    }
    $data = json_decode($html);
    $data = objectToArray($data);
        if(!empty($data)){
            foreach($data as $key => $val){
                $mainPO[] = $val['PO_ID'];
            }

            //$test = array_unique($mainPO, SORT_REGULAR);
            //echo count($test);
            //print_r($test);
            foreach($data as $array){
                if($array['cstatus'] == 0 && $array['istatus'] == 0){
                    $sqlIbp = $db->get_where('inbound_po',array('po_id'=>$array['PO_ID'],'product_no'=>$array['GOODCODE_PO'],'ibp_id'=>''));
                    $rowIbp = $sqlIbp->num_rows();
                    //echo $db->last_query();
                    if($rowIbp == 1){
                        $dataUpdatePO = array(
                            'ibp_id' => $array['DOC_IBP'],
                            'product_qty' =>  (int)$array['PO_QTY'],
                            'po_status' => 1,
                            'free_qty' => (int)$array['IBP_QTY_FEE'],
                            'product_date_in' => _DATE_TIME_,
                            'user_update' => $user_id,
                            'dateupdate' => _DATE_TIME_
                        );
                        $sqlEditPo = $db->edit('inbound_po',$dataUpdatePO,array('po_id'=>$array['PO_ID'],'product_no'=>$array['GOODCODE_PO']));
                        //echo $db->last_query();
                        insert_tb_stock_product($array['GOODCODE_PO'],$array['GOOD_IBP_ALIAS'],$array['PO_UTQ'],(int)$array['PO_QTY'],$user_id);
                        checkStatusInbound($array['PO_ID'],$array['DOC_IBP']);
                        $success = 1;
                    }else{
						$sqlIbp = $db->get_where('inbound_po',array('po_id'=>$array['PO_ID'],'product_no'=>$array['GOODCODE_PO'],'ibp_id !='=>$array['DOC_IBP']));
						$rowIbp = $sqlIbp->num_rows();
						$value = $sqlIbp->row_array();
						if($rowIbp > 0){
							$inbound_po_fields = array(
							'po_id' =>  $value['po_id'],
							'ibp_id' => $array['DOC_IBP'],
							'receipt_type' => $value['receipt_type'],
							'po_create' => $value['po_create'],
							'po_delivery_date' => $value['po_delivery_date'],
							'po_supplier' => $value['po_supplier'],
							'product_no' => $value['product_no'],
							'product_name' => $value['product_name'],
							'cat' => $value['cat'],
							'order_qty' => $value['order_qty'],
							'product_qty' => (int)$array['PO_QTY'],
							'free_qty' => (int)$array['IBP_QTY_FEE'],
							'product_unit' => $value['product_unit'],
							'product_date_in' => _DATE_TIME_,
							'product_create_date' => $value['po_create'],
							'product_fefo' => $value['product_fefo'],
							'product_fefo_date' => $value['product_fefo_date'],
							'user_create' => $value['user_create'],
							'user_update' => $user_id,
							'note' => $value['note'],
							'po_status' => 1,
							'datecreate' => $value['datecreate'],
							'dateupdate' => _DATE_TIME_,
							'status' => (strtolower($value[12])=='active')?0:1
							);
							$db->insert('inbound_po',$inbound_po_fields);
							insert_tb_stock_product($array['GOODCODE_PO'],$array['GOOD_IBP_ALIAS'],$array['PO_UTQ'],(int)$array['PO_QTY'],$user_id);

							$sqlChkStatus = $db->get_where('inbound_status',array('inbound_id'=> $value['po_id'],'ibp_id'=>$array['DOC_IBP']));
							$rowChkStatus = $sqlChkStatus->num_rows();
							$inbound_status_fields = array(
								'inbound_id' => $value['po_id'],
								'ibp_id' => $array['DOC_IBP'],
								'start_date' => _DATE_TIME_,
								'status' => 1,
								'time_get_product' => _DATE_TIME_,
								'time_in_stock' => 0
							);
							$db->insert('inbound_status',$inbound_status_fields);
							$success = 1;
						}
					}
                }
            //print_r($array);
            //echo '<hr>';
            }
        if($success === 1){
            echo 'บันทึกข้อมูลเรียบร้อย';
        }
    }else{
        echo "ไม่พบข้อมูล";
    }
}else{
    echo "ไม่พบข้อมูล";
}

function checkStatusInbound($poID,$ibpID){
    $db = DB();
    $sql = $db->get_where('inbound_po',array('po_id'=>$poID,'ibp_id'=>$ibpID,'po_status'=>0));
    $num = $sql->num_rows();
    if($num == 0){
        $db->edit('inbound_status',array('status'=>1,'time_get_product'=>_DATE_TIME_,'start_date'=>_DATE_TIME_,'ibp_id'=>$ibpID),array('inbound_id'=>$poID));
        $db->update('inbound_booking',array('ibp_id'=>$ibpID,'delivery_status'=>0,'delivery_date'=>_DATE_TIME_,'date_update'=>_DATE_TIME_,'user_id'=>$user_id),array('po_id'=>$poID));
    }
}

function insert_tb_stock_product($productNo,$productName,$productUnit,$qty,$userID){
    $db = DB();
    $sqlNumStock = $db->get_where('stock_product',array('product_id'=>$productNo));
    //echo $db->last_query();
    $numStock = $sqlNumStock->num_rows();
    if($numStock > 0){// หากมีรายการแล้ว
        $rsStock = $sqlNumStock->row_array();
        $qtySum = $qty+$rsStock['product_qty'];
        $dataLog = array(
            'product_id' => $rsStock['product_id'],
            'product_qty' => $rsStock['product_qty'],
            'stock_update' => $rsStock['product_update'],
            'user_id' => $rsStock['user_id']
        );
        $db->insert('stock_product_log',$dataLog);
        $dataStock = array(
            'product_qty' => $qtySum,
            'product_update' => _DATE_TIME_,
            'user_id' => $userID
        );
        $whereStock = array(
            'product_id'=>$productNo
        );
        $db->update('stock_product',$dataStock,$whereStock);
    }else if($numStock == 0){ //หากยังไม่มีรายการ
        $dataStock = array(
            'product_id' => $productNo,
            'product_name' => $productName,
            'product_unit' => $productUnit,
            'product_qty' => $qty,
            'product_update' => _DATE_TIME_,
            'user_id' => $userID
        );
        $db->insert('stock_product',$dataStock);
        //echo $db->last_query();
    }
}

function getHtml($url, $post = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if(!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function objectToArray($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}
?>