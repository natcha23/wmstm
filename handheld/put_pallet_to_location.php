<?php
?>
<button type="button" onclick="javascript:window.location.href='?'">เมนู</button>
<form action="action_put_pallet_to_location.php" method="post" id="formMove" >
    <input name="method" type="hidden" value="put_pallet_in_location"/>
    <input id="palletID" name="palletID" size="10" value=""
           onkeypress="if(event.keyCode == 13){selectList(this.value,'getProduct'); return false; }" style=""/></br>
    <input id="locationName" name="locationName" size="10" value=""
           onkeypress="if(event.keyCode == 13){selectList(this.value,'getLocationID'); return false;}" style=""/>
    <input type="hidden" id="locationID" name="locationID"/>
    <button type="button" onclick="submitForm();">นำเข้า</button>

    <table border='1' id="tableList" width="200">
        <thead>
            <tr bgcolor="#0099FF">
                <th>barcode</th>
                <th>จำนวน</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</form>
<div id="testAjax"></div>
<script>
    $(document).ready(function(){
        $('#palletID').focus();
    });
    function submitForm(){
        if(!$('#palletID').val()){
            $('#palletID').focus();
            return false;
        }
        if(!$('#locationID').val() || !$('#locationName').val()){
            $('#locationName').focus();
            return false;
        }
        if($('#palletID').val()!='' && $('#locationID').val() !=''){
            if(confirm('ยืนยันที่จะดำเนินการค่อ')){
                $('#formMove').submit();
            }
        }
    }
    function selectList(val,method){
        if(!location){
            return false;
        }
        data = new Array();
        data.push({name:'val',value:val});
        data.push({name:'method',value:method});
        $.post('ajax/get_product_in_pallet.php',data,function(html){
            if(method == 'getProduct'){
                $('#tableList tbody').html('');
                //console.log(html.last_query);
                if(html.status != 1){
                    $('#palletID').val('');
                }
                $('#tableList tbody').append(html.detail);
            }else if(method == 'getLocationID'){
                if(html.locationID != 'false'){
                    $('#locationID').val(html.locationID);
                }else{
                    alert('รหัสตำแหน่งไม่ถูกต้อง');
                    $('#locationName').val('');
                    $('#locationName').focus();
                }
            }
        },'json');
    }
</script>


