<?php
$sqlProduct = $db->get('tb_product');
?>
<div class="ibox-title">
    <table border="1" id="table-product" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Barcode</th>
                <th>ชื่อ</th>
                <th>category</th>
                <th>import</th>
                <th>supplier</th>
                <th>type</th>
                <th>status</th>
                <th>length</th>
                <th>width</th>
                <th>height</th>
                <th>net_weight</th>
                <th>stackable</th>
                <th>uom</th>
                <th>FIFO/FEFO</th>
                <th>aging</th>
                <!--<th>min</th>
                <th>max</th>-->
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
                <td><?php echo $rsProduct['barcode']?></td>
                <td><?php echo $rsProduct['name']?></td>
                <td><?php echo $rsProduct['cat']?></td>
                <td><?php echo $rsProduct['import']?></td>
                <td><?php echo $rsProduct['supplier']?></td>
                <td><?php echo $rsProduct['type']?></td>
                <td><?php echo $rsProduct['status']?></td>
                <td><?php echo $rsProduct['lenght']?></td>
                <td><?php echo $rsProduct['width']?></td>
                <td><?php echo $rsProduct['height']?></td>
                <td><?php echo $rsProduct['net_weight']?></td>
                <td><?php echo $rsProduct['uom']?></td>
                <td><?php echo $rsProduct['stackable']?></td>
                <td><?php echo $rsProduct['fifo_fefo']?></td>
                <td><?php echo $rsProduct['aging']?></td>
                <!--<td>


                    <input id="maxProduct<?php echo $productKey;?>" type="text" placeholder="จำนวนสูงสุด" class="form-control" size='4' value="<?php echo $qtyMax; ?>" />
                    <button onclick="$.maxProduct('<?php echo $productKey;?>');">บันทึก</button>
                </td>-->
                <!--<td>
                    <input id="expProduct<?php echo $productKey;?>" type="text" placeholder="อายุสินค้า" class="form-control" size='4' value="<?php echo $numExp; ?>" />
                    <button onclick="$.expProduct('<?php echo $productKey;?>')">บันทึก</button>
                </td>-->
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<script>
$(function(){
	$('#nav-setting').parent().addClass('active');
	$('#nav-setting').addClass('in');
	$('#setting_product').addClass('active');

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