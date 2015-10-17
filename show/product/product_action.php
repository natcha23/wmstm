<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();
    if(isset($_POST['method'])&& $_POST['method']!=''){
        $method = $_POST['method'];
        if($method == 'editExpProduct'){
            $productKey = $_POST['productKey'];
            $expProduct = $_POST['numExp'];
            $data = array(
                'num_exp'=>$expProduct
            );
            $where = array(
                'id'=>$productKey
            );
            $sql = $db->update('stock_product',$data,$where);
            if($db->affected_rows() == '1'){
                $return['success'] = 'true';
                echo json_encode($return);
            }
        }else if($method == 'editMaxProduct'){
            $productKey = $_POST['productKey'];
            $numMax = $_POST['numMax'];
            $data = array(
                'qty_max'=>$numMax
            );
            $where = array(
                'id'=>$productKey
            );
            $sql = $db->update('stock_product',$data,$where);
            if($db->affected_rows() == '1'){
                $return['success'] = 'true';
                echo json_encode($return);
            }
        }
    }
?>
