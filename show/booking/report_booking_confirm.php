<?php
$date1 = str_replace('-', '/', date('Y-m-d'));
$dateConfirm = date('Y-m-d',strtotime($date1 . "+1 days"));
$db->select('ip.po_id,ip.po_supplier,ib.booking_date,ib.booking_status');
$db->join('inbound_po ip','ip.po_id = ib.po_id');
$db->group_by('ip.po_id');
$sqlB = $db->get_where('inbound_booking ib',array('date(ib.booking_date)'=>$dateConfirm));
?>
<div class="ibox-title">
    <table id="table-booking" class="table table-striped table-bordered table-hover dataTable">
        <thead>
            <tr>
                <th>PO Reference No.</th>
                <th>Supplier</th>
                <th>วันที่จองส่งสินค้า</th>
                <th>ยืนยัน</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($sqlB->result_array() as $rsB){?>
            <tr>
                <td><?php echo $rsB['po_id'];?></td>
                <td><?php echo $rsB['po_supplier'];?></td>
                <td><?php echo $rsB['booking_date'];?></td>
                <td>
                    <?php
                        $select = '';
                        if($rsB['booking_status'] == 1){
                            $color = 'green';
                            $select = 'ยืนยันส่งสินค้า';
                        }elseif ($rsB['booking_status'] == 2) {
                            $color = 'orange';
                            $select = 'ไม่ยืนยัน';
                        }else{
                            $color = 'red';
                            $select = 'ยังไม่ได้ดำเนินการ';
                        }
                        echo "<h5 style='color:$color'>$select</h5>";
                    ?>
                </td>
            </tr>
            <?php
            }?>

        </tbody>
    </table>
</div>
<script>
var oTable = $('#table-booking').dataTable({
    "pageLength": 50,
});
</script>