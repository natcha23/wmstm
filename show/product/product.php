<?php
$sqlProduct = $db->get('stock_product');
?>
<div class="ibox-title">
    <table border="1" id="table-product" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Barcode</th>
                <th>ชื่อ</th>
                <th>จำนวน</th>
                <th>หน่วย</th>
                <!--<th>จำนวนอายุ/วัน</th>
                <th>จำนวนจำกัด</th>-->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($sqlProduct->result_array() as $rsProduct){
                $numExp = '';
                $qtyMax = '';
                if($rsProduct['num_exp'] != NULL){
                    $numExp = $rsProduct['num_exp'];
                }
                if($rsProduct['qty_max'] != NULL){
                    $qtyMax = $rsProduct['qty_max'];
                }
                $productKey = $rsProduct['id'];
            ?>
            <tr>
                <td><?php echo $rsProduct['product_id']?></td>
                <td><?php echo $rsProduct['product_name']?></td>
                <td><?php echo $rsProduct['product_qty']?></td>
                <td><?php echo $rsProduct['product_unit']?></td>
                <!--<td>
                    <input id="expProduct<?php echo $productKey;?>" type="text" placeholder="อายุสินค้า" class="form-control" size='4' value="<?php echo $numExp; ?>" />
                    <button onclick="$.expProduct('<?php echo $productKey;?>')">บันทึก</button>
                </td>
                <td>
                    <input id="maxProduct<?php echo $productKey;?>" type="text" placeholder="จำนวนสูงสุด" class="form-control" size='4' value="<?php echo $qtyMax; ?>" />
                    <button onclick="$.maxProduct('<?php echo $productKey;?>');">บันทึก</button></td>-->
                <td>
                    [<a href="?page=detail_product_in&product_id=<?php echo $rsProduct['product_id'];?>">รายละเอียดการนำเข้า</a>] \ \
                    [<a href="?page=detail_product_out&product_id=<?php echo $rsProduct['product_id'];?>">รายละเอียดการนำออก</a>]
                </td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<script>
$(function(){
	$('#nav-product').parent().addClass('active');
	$('#nav-product').addClass('in');
	$('#product').addClass('active');
	
	var oTable = $('#table-product').dataTable({
		"pageLength": 50,
	});
    $.expProduct = function(id){
        var numExp = $('#expProduct'+id).val();
        var data = new Array();
        data.push({name:'numExp',value:numExp});
        data.push({name:'productKey',value:id});
        data.push({name:'method',value:'editExpProduct'});
        if(confirm('ต้องการที่จะเปลี่ยนค่าใช่หรือไม่') === true){
            $.post('<?php echo _BASE_URL_;?>show/product/product_action.php',data,function(html){
                if(html.success === 'true'){
                    $.loader('save');
                    $.unloader('save');
                }
            },'json');
        }
    }
    $.maxProduct = function(id){
        var numMax = $('#maxProduct'+id).val();
        var data = new Array();
        data.push({name:'numMax',value:numMax});
        data.push({name:'productKey',value:id});
        data.push({name:'method',value:'editMaxProduct'});
        if(confirm('ต้องการที่จะเปลี่ยนค่าใช่หรือไม่') === true){
            $.post('<?php echo _BASE_URL_;?>show/product/product_action.php',data,function(html){
                if(html.success === 'true'){
                    $.loader('save');
                    $.unloader('save');
                }
            },'json');
        }
    }
    //$.loader('save');
    //$.unloader('save');
});

</script>