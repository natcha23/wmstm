<?php
// Relative path
$conf['dir'] = str_replace('\\', '/', dirname(__FILE__));
// Absolute path
$conf['path'] = "http://".$_SERVER['SERVER_NAME'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $conf['dir']);
// uri แบบที่ตัด root folder ออก
$conf['uri'] = str_replace($conf['dir'].'/', '', $_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI']);
// explode เพื่อตัดข้อมูลหลังจาก ? ออกไป
list($xuri) = explode('?', $conf['uri'], 2);
// explode เพื่อสร้าง array โดยการ แยก ที่ตำแหน่ง /
$uri = explode("/", $xuri);
?>