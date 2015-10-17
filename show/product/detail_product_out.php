<?php
$product_id = isset($_GET['product_id'])?$_GET['product_id']:'';
$db->select('oil.id,oil.user_id,oil.date_out,ort.rt_refid,oil.qty,tba.add_name,ors.rt_branch,ort.unit,oil.outbound_id,ort.goods_name');
$db->order_by('oil.date_out DESC');
$db->join('outbound_rt_status ors','ors.rt_id = oil.outbound_id');
$db->join('tb_address tba','tba.add_id = oil.location_id');
$db->join('outbound_rt ort','ort.id = oil.outbound_id');
$sqlIn = $db->get_where('outbound_items_location oil',array('ort.barcode'=>$product_id));
$rsName = $sqlIn->row_array();
//echo $db->last_query();
?>
<div><button onclick="javascript:history.back();">ย้อนกลับ </button></div>
<div class="ibox-title">
    <?php echo 'รหัสสินค้า : '.$product_id.' ชื่อสินค้า : '.$rsName['goods_name'];?>
<table border="1" id="table-product" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>ใบรายการ</th>
                <th>สาขา</th>
                <th>วันที่นำออก</th>
                <th>จำนวน</th>
                <th>หน่วย</th>
                <th>สถานที่เก็บ/สถานที่ดึงออก</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($sqlIn->result_array() as $rsIn){ ?>
            <tr>
                <td><?php echo $rsIn['rt_refid']?></td>
                <td><?php echo $rsIn['rt_branch']?></td>
                <td><?php echo $rsIn['date_out']?></td>
                <td><?php echo $rsIn['qty']?></td>
                <td><?php echo $rsIn['unit']?></td>
                <td><?php echo getInboundLocation($rsIn['inbound_id'])?></td>
                <td>

                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>

</div>