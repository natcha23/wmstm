<?php
$start = '0';
$num_show = '10';
$page = '1';
if(isset($_REQUEST['p']))
{
	if($_REQUEST['p']>1)
	{
		$page = $_REQUEST['p'];
		$start = ($page-1)*$num_show;
	}
}
$db->order_by("zone_name", "asc");
$db->where('status','0');
$sqlZoneAll = $db->get('tb_zone');
$numAll = $sqlZoneAll->num_rows();

$db->order_by("zone_name", "asc");
$db->where('status','0');
$sqlZone = $db->get('tb_zone',$num_show,$start);
$num_rows = $sqlZone->num_rows();
//echo $db->last_query();

$args = array();
$args['page'] = $page;
$args['perPage'] = $num_show;
$args['title'] = 'รายการ';
$args['rows'] = $numAll;
$args['func'] = 'zone.paginationChange';
$fpage = pagination($args);
$n = $start+1;
?>
<div>
    <!-- Form add zone -->
    <div class="ibox-title">
        <input type="hidden" id="thisPage" value="set_zone"/>
        <h2>ตั้งค่าโซน</h2>
        <input class="" type="button" value="สร้างโซน" onclick="zone.add();"/>
        <div id="zoneForm" class="" style="border:solid 2px; border-color: #e7eaec; padding: 20px; margin-top:5px; display:none;">
            <h2 id="titleHead">เพิ่มโซน</h2>
            <form class="form-horizontal" action="show/zone/manage.php" method="post">
                <div class="form-group ">
                    <label for="inputEmail3" class="col-sm-2 control-label">ชื่อโซน :</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control formInput" id="nameZone" class="chkInput" name="nameZone" placeholder="ขื่อโซน" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">หมายเหตุ :</label>
                    <div class="col-sm-2">
                      <textarea type="text" class="form-control formInput" id="note" class="chkInput" name="note" placeholder="หมายเหตุ" cols="20" rows="3"></textarea>
                    </div>
                </div>
                <input type="hidden" id="actionType" name="actionType" value="addZone"/>
                <input type="hidden" id="zoneID" name="zoneID" value=""/>
                <input class="btn btn-outline btn-primary" type="submit" value="บันทึกข้อมูล"/>
                <button type="button" class="btn btn-outline btn-warning" onclick="zone.cancel();">ยกเลิก</button>
            </form>
        </div>
        </form>
    </div>
    <!-- List Zone -->
    <?php

    ?>
    <div class="ibox-title" id="test1">
        <div class="paginationClass" id="paging1" rel="<?=$page?>"><?=$fpage['pagination']?></div>
        <table class="table table-striped table-bordered table-hover dataTables-example dataTable dtr-inline">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อ ZONE</th>
                    <th>หมายเหตุ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="detailZone">
                <?php
                if($num_rows > 0){
                    foreach($sqlZone->result_array() as $rs){
                ?>
                        <tr id="tr<?=$n?>">
                            <td><?=$n?></td>
                            <td id="name<?=$rs['zone_id']?>"><?=$rs['zone_name']?></td>
                            <td id="note<?=$rs['zone_id']?>"><?=$rs['note']?></td>
                            <td>
                                <button type="button" class="btn btn-outline btn-warning btn-sm" onclick="zone.update('<?=$rs['zone_id']?>');">แก้ไข</button>
                                <!--
                                <button type="button" class="btn btn-outline btn-danger btn-sm" onclick="zone.del('<?=$rs['zone_id']?>');">ลบ</button>
                                -->
                            </td>
                        </tr>
                <?php
                        $n++;
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="paginationClass"><?=$fpage['pagination']?></div>
    </div>
</div>
<script>
    zone = {
        add : function(){
            $('#zoneForm').show();
            $('.formInput').val('');
            $('.formInput').attr('disabled',false);
            $('#zoneID').val('');
            $('#actionType').val('addZone');
        },
        update : function(id){
            $('#zoneForm').show();
            $('#actionType').val('updateZone');
            $('#nameZone').val($('#name'+id).html());
            $('#note').val($('#note'+id).html());
            $('#zoneID').val(id);
        },
        del : function(id){
            if(confirm('คุณต้องการที่จะลบรายการนี้ใช่หรีอไม่ ')){
                var url = 'show/location_setting/manage.php';
                $.post(url,{'zoneID':id,'actionType':'delZone'},function(data){
                    if(data == 1){
                        var page = $('#paging1').attr('rel');
                        zone.paginationChange(page);
                    }if(data == 2){
                        alert('ไม่สามารถลบรายการนี้ได้');
                        return false;
                    }
                });
            }else{

            }
        }
    }
</script>