<?php
?>
<button type="button" onclick="javascript:window.location.href='?'">เมนูหลัก</button>
<form action="action_move_product.php" method="post" id="formMove" >
    <input name="method" type="hidden" value="move_location"/>
    <input id="locationOld" name="locationOld" size="10" value="" onkeypress="if(event.keyCode == 13){selectList(this.value,'getProduct'); return false; }" style=""/>
    <input type="hidden" id="locationOldID" name="locationOldID"/><br/>
    <input id="locationNew" name="locationNew" size="10" value="" onkeypress="if(event.keyCode == 13){selectList(this.value,'getLocationNewID'); return false;}" style=""/>
    <input type="hidden" id="locationNewID" name="locationNewID"/>
    <button type="button" onclick="submitForm();">ย้าย</button>


    <table border='1' id="tableList" width="200">
        <thead>
            <tr bgcolor="#0099FF">
                <th>#</th>
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
        $('#locationOld').focus();
    });
    function submitForm(){
        if(!$('#locationOld').val()){
            $('#locationOld').focus();
            return false;
        }
        if(!$("input[name='inKey[]']:checked").length > 0){
            alert('เลือกรายการ');
            return false;
        }
        if($('#locationOld').val() && !$('#locationNew').val() ){
            $('#locationID').focus();
            return false;
        }
        if($('#locationOld').val()!='' && $('#locationNew').val() !='' && $("input[name='inKey[]']:checked").length > 0){
            if($('#locationOldID').val() != '' && $('#locationNewID').val() != ''){
                if(confirm('ยืนยันที่จะดำเนินการ')){
                    $('#formMove').submit();
                }
            }
        }
    }
    function selectList(location,method){
        if(!location){
            return false;
        }
        data = new Array();
        data.push({name:'location',value:location});
        data.push({name:'method',value:method});
        $.post('ajax/get_product_in_location.php',data,function(html){
            if(method == 'getProduct'){
                $('#tableList tbody').html('');
                if(html.status != 1){
                    $('#locationOld').val('');
                    $('#locationOldID').val('');
                    $('#locationNew').val('');
                }else{
                    $('#locationOldID').val(html.locationID);
                }
                $('#tableList tbody').append(html.detail);
            }else if(method == 'getLocationNewID'){
                if(html.locationID != 'false'){
                    $('#locationNewID').val(html.locationID);
                }else{
                    $('#locationNew').val('');
                    $('#locationNew').focus();
                }
            }
        },'json');
    }
</script>


