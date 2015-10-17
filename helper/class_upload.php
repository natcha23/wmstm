<?php
// require_once('/../config.php');

class upload {

    var $temp_id = NULL;
    var $temp_file = NULL;
    private $time_del_temp = 1; //hour
    var $path_temp = NULL;
    var $db = NULL;
    var $userID = 0;

    function __construct($File = false) {
    	
//     	echo '<pre>'. print_r($_SESSION,1).'</pre>';
    	$userID = $_SESSION['userID'];
        $this->db = DB();
        $temp_path = dirname(__FILE__) . '/../upload/';
//         $temp_path = dirname(__FILE__) . '/../upload/temp/';
        if (!is_dir($temp_path)) {
            makefolder($temp_path);
        }
        $this->path_temp = realpath($temp_path);
        if ($File)
            $this->temp($File, $userID);
        $this->userID = isset($_SESSION['user']->ID) ? $_SESSION['user']->ID : $this->userID;
    }
    
    function getBasePath($File = false) {
    	return $this->path_temp;
    }
    
    function returnid($tempID, $programID=null, $recordID = NULL) {
    	$success = false;
    	//$sql='SELECT PhysicalName FROM files_upload_temp WHERE ID='.$tempID;
    	$this->db->select('PhysicalName');
    	$this->db->from('files_upload_temp');
    	$this->db->where('ID', $tempID);
    	$result = $this->db->get();
    	//$result=$this->db->query($sql);
    	$row = $result->result();
    	if ($result->num_rows() > 0) {
    		$tempFile = $row[0]->PhysicalName;
    
    		$path = realpath(dirname(__FILE__) . '/../upload') . '/' . $programID . '/' . $this->userID;
    		if (makefolder($path)) {
    			$fp = fopen($path . '/' . $tempFile, 'w');
    			if (fwrite($fp, file_get_contents($this->path_temp . '/' . $tempFile))) {
    				$sql = 'SELECT PhysicalName,FileName,FileType,DateCreate FROM files_upload_temp WHERE ID=' . $tempID;
    				$result = $this->db->query($sql);
    				$File = $result->result();
    				$insFile['ProgramID'] = $programID;
    				$insFile['RecordID'] = $recordID;
    				$insFile['UserID'] = $this->userID;
    				$insFile['PhysicalName'] = $File[0]->PhysicalName;
    				$insFile['FileName'] = $File[0]->FileName;
    				$insFile['FileType'] = $File[0]->FileType;
    				$insFile['DateCreate'] = $File[0]->DateCreate;
    				$this->db->insert('files_upload', $insFile);
    				$success = $this->db->insert_id();
    				$this->remove_temp($tempID);
    			}
    			fclose($fp);
    			/* if(copy($this->path_temp.'/'.$tempFile,$path))
    			 {
    			 $this->remove_temp($tempID);
    			} */
    		}
    	}
    	return $success;
    }

    public function temp($File = false, $userID) {
    	$success = false;
    	if ($File != false) {
    		
    		if (makefolder($this->path_temp)) {
    			$insFile = array();
    			$temp = $File['tmp_name'];
//     			$insFile['PhysicalName'] = md5($File['name'] . time());
    			$insFile['PhysicalName'] = $File['name'];
    			$insFile['FileName'] = $File['name'];
    			$insFile['FileType'] = $File['type'];
    			if (preg_match('/image/i', $File['type'])) {
    				$this->imageResize($File, $insFile['PhysicalName']);
    				$this->db->insert('files_upload_temp', $insFile);
    				$this->temp_id = $this->db->insert_id();
    				$success = $insFile['PhysicalName'];
    				$this->temp_file = $success;
    			} else if (copy($temp, $this->path_temp . '/' . $insFile['PhysicalName'])) {
    				$this->db->insert('files_upload_temp', $insFile);
    				$this->temp_id = $this->db->insert_id();
    				$success = $insFile['PhysicalName'];
    				$this->temp_file = $success;
    				
//     				/* Save file table */
//     				$insFile['UserID'] = $this->userID;
//     				$this->db->insert("files_upload", $insFile);
// //     				echo $this->db->last_query();
    			}
    		}
    	}
    	return $success;
    }
    
    function _temp($File = false) {
        $success = false;
        if ($File != false) {
            if (makefolder($this->path_temp)) {
                $insFile = array();
                $temp = $File['tmp_name'];
                $insFile['PhysicalName'] = md5($File['name'] . time());
                $insFile['FileName'] = $File['name'];
                $insFile['FileType'] = $File['type'];
                if (preg_match('/image/i', $File['type'])) {
                    $this->imageResize($File, $insFile['PhysicalName']);
                    $this->db->insert('files_upload_temp', $insFile);
                    $this->temp_id = $this->db->insert_id();
                    $success = $insFile['PhysicalName'];
                    $this->temp_file = $success;
                } else if (copy($temp, $this->path_temp . '/' . $insFile['PhysicalName'])) {
                    $this->db->insert('files_upload_temp', $insFile);
                    $this->temp_id = $this->db->insert_id();
                    $success = $insFile['PhysicalName'];
                    $this->temp_file = $success;
                }
            }
        }
        return $success;
    }

    function move($programID, $recordID = NULL) {
        $path = realpath(dirname(__FILE__) . '/../upload') . '/' . $programID . '/' . $this->userID;
        if (makefolder($path)) {
            if (copy($this->path_temp . '/' . $this->temp_file, $path)) {
                $this->remove_temp();
            }
        }
    }

    function move_id($tempID, $programID=null, $recordID = NULL) {
        $success = false;
        //$sql='SELECT PhysicalName FROM files_upload_temp WHERE ID='.$tempID;
        $this->db->select('PhysicalName');
        $this->db->from('files_upload_temp');
        $this->db->where('ID', $tempID);
        $result = $this->db->get();
        //$result=$this->db->query($sql);
        $row = $result->result();
        if ($result->num_rows() > 0) {
            $tempFile = $row[0]->PhysicalName;

            $path = realpath(dirname(__FILE__) . '/../upload') . '/' . $programID . '/' . $this->userID;
            if (makefolder($path)) {
                $fp = fopen($path . '/' . $tempFile, 'w');
                if (fwrite($fp, file_get_contents($this->path_temp . '/' . $tempFile))) {
                    $sql = 'SELECT PhysicalName,FileName,FileType,DateCreate FROM files_upload_temp WHERE ID=' . $tempID;
                    $result = $this->db->query($sql);
                    $File = $result->result();
                    $insFile['ProgramID'] = $programID;
                    $insFile['RecordID'] = $recordID;
                    $insFile['UserID'] = $this->userID;
                    $insFile['PhysicalName'] = $File[0]->PhysicalName;
                    $insFile['FileName'] = $File[0]->FileName;
                    $insFile['FileType'] = $File[0]->FileType;
                    $insFile['DateCreate'] = $File[0]->DateCreate;
                    $this->db->insert('files_upload', $insFile);
                    $success = $this->db->insert_id();
                    $this->remove_temp($tempID);
                }
                fclose($fp);
                /* if(copy($this->path_temp.'/'.$tempFile,$path))
                  {
                  $this->remove_temp($tempID);
                  } */
            }
        }
        return $success;
    }

    function remove_temp($tempID = false) {
        $sql = 'SELECT ID,PhysicalName FROM files_upload_temp WHERE DATE_SUB(NOW(),INTERVAL 1 HOUR) > DateCreate';
        $result = $this->db->query($sql);
        foreach ($result->result() as $row) {
            $path_file = $this->path_temp . '/' . $row->PhysicalName;
            $this->remove_file($row->ID, $path_file);
        }
        #------------------------------
        if ($this->temp_id != NULL) {
            $path_file = $this->path_temp . '/' . $this->temp_file;
            $this->remove_file($this->temp_id, $path_file);
            $tempID = false;
        }
        #------------------------------
        if ($tempID != false) {
            $result = $this->db->query('SELECT PhysicalName FROM files_upload_temp WHERE ID=' . $tempID);
            $row = $result->result();
            $path_file = $this->path_temp . '/' . $row[0]->PhysicalName;
            $this->remove_file($tempID, $path_file);
        }
    }

    function getTemp($temp = false) {
        if ($temp) {
            if (is_numeric($temp)) {
                $result = $this->db->query('SELECT PhysicalName,FileName,FileType FROM files_upload_temp WHERE ID=' . $temp);
            } else if (!is_numeric($temp)) {
                $result = $this->db->query('SELECT PhysicalName,FileName,FileType FROM files_upload_temp WHERE PhysicalName LIKE \'' . $temp . '\'');
            }
            $row = $result->result();
            if ($row) {
                $success['path'] = $this->path_temp . '/' . $row[0]->PhysicalName;
//                 $success['url'] = _HTTP_PATH_ . '/upload/temp/' . $row[0]->PhysicalName;
                $success['url'] = _BASE_URL_ . '/upload/' . $row[0]->PhysicalName;
                $success['name'] = $row[0]->FileName;
                $success['type'] = $row[0]->FileType;
            }
        }
        return $success;
    }

    private function remove_file($temp_id, $temp_file) {
        if (file_exists($temp_file) && is_file($temp_file)) {
            if (unlink($temp_file)) {
                $this->db->query('DELETE FROM files_upload_temp WHERE ID=' . $temp_id);
            }
        }
    }

    function getFile($method, $val) {
        $result = false;
        $this->db->where($method, $val);
        $sql_result = $this->db->get('files_upload');
        foreach ($sql_result->result_array() as $row) {
            $result[] = $row;
        }
        return $result;
    }

    function imageResize($fileUpload, $saveName, $maxSize = 1024) {
        $size = $maxSize;
        $file_type = strtolower($fileUpload['type']);
        $info = getimagesize($fileUpload['tmp_name']);
        $w = $info[0];
        $h = $info[1];

        switch ($file_type) {
            case 'image/gif':
                $source = imagecreatefromgif($fileUpload['tmp_name']);
                break;
            case 'image/jpeg':
                $source = imagecreatefromjpeg($fileUpload['tmp_name']);
                break;
            case 'image/png':
                $source = imagecreatefrompng($fileUpload['tmp_name']);
                break;
        }
        if ($w > $h) {
            $percent = $size / $w;
        } else {
            $percent = $size / $h;
        }
        $percent = $percent > 1 ? 1 : $percent;
        $new_w = $w * $percent;
        $new_h = $h * $percent;
        $img = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($img, $source, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        $savePath = $this->path_temp . '/' . $saveName;
        switch ($file_type) {
            case 'image/gif':
                $source = imagegif($img, $savePath);
                break;
            case 'image/jpeg':
                $source = imagejpeg($img, $savePath);
                break;
            case 'image/png':
                $source = imagepng($img, $savePath);
                break;
        }
        imagedestroy($img);
    }

}
function makefolder($folderPathName) {
	$success = true;
	$folderArr = explode('/', $folderPathName);
	$folderNum = count($folderArr);
	if ($folderNum > 0) {
		$path = array();
		foreach ($folderArr as $folder) {
			$path[] = $folder;
			$test_case = implode('/', $path);
			if ($folder == 'home')
				continue;
			if ($test_case != '') {
				if (!is_dir($test_case)) {
					$success = mkdir($test_case);
				}
			}
		}
	}
	return $success;
}

function getdownload() {
	$db = DB();
	$success = false;
// 	echo func_get_arg(0);
	switch (func_num_args()) {
		
		case 1:
			if (is_numeric(func_get_arg(0))) {
				$result = $db->query('SELECT PhysicalName,FileName,FileType FROM files_upload WHERE ID=\'' . func_get_arg(0) . '\'');
				
			} else if (!is_numeric(func_get_arg(0))) {
				$result = $db->query('SELECT PhysicalName,FileName,FileType FROM files_upload WHERE PhysicalName LIKE \'' . func_get_arg(0) . '\'');
			}
			$row = $result->result();
			if ($result->num_rows() > 0) {
				$success = array(
						'link' => _BASE_URL_ . 'file.php?file=' . $row[0]->PhysicalName,
// 						'link' => _HTTP_PATH_ . '/file.php?file=' . $row[0]->PhysicalName,
						'name' => $row[0]->FileName,
						'type' => $row[0]->FileType
				);
			}
			break;
		case 2:
			$result = $db->query('SELECT * FROM files_upload WHERE ProgramID=\'' . func_get_arg(0) . '\' AND RecordID=\'' . func_get_arg(1) . '\'');
			foreach ($result->result() as $row) {
				if ($row) {
					$success[] = array(
							'link' => _BASE_URL_ . 'file.php?file=' . $row->PhysicalName,
// 							'link' => _HTTP_PATH_ . '/file.php?file=' . $row->PhysicalName,
							'name' => $row->FileName,
							'type' => $row->FileType
					);
				}
			}
			break;
	}
	return $success;
}


?>
