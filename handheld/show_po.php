<?php
$date_delivery = isset($_GET['date_delivery'])?$_GET['date_delivery']:date( "Y-m-d");
$db->distinct();
$db->select('in_po.po_id,in_po.po_create,in_st.status');
$db->join('inbound_po in_po','in_po.po_id = in_st.inbound_id');
$db->where(array('in_po.po_delivery_date'=>$date_delivery));
$sqlPo = $db->get('inbound_status in_st');
echo $db->last_query();
?>
<a href="index.php" border='1'>หน้าเมนู</a><br/>

<form action='?page=show_po' method="GET">
    <input type="hidden" name="page" value='show_po'/>
	<input name="date_delivery" value="<?php echo $date_delivery?>"/>
	<input type="submit" value="ค้นหา"/>
</form>
กำหนดส่ง : <?php echo $date_delivery; ?>
<table id="table-inbound-po" border="1">
<thead>
	<tr>
		<th>#</th>
		<th>Date</th>
		<th>PO Reference No.</th>
		<th>Action</th>
	</tr>
</thead>
<tbody>
<?php
    $n = 1;
	foreach($sqlPo->result_array() as $arr){
?>
	<tr <?php if($n%2==0){echo 'style=background-color:#D8D8D8';}?> >
		<td><?php echo $n; ?></td>
		<td><?php echo shortDate($arr['po_create']); ?></td>
		<td><?php echo $arr['po_id']; ?></td>
		<td>
            <?php
            if($arr['status'] == 1){
            ?>
                <a href="?page=show_item&po_id=<?php echo $arr['po_id']; ?>" class="btn btn-xs btn-primary">เลือก</a>
            <?php
            }else{
                echo 'เก็บสินค้าเรียบร้อย';
            }
            ?>

        </td>
	</tr>
<?php
    $n++;
	}
?>
</tbody>
</table>
