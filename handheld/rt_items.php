<?php 

// $connectionInfo = array( "Database" => _MS_DBNAME_, "UID" => _MS_USER_, "PWD" => _MS_PWD_, "CharacterSet" => "UTF-8");
// $conn = sqlsrv_connect( _MS_HOST_, $connectionInfo);
// sqlsrv_query("SET NAMES UTF8");

$search = isset($_REQUEST['search'])?$_REQUEST['search']:'';
$today = isset($_REQUEST['todate'])?$_REQUEST['todate']:date( "Y-m-d");
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$rtID = isset($_REQUEST['refid'])? $_REQUEST['refid'] : '';

?>
<div id="menu-name">นำออก</div>
<div id="menu-back"><a href="" onclick="$.backShowrt('<?php echo $today; ?>');" border='1'>ย้อนกลับ</a></div>
<div style="clear:left"></div>

<?php echo 'RT : '.$rtID; ?>

<table border="1" bordercolor="#D8D8D8" cellpadding="0" cellspacing="0">
    <tr height="20px;" bgcolor="#0099FF">
        <td align="center">รหัสสินค้า</td>
        <!-- <td align="center">ชื่อ</td> -->
        <td>จำนวน</td>
        <td>หน่วยนับ</td>
        <td>สถานะ</td>
    </tr>
<?php

$condition = '';
$cond_date = " AND (DI.DI_CRE_DATE BETWEEN CONVERT(datetime, '{$yesterday}', 121) AND CONVERT(datetime, '{$eoftoday}', 121)) ";
// $condition =" WHERE DT.DT_PROPERTIES IN ('211','304') and DT.DT_KEY <> '2' and WL.WL_KEY in( '1','111','165') AND WL_TO.WL_CODE <> '0499'  AND DI.DI_ACTIVE=0 AND DT.DT_ENABLE='Y' ".$cond_date;

$arr_cond = array(
		"DT.DT_PROPERTIES IN ('211','304')",
		"DT.DT_KEY <> '2'",
		"WL.WL_KEY in( '1','111','165')",
		"WL_TO.WL_CODE <> '0499'",
		// 		"DI.DI_ACTIVE=0",
		// 		"DT.DT_ENABLE='Y'"
);

foreach ($arr_cond as $where) {
	$cond .= ($cond)?  " AND " . $where : " WHERE " . $where;
}
$condition = $cond . $cond_date;
$condition = $cond . " AND DI.DI_REF = '$refid' ";

$sql_fields = "DI.DI_KEY, DI.DI_CRE_DATE, DI.DI_REF, DI.DI_REMARK,
				TD.TRD_SH_CODE, TD.TRD_QTY, TD.TRD_UTQNAME, GDM.GOODS_ALIAS";

$sql	= "SELECT ". $sql_fields . "
							FROM  DOCINFO AS DI
							LEFT JOIN  TRANSTKH AS TH ON DI.DI_KEY = TH.TRH_DI
							INNER JOIN TRANSTKD AS TD ON TH.TRH_KEY = TD.TRD_TRH
							INNER JOIN WARELOCATION AS WL ON WL.WL_KEY = TD.TRD_WL
							INNER JOIN WARELOCATION as WL_TO on TD.TRD_TO_WL = WL_TO.WL_KEY
							INNER JOIN WAREHOUSE AS WH ON WL.WL_WH = WH.WH_KEY
							INNER JOIN DOCTYPE AS DT ON DI.DI_DT = DT.DT_KEY
							LEFT JOIN GOODSMASTER AS GDM ON TD.TRD_SH_CODE = GDM.GOODS_CODE
	                      ".$condition;

$sql .= ' ORDER BY DI.DI_REF, TD.TRD_SEQ ';
// echo $sql;exit;
$params = array();
// $stmt = sqlsrv_query( $conn, $sql );

$allproductinrt = 0;
// while( $srv_row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
// 	$results[] = $srv_row;
// 	$allproductinrt += $srv_row['TRD_QTY'];
	
// }

// del //
$db->select("*")->from("outbound_rt AS rt");
$db->join("outbound_items_location AS lo", "rt.id = lo.outbound_id", "LEFT");
$db->where("rt.rt_refid = '$rtID'");
$db->where("rt.status = 0");
$db->group_by("rt.barcode");

$sql = $db->get();
$outs = $sql->result_array();
$results = array();
foreach( $outs as $srv_row ) {
	
	$srv_row['DI_REF'] = $srv_row['rt_refid'];
	$srv_row['GOODS_ALIAS'] = $srv_row['goods_name'];
	$srv_row['TRD_UTQNAME'] = $srv_row['unit'];
	$srv_row['TRD_SH_CODE'] = $srv_row['barcode'];
	$srv_row['DI_CRE_DATE'] = $srv_row['rt_date'];
	$srv_row['TRD_QTY'] = $srv_row['rt_qty'];
	
	$results[] = $srv_row;
	$rt_date = $srv_row['rt_date'];
	$allproductinrt += $srv_row['TRD_QTY'];
}
// del //
$product_amount = count($results);

$loop = 0;
foreach( $results as $result ) {
// while( $result = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
// 	$result['DI_CRE_DATE'] = $result['DI_CRE_DATE']->format('Y-m-d H:i:s');
	$result['DI_CRE_DATE'] = $result['DI_CRE_DATE']; // del //
	$qty_exp = explode(".", $result['TRD_QTY']);
	if(count($qty_exp) > 0) {
		$qty = $qty_exp[0];
	}else{
		$qty = $result['TRD_QTY'];
	}
?>
    <tr <?php if($loop%2 == 0) {?> bgcolor="#FFFFFF" <?php } else { ?> bgcolor="#D8D8D8" <?php } ?>>
        <td><?php echo $result['TRD_SH_CODE'];?></td>
        <!-- <td><?php echo $result['GOODS_ALIAS'];?></td> -->
        <td><?php echo number_format($qty);?></td>
        <td><?php echo $result['TRD_UTQNAME'];?></td>
        <td>
        <?php
            $sqlRow = $db->get_where('outbound_rt',array('rt_refid'=>$result['DI_REF'], 'barcode' => $result['TRD_SH_CODE'],'rt_success'=>0, 'status'=>0));
            $numRow = $sqlRow->num_rows();
            if($numRow == 0){
        ?>
        	<a href="" onclick="$.toOperate('<?php echo $rtID; ?>', '<?php echo $product_amount; ?>', '<?php echo $allproductinrt; ?>', '<?php echo $result['TRD_SH_CODE']; ?>', '<?php echo $today; ?>')">เลือก</a>
        <?php 
	        }else{
            ?>
                <font color="#00CC99"><b>สำเร็จ</b></font>
            <?php 
            }
            ?>
        </td>
    </tr>
<?php
	$loop++;
}
?>
</table>
<?php 
	// Remark RT
	$db->select("id, detail");
	$db->from("outbound_remarks");
	$db->where(array("rt_refid" => $refid,
			"type" => "RT"
	));
	
	$sql = $db->get();
	$remark = $sql->row_array();
?>

<form id="frm-remark">
	<label><b>Remark :</b></label> <span style="margin-left:90px"><a href="" onclick="$.backShowrt('<?php echo $today; ?>');" border='1'>ย้อนกลับ</a></span>
	<br/>
	<textarea rows="3" cols="20" name="remark"><?php echo $remark['detail']; ?></textarea>
	<br/>
	<button type="button" onclick="$.closeProcess('<?php echo $today; ?>');">ปิดรายการ</button>
	<button type="submit" onclick="">บันทึก</button>
	<input type="hidden" name="remark_id" value="<?php echo $remark['id']; ?>"> 
	<input type="hidden" id="mode" name="mode">
	<input type="hidden" name="rt_no" id="rt_no" value="<?php echo $rtID; ?>">
	
</form>
	
<script>

$(function(){

	$('#frm-remark').submit(function (e) {
		event.returnValue = false;
		if (confirm('ต้องการบันทึกข้อมูลใช่หรือไม่') == true) {
		 	$('#mode').val('saveremark');
		$.ajax({             
			type: 'post',
			url: '?page=rt_process',
			data: $('#frm-remark').serialize(),
			success: function (html) {
// 				alert(html);
// 				alert('บันทึกข้อมูลแล้ว');
				location.reload();
			}
	    });
	    return false;
	}
	});

	$.closeProcess = function(todate) {

// 			event.returnValue = false;

    	 if (confirm('ต้องการปิดรายการเพื่อส่งเช็คสินค้าใช่หรือไม่') == true) {
			$.ajax({
				type: 'post',
				url: '?page=update_process',
				data: {	method : 'send-to-checking-out',	rt_id : $('#rt_no').val()},
				success: function (html) {
// 	 						alert('บันทึกข้อมูลแล้ว');
			 				location.reload();
// 			 				console.log(html);
// 			 				var params = {	todate: todate	};
// 			 				$.postAndRedirect('?page=show_rt', params);
				}
		    });
			event.returnValue = false;
		} else {
			event.returnValue = false;
		}

	}
	
	$.toOperate = function(refid, amt, allamt, prod, todate) {

		var params = {
				refid: refid,
				amt: amt,
				allamt: allamt,
				prod: prod,
				todate: todate
			};
		
		$.postAndRedirect('?page=rt_operate', params);
	}

	$.backShowrt = function(todate) {

		var params = {
				todate: todate
			};
		
		$.postAndRedirect('?page=show_rt', params);
	}
	
	$.postAndRedirect = function(url, postData)
	{
	    var postFormStr = "<form method='POST' action='" + url + "'>\n";
	    
	    for (var key in postData)
	    {
	        if (postData.hasOwnProperty(key))
	        {
	            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'/>";
	            
	        }
	    }

	    postFormStr += "</form>";
	    var formElement = $(postFormStr);
		event.returnValue=false;
	    $('body').append(formElement);
	    $(formElement).submit();
	}
});

</script>	
	
	
	
	
	