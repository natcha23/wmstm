<?php
require_once('config.php');
require_once('class_my.php');
require_once('func.php');

$error=false;
$db=DB();
if($_SESSION['username']){
	if(isset($_GET['file'])){
		$db->where('PhysicalName LIKE \''.  $_GET['file'] . '%\'');
		$result=$db->get('files_upload');
	}else if(isset($_GET['fileID'])){
		$db->where('ID',$_GET['fileID']);
		$result=$db->get('files_upload');
	}
}

if(isset($result)){
	$numFile=$result->num_rows();
	if($numFile>1)
	{
		$link_download=array();
		foreach($result->result() as $row){
			$link_download[]='<a href="'._BASE_URL_.'/file.php?file='.$row->PhysicalName.'" target="_blank" >'.$row->FileName.'</a>';
		}
		echo implode('<br>',$link_download);
		exit();
	}else if($numFile==1){
		$row=$result->result();
		$file_path='upload/'.$row[0]->PhysicalName;
		if(is_file($file_path)){
			$is_image=false;
			if(isset($_GET['size']) && is_numeric($_GET['size']) && preg_match('/image/i',$row[0]->FileType) && !preg_match('/bmp/i',$row[0]->FileType)){
				$is_image=true;
				$file_type=$row[0]->FileType;
				$file_name=$row[0]->FileName;
				
				$size=$_GET['size']<=100?100:150;
				$thumbnail_file=$size.'_'.$row[0]->PhysicalName;
				$thumbnail_file_path='upload/'.$thumbnail_file;
				if(is_file($thumbnail_file_path)){
					$file_path=$thumbnail_file_path;
				}else{
					$info=getimagesize($file_path);
					$w=$info[0];
					$h=$info[1];
					
					switch($file_type){
						case 'image/gif':
							$source=imagecreatefromgif($file_path);
							break;
						case 'image/jpeg':
							$source=imagecreatefromjpeg($file_path);
							break;
						case 'image/png':
							$source=imagecreatefrompng($file_path);
							break;
					}
					
					$img=imagecreatetruecolor($size,$size);
					$fff=imagecolorallocate($img,255,255,255);
					imagefill($img,0,0,$fff);
					
					
					if($w>$h){
					$percent=$size/$w;
					}else{
					$percent=$size/$h;
					}
					$new_w=$w*$percent;
					$new_h=$h*$percent;
					
					$x=($size/2)-($new_w/2);
					$y=($size/2)-($new_h/2);
					
					imagecopyresampled($img,$source,$x,$y,0,0,$new_w,$new_h,$w,$h);
					$file_type=$info['mime'];
					header('Content-type: '.$file_type);
					header('Content-Disposition:'.(isset($_GET['attach']) && $_GET['attach']==1?'attachment':'inline').'; filename=\''.$file_name.'\'');
					imagepng($img);
					switch($file_type){
						case 'image/gif':
							$source=imagegif($img,$thumbnail_file_path);
							break;
						case 'image/jpeg':
							$source=imagejpeg($img,$thumbnail_file_path);
							break;
						case 'image/png':
							$source=imagepng($img,$thumbnail_file_path);
							break;
					}
					imagedestroy($img);
					exit();
				}
			}else{
				$file_type=$row[0]->FileType;
				$file_name=$row[0]->FileName;
			}
			
		}else{
			$error=true;
		}
	}else{
		$error=true;
	}
	
	if($error){
		
		$file_path=(isset($_GET['size']) && is_numeric($_GET['size'])?$_GET['size']<=100?'100_':'150_':'').'error.jpg';
		$file_name=$file_path;
		$info=getimagesize($file_path);
		$file_type=$info['mime'];
	}else if(!$error && !$is_image && isset($_GET['size'])){
		$ex=explode('.',$row[0]->FileName);
		$file_path='icon/filetype_'.end($ex).'.png';
		if(is_file($file_path)){
			$file_path=(isset($_GET['size']) && is_numeric($_GET['size'])?$_GET['size']<=100?'100_':'150_':'').'error.jpg';
		}
		$file_name=$row[0]->FileName;
		$file_type='image/png';
	}
	
	header("Content-type: ".$file_type);
	header("Content-Disposition:".(isset($_GET['attach']) && $_GET['attach']==1 && $error==false?'attachment':'inline')."; filename='".basename($file_name)."'");
	readfile($file_path);
}
?>
