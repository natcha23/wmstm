<!-- รายวัน -->
<div class="ibox-title">
    ดึง IBP แบบรายวัน
    <input type="text" id="dateImport" class="inputDate" placeholder="วันที่จะดึงข้อมูล" value="<?php echo date( "Ymd");?>"/>
    <button onclick="$.import('dateImport','dateImport')" class="btn btn-primary">ดึงข้อมูล</button>
    <div id="content"></div>
</div>
<!-- ตามรหัส PO -->
<div class="ibox-title">
    ดึง IBP ด้วย รหัสใบ PO
    <input type="text" id="poID" class="" placeholder="เลขที่ใบ PO" value=""/>
    <button onclick="$.import('poImport','poID')" class="btn btn-primary">ดึงข้อมูล</button>
</div>
<!-- ตามรหัส IBP -->
<div class="ibox-title">
    ดึง IBP ด้วยรหัส IBP
    <input type="text" id="ibpID" class="" placeholder="รหัส IBP" value=""/>
    <button onclick="$.import('ibpImport','ibpID')" class="btn btn-primary">ดึงข้อมูล</button>
</div>

<script>
$(function(){
    $.import = function(method,id){
        $.loader();
        data = new Array();
        data.push({name:'method' , value:method});
        data.push({name:'dataGet' , value:$('#'+id).val()});
        $.post('show/inbound/get_import_ibp.php',data,function(html){
            alert(html);
            $.unloader();
            //$.loader('save');
            //$.unloader('save');
        });
    }

    $('.inputDate').datepicker({
        format: 'yyyymmdd',
        autoclose:true
    }).on('changeDate',function(e){
        var id = $(this).attr('rel');
        var dateExp = $(this).val();
    });
});
</script>