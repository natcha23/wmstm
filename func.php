<?php
define('_DATE_',date('Y-m-d'));
define('_DATE_TIME_',date('Y-m-d H:i:s'));
// function NameOutboundStatus แสดงชื่อ status  outbound
// function shortDate แสดง เฉพาะวันที่
// function returnDate
// function retrunDateToDB รับเป็นวันที่แบบ array
// function getInboundLocation แสดงชื่อ location ของ สินค้านั้นๆ
// function getOutboundLocation แสดงชื่อ location ของ สินค้านั้นๆ
// function getNameLocation แสดงชื่อ location
// function cleanArray
// function catname ดึงชื่อ ประเภทสินค้า
// function pagination
// function iconImg แสดงรุป
// function  _print
// function addDay function + วัน
// function dateToShow แปลงวันที่เพื่อแสดง

$statusKeyword = array(
		'1' => 'Launch',
		'2' => 'Pick up',
		'3' => 'Checking out',
		'4' => 'Choose car',
		'5' => 'Transporting',
		'6' => 'Arrival branch ',
		'7' => 'Received goods'
);

function DB($active_record_override = NULL)
{
    $myClass = new myClass;
    return $myClass->DB($active_record_override);
}

function NameOutboundStatus($statusID)
{
	global $statusKeyword;
	echo $statusKeyword[$statusID];
}

function _NameOutboundStatus($statusID){
    if($statusID == '1'){ echo 'เริ่มดำเนินการ';}
    else if($statusID == '2'){echo 'ดึงของเสร็จ';}
    else if($statusID == '3'){echo 'เช็คของเสร็จ';}
    else if($statusID == '4'){echo 'เลือกรถเสร็จ';}
    else if($statusID == '5'){echo 'รถออก';}
    else if($statusID == '6'){echo 'รถถึงสาขา';}
    else if($statusID == '7'){echo 'รับของ';}
}

function shortDate($strDate) {
    if (empty($strDate) || $strDate == "0000-00-00" || $strDate == "0000-00-00 00:00:00") {
        return "";
    } else {
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("m", strtotime($strDate));
        $strDay = date("d", strtotime($strDate));

        return "$strDay/$strMonth/$strYear";
    }
}

function retrunDate($array){
    if(is_array($array)){
        return 'array';
    }else if(is_object($array)){
        foreach($array as $i => $e){
            if($i=='date'){
                $ex = explode(' ',$e);
            }return $ex[0];
        }
    }else{
        return false;
    }
}

function retrunDateToDB($array){
    if(is_array($array)){
        return 'array';
    }else if(is_object($array)){
        foreach($array as $i => $e){
            if($i=='date'){
                return $e;
            }
        }
    }else{
        return false;
    }
}
function getInboundLocation($inboundID){
    $db = DB();
    $sqlLocationID = $db->get_where('inbound_location in_lo',array('inbound_id'=>$inboundID));
    foreach($sqlLocationID->result_array() as $rsLo){
        echo getNameLocation($rsLo['location_id']).'('.$rsLo["qty"].')</br>';
    }
}
function getOutboundLocation($outboundID){
    $db = DB();
    $sqlLocationID = $db->get_where('outbound_items_location',array('outbound_id'=>$outboundID));
    foreach($sqlLocationID->result_array() as $rsLo){
        echo getNameLocation($rsLo['location_id']).'('.$rsLo["qty"].')</br>';
    }
}
function getNameLocation($id){
    $db = DB();
    $db->select('add_name');
    $sql = $db->get_where('tb_address',array('add_id'=>$id));
    $rs = $sql->row_array();
    return $rs['add_name'];
}

function cleanArray($arr){
	$r = array();
    $size = sizeof($arr);
    for($i=0;$i<$size;$i++){
        $thum = trim($arr[$i]);
        if($thum != ""){
            $r[] = $thum;
        }
    }
    return $r;
}

function catname($id){
    $db = DB();
    $db->select('cat_name')->from('tb_cat')->where(array('cat_id'=>$id));
    $sql = $db->get();
    $arr = $sql->row_array();
    return $arr['cat_name'];
}

function getStatus_PO($id){
    if($id == 0){
        echo "<span style='color:red;font-weight:bold; float:right;'>รอดำเนินการ</span>";
    }else if($id == 1){
        echo "<span style='color:#f8ac59;font-weight:bold; float:right;'>รับสินค้าเรียบร้อย</span>";
    }else if($id == 2){
        echo "<span style='color:green;font-weight:bold; float:right;'>เก็บสินค้าเรียบร้อย</span>";
    }
}

function pagination($args){
	$Per_Page = '10';//Defult
	if(isset($args['perPage']))
	{
		$Per_Page = $args['perPage'];
	}
    $Page=1;
    if($args['page']!=''){
        $Page = $args['page'];
    }

    $Prev_Page = $Page-1;
    $Next_Page = $Page+1;

    $Page_Start = (($Per_Page*$Page)-$Per_Page);
    if($args['rows']<=$Per_Page){
        $Num_Pages =1;
    }else if(($args['rows'] % $Per_Page)==0){
        $Num_Pages =($args['rows']/$Per_Page) ;
    }else{
        $Num_Pages =($args['rows']/$Per_Page)+1;
        $Num_Pages = (int)$Num_Pages;
    }
    $paginate = "<div class='paginate'>";
    $paginate .= "ทั้งหมด ".$args['rows']." ".$args['title']." : $Num_Pages หน้า :";
    if($Prev_Page){
        $paginate.= " <a href='#' onclick=\"".$args['func']."(1)\"> <u><<</u> </a> ";
        $paginate.= " <a href='#' onclick=\"".$args['func']."($Prev_Page)\"> < </a> ";
    }

    for($i=1; $i<=$Num_Pages; $i++){
        if($i != $Page){
            $min = $Page - 3;
            $max = $Page + 3;
            if($min<$i && $i<$max){
                $paginate.= "<a href='#' onclick=\"".$args['func']."($i)\">$i</a>";
            }
        }else{
            $paginate.= "<a href='#' class='active'> $i </a>";
        }
    }
    if($Page!=$Num_Pages){
        $paginate.= " <a href='#' onclick=\"".$args['func']."($Next_Page)\"> > </a> ";
        $paginate.= " <a href='#' onclick=\"".$args['func']."($Num_Pages)\"> <u id='lastPaging'>>></u> </a> ";
    }
    $paginate .= "</div>";

    $data = array();
    $data['pagination'] = $paginate;
    $data['pageStart'] = $Page_Start;
    $data['perPage'] = $Per_Page;
    return $data;
}

function iconImg($img, $link = '') {
	$ex = end(explode(".", $img));
	$imgType = array("jpg", "jpeg", "gif", "png");
	$docType = array("doc", "docx", "xls", "xlsx", "ppt", "pptx", "pdf");
	$mediaType = array("mp3", "wmv", "avi", "mp4");

	if (in_array($ex, $imgType)) {
		$imgPath = $link;
	} elseif (in_array($ex, $docType)) {
		$imgPath = _BASE_URL_ . "lib/img/icon/filetype_" . $ex . ".png";
	} elseif (in_array($ex, $mediaType)) {
		$imgPath = _BASE_URL_ . "lib/img/icon/filetype_mov.png";
	} else {
		$imgPath = _BASE_URL_ . "lib/img/icon/untitle.png";
	}

	return $imgPath;
}

function _print($data=null) {
	if(!empty($data)) {
		echo '<pre>' . print_r($data, 1) . '</pre>';
	} else {
		echo 'Empty data.';
	}
}
function addDay($date,$add){
    $now = date('Y-m-d',strtotime($date." + ".$add." day"));
    return $now;
    //$datetime = dateToShow($now);
    //return $datetime['year'];
}

function date_sum_remain($exp){
    if($exp != ''){
        $exNow = explode('-', date('Y-m-d'));
        $exExp = explode('-',$exp);
        $nowDay = gregoriantojd($exNow[1],$exNow[2],$exNow[0]);
        $dayExp = gregoriantojd($exExp[1],$exExp[2],$exExp[0]);
        return $dayExp-$nowDay;
    }
}

function dateToShow($date) {
    $ex = explode(' ', $date);
    $data = array();

    $m = array("01" => "ม.ค.", "02" => "ก.พ.", "03" => "มี.ค.", "04" => "เม.ย.", "05" => "พ.ค.", "06" => "มิ.ย.", "07" => "ก.ค.", "08" => "ส.ค.", "09" => "ก.ย.", "10" => "ต.ค.", "11" => "พ.ย.", "12" => "ธ.ค.");

    if ($ex[0] != '0000-00-00') {
        $e1 = explode('-', $ex[0]);
        $y = $e1[0] + 543;
        $data['year'] = $e1[2] . '/' . $e1[1] . '/' . $y;
        $data['th'] = $e1[2] . ' ' . $m[$e1[1]] . ' ' . $y;
        //$data['time'] = $ex[1];
    } else {
        $data['year'] = date('d/m/') . (date('Y') + 543);
        $data['th'] = '';
        //$data['time'] = $ex[1];
    }

    return $data;
}

function conv2mysqldatetime($date=null) {
	if(!empty($date)) {
		$data = explode(" ", $date);

		$_date = explode("/", $data[0]);
		$enYear = $_date[2] - 543;
		if(count($data) > 1) {
			$_result = $enYear . "-" . $_date[1] . "-" . $_date[0] . " " . $data[1];
		} else {
			$_result = $enYear . "-" . $_date[1] . "-" . $_date[0] . $thYear;
		}
		return $_result;
	} else {
		return;
	}
}

if(!function_exists('UTFEncode')){
	function UTFEncode($string, $encoding = 'default') {
		global $config;
		$appEncoding = 'TIS-620';
		if (strtolower ( $encoding ) == 'default') {
			$fromEncoding = $appEncoding;
		} else {
			$fromEncoding = $encoding;
		}

		return iconv ( $fromEncoding, 'UTF-8', $string );
	}
}

if(!function_exists('UTFDecode')){
	function UTFDecode($string, $encoding = 'default') {
		global $config;
		$appEncoding = 'TIS-620';
		if (strtolower ( $encoding ) == 'default') {
			$targetEncoding = $appEncoding;
		} else {
			$targetEncoding = $encoding;
		}

		return iconv ( 'UTF-8', $targetEncoding, $string );

	}
}
function genDOCID($name=null, $prefix_str=null) {
	$db = DB();
	if (empty($name)) { return; }
// 	$yearMonth = (date("Y")+543).date("m");
	$Year = (date("Y")+543);
	// 	$yearMonth = substr(date("Y")+543, -2).date("m");

	//query MAX ID
	//$sql = "SELECT MAX(id) AS last_id FROM my_table";
	//$qry = mysql_query($sql) or die(mysql_error());
	//$rs = mysql_fetch_assoc($qry);
	$sql = $db->select("*")->from("tb_running")->where(array('name' => $name, 'prefix' => $prefix_str.$Year))->get();
	$ds = $sql->row();
	$count = $sql->num_rows();

	if(!empty($count) || $count > 0){
		$lastnumber = $ds->lastnumber;
		$running = ($lastnumber+1);
		$update_where = array('name' => $name, 'prefix' => $prefix_str.$Year);
		$db->update("tb_running", array('lastnumber' => $running), $update_where);
	}
	else{
		$running = 1;
		$fields = array(
				'name' => $name,
				'prefix' => $prefix_str.$Year,
				'running' => 0,
				'lastnumber' => $running
		);
		$db->insert("tb_running", $fields);
	}

// 	$db->select_max('delivery_order_id', 'max_num');
// 	$query = $db->get('tb_running');

// 	$result = $query->row_array();

// 	$maxId = $result['max_num'];
// 	$maxId = substr($result['max_num'], -5);  //ข้อมูลนี้จะติดรหัสตัวอักษรด้วย ตัดเอาเฉพาะตัวเลขท้ายนะครับ
// 	$maxId = ($maxId + 1);

	$maxId = substr("000000".$running, -6);

	return strtoupper($prefix_str).$Year."-".$maxId;

}

if(!function_exists('runningId')){
	#$runnig_id = runningId('re_po','P2558');
	function runningId($name,$prefix){
		$db = DB();

		$utf8 = new utf8();
// 		$obj = new xajaxResponse();
		$sql = $db->select("*")->from("tb_running")->where(array('name' => $name, 'prefix' => $prefix));
		$ds = $sql->row();

		$count = $sql->num_rows();

		if(!empty($count) || $count > 0){
			$lastnumber = $ds->lastnumber;
			$running = ($lastnumber+1);
			$update_where = array('name' => $name, 'prefix' => $prefix);
			$db->update("tb_running", array('lastnumber' => $running), $update_where);
		}else{
			$running = 1;
			$fields = array(
				'name' => $name,
				'prefix' => $prefix,
				'running' => 0,
				'lastnumber' => $running
			);
			$db->insert("tb_running", $fields);
		}

		$maxId = substr("00000".$running, -5);
		return strtoupper($prefix_str).$yearMonth."/".$maxId;

		$prefixrun = "";
		for($i=3;$i>count($running);$i--){

			$prefixrun.="0";
		}
		$running =$prefix.''.$prefixrun.''.$running;
		return $running;
	}
}



?>