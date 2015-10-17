<?php
require_once('config.php');
require_once('class_my.php');
require_once('func.php');
$db = DB();
$upload_dir = "upload/";
$postfilename = $_POST['Filename'];
if(isset($_FILES["Filedata"])){
	$files = array();
	$fieltype = end(explode(".",$_FILES["Filedata"]["name"]));
	$fileName = md5(time().$_FILES["Filedata"]["name"]).'.'.$fieltype;
	move_uploaded_file($_FILES["Filedata"]["tmp_name"],$upload_dir.$fileName);
	$db->insert('files_upload',array(
		'PhysicalName' => $fileName,
		'FileName' => $postfilename,
		'FileType' => $_FILES['Filedata']['type'],
		'DateCreate' => date('Y-m-d H:i:s')
	));
	
	$file_id = $db->insert_id();
	// echo $file_id;
	echo json_encode(array('file_id'=>$file_id));
}
?>