<?php

$search = isset($_GET['search'])?$_GET['search']:'';
$today = isset($_GET['todate'])?$_GET['todate']:date( "Y-m-d");
$rtdate = isset($_GET['rtdate'])?$_GET['rtdate']:'';
$refid = isset($_GET['refid'])?$_GET['refid']:'';
$yesterday = date( "Y-m-d", strtotime( "$today -1 day" ) ).' 07:00:00';
$eoftoday = $today.' 06:59:59';

$user_id = ($_SESSION['userID'])?$_SESSION['userID']:0;

// /* insert to DB (mySQL) */
if(isset($_POST['mode']) && $_POST['mode'] == "save") {
	
	
}