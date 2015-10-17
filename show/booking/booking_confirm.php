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
                        $select1 = '';
                        $select2 = '';
                        if($rsB['booking_status'] == 1){
                            $select1 = 'selected';
                        }elseif ($rsB['booking_status'] == 2) {
                            $select2 = 'selected';
                        }
                    ?>
                    <select id="confirm" class="form-control">
                        <option value="0">ยังไม่ได้ดำเนินการ</option>
                        <option value="1" <?php echo $select1;?>>ยืนยัน</option>
                        <option value="2" <?php echo $select2;?>>ไม่ยืนยัน</option>
                    </select>
                    <button onclick="$.confirmBooking('<?php echo $rsB['po_id'];?>');" class="btn btn-primary ">บันทึก</button>
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
$(function(){
    $.confirmBooking = function(poID){
        var data = new Array();
        data.push({name:'poID', value:poID});
        data.push({name:'confirm', value:$('#confirm').val()});
        data.push({name:'method',value:'booking_confirm_status'});
        $.post('show/booking/booking_action.php',data,function(html){
            if(html.status == 1){
                alert('บันทึกข้อมูลเรียบร้อย');
            }
        },'json');
    }
});
</script>