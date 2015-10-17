<?php

require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$db = DB();
$db->from('location_row');
$query = $db->get();
echo $db->last_query();

echo 'test.php';
