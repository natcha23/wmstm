<?php
if($_GET['poID']){
    $poID = $_GET['poID'];
}
$sqlItem = $db->get_where('inbound_po',array('po_id'=>$poID));
$sqlStatus = $db->get_where('inbound_status',array('inbound_id'=>$poID));
$numStatus = $sqlStatus->num_rows();
if($numStatus != 0){
    $rsStatus = $sqlStatus->row_array();
}
?>
<form id="formStatus" action="show/inbound/inbound_action.php" method="post">
    <input type="hidden" name="poID" value="<?php echo $poID;?>"/>
    <input type="hidden" name="method" value="start_inbound"/>
</form>
<div class="ibox-title">
    <button onclick="history.back();">ย้อนกลับ</button>
    <?php if($numStatus == 0){?>
        <button type="button" class="btn btn-w-m btn-primary" style="float:right;" onclick="startInbound();">เริ่มดำเนินการ</button>
    <?php
    }else if(isset($rsStatus['status'])){
        getStatus_PO($rsStatus['status']);
    }
    ?>
    <p></p>
<table id="table-inbound" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>#</th>
		<th>กำหนดส่งสินค้า</th>
		<th>PO Reference No.</th>
		<th>Receipt Type</th>
		<th>Supplier No.</th>
		<!-- <th>Supplier name</th> -->
		<th>Barcode</th>
		<th>Name</th>
        <?php if($rsStatus['status']!= 1){?>
            <th>จำนวนที่สั่ง</th>
        <?php } ?>
        <?php if($rsStatus['status'] != 0){?>
		<th>QTY</th>
        <?php } ?>
		<th>Unit</th>
        <?php if($numStatus != 0 && $rsStatus['status']== 2){?>
            <th>Location</th>
        <?php
        }
        if($numStatus != 0 && $rsStatus['status']!= 0){?>
            <th style="width:150px">วันหมดอายุ</th>
        <?php } ?>
	</tr>
</thead>
<tbody>

<?php
$n=1;
foreach($sqlItem->result_array() as $rsItem){
    $product_fefo_date = '';
    $product_create_date = '';
    $product_fefo = $rsItem['product_fefo'];
    $row_id = $rsItem['inbound_id'];
    if($rsItem['product_fefo'] == 1){
        $product_fefo_date = $rsItem['product_fefo_date'];
        if($rsItem['product_create_date'] != "0000-00-00"){
            $product_create_date = $rsItem['product_create_date'];
        }
    }
    $numExp = '';
    //$db->select('num_exp');
    $sqlExpSetting = $db->get_where('stock_product',array('product_id'=>$rsItem['product_no']));
    //echo $db->last_query();
    $rsExp = $sqlExpSetting->row_array();
    if($rsExp['num_exp']!= NULL){
        $numExp = $rsExp['num_exp'];
    }
?>
	<tr>
		<td><?php echo $n; ?></td>
		<td><?php echo $rsItem['po_delivery_date'];?></td>
		<td><?php echo $rsItem['po_id']; ?></td>
		<td><?php echo $rsItem['receipt_type']; ?></td>
		<td><?php echo $rsItem['po_supplier']; ?></td>
		<!-- <td><?php echo $rsItem['product_no']; ?></td> -->
		<td style="color:red;font-weight:bold"><?php echo $rsItem['product_no']; ?></td>
		<td><?php echo $rsItem['product_name']; ?></td>
        <?php if($rsStatus['status']!= 1){?>
        <td style="color:red;font-weight:bold;text-align:right"><?php echo number_format($rsItem['order_qty']); ?></td>
        <?php }
        if($rsStatus['status'] != 0){
        ?>
        <td>
            <?php echo number_format($rsItem['product_qty']); ?>
        </td>
        <?php } ?>
		<td><?php echo $rsItem['product_unit']; ?></td>
        <?php if($numStatus != 0 && $rsItem['po_status']!= 0){?>
		<td>
            <?php
            if($rsItem['po_status'] == '2'){
                $sqlLocation = $db->get_where('inbound_location',array('inbound_id'=>$rsItem['inbound_id'],'action_status'=>1));
                foreach($sqlLocation->result_array() as $rsLocation){
                    echo getNameLocation($rsLocation['location_id']).'('.$rsLocation['qty'].')</br>';
                }
            }
            ?>
		</td>
		<td>
            <?php
            if($product_create_date){echo 'ผลิต : ' .$product_create_date.'<br/>'; }
            if($product_fefo_date){echo 'หมดอายุ : ' .$product_fefo_date.'<br/>'; }
            $remain = date_sum_remain($product_fefo_date);
            if($remain){echo 'คงเหลือ : ' .$remain.'วัน'; }
            ?>
            </span>
		</td>
        <?php } ?>
	</tr>
<?php
$n++;
}
?>
</tbody>
</table>

</div>
<?php // echo $count_po; ?>
<script>
$(function(){
    //-------------------------------------------------------------------------------
	var oTable = $('#table-inbound').dataTable({
		"pageLength": 100,
	});
	$('.create_datepicker').datepicker({
	 	format: 'yyyy-mm-dd',
        autoclose:true
	}).on('changeDate',function(e){
        var id = $(this).attr('rel');
        $.changeCreateExp(this.value,$('#numExp'+id).val(),id);
    });
    $('.fefo_datepicker').datepicker({
	 	format: 'yyyy-mm-dd',
        autoclose:true
	}).on('changeDate',function(e){
        var id = $(this).attr('rel');
        var dateExp = $(this).val();
        $.changeCreateExp('','',id,dateExp);
    });

	var search = $('#table-inbound_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

//------------------------------------------------------------------------------------------------------
	$.checkFefo = function(obj){
		var id = obj.value;
		if($(obj).is(':checked')){
			$('#div_fefo_'+id).show();
		}else{
			$('#div_fefo_'+id).hide();
		}
	}
});
//-------------------------------------------------------------------------------------
function startInbound(){
    if(confirm('คุณต้องการที่จะดำเนินการนี้หรือไม่')){
        $('#formStatus').submit();
    }
}
</script>