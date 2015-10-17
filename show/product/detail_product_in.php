<?php
$product_id = isset($_GET['product_id'])?$_GET['product_id']:'';
$db->order_by('product_date_in DESC');
$sqlIn = $db->get_where('inbound_po',array('product_no'=>$product_id));
$rsName = $sqlIn->row_array();
?>
<div><button onclick="javascript:history.back();">ย้อนกลับ </button></div>
<div class="ibox-title">
    <?php echo 'รหัสสินค้า : '.$product_id.' ชื่อสินค้า : '.$rsName['product_name'];?>
<table border="1" id="table-product" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>ใบรายการ</th>
                <th>ผู้ผลิต/สาขา</th>
                <th>วันที่นำเข้า/วันที่นำออก</th>
                <th>จำนวน</th>
                <th>หน่วย</th>
                <th>สถานที่เก็บ/สถานที่ดึงออก</th>
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
                <td><?php echo getInboundLocation($rsIn['inbound_id'])?></td>
                <td>

                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>

</div>