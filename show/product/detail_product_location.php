<?php
$product_id = isset($_GET['product_id'])?$_GET['product_id']:'';
$db->order_by('product_date_in DESC');
$sqlIn = $db->get_where('inbound_po',array('product_no'=>$product_id));
?>
<div class="ibox-title">
    <?php echo 'รหัสสินค้า : '.$product_id.' ชื่อสินค้า : '?>
<table border="1" id="table-product" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>PO</th>
                <th>ผู้ผลิต</th>
                <th>วันที่นำเข้า</th>
                <th>จำนวน</th>
                <th>หน่วย</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($sqlIn->result_array() as $rsIn){ ?>
            <tr>
                <td><?php echo $rsIn['po_id']?></td>
                <td><?php echo $rsIn['po_supplier']?></td>
                <td><?php echo $rsIn['product_date_in']?></td>
                <td><?php echo $rsIn['product_qty']?></td>
                <td><?php echo $rsIn['product_unit']?></td>
                <td>
                    <a href="?page=detail_product_in?product_id=<?php echo $rsIn['product_id'];?>">รายละเอียดการนำเข้า</a>| |
                    <a href="?page=detail_product_location?product_id=<?php echo $rsIn['product_id'];?>">สถานที่เก็บ</a>
                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>

</div>