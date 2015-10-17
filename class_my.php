<?php
class myClass{
	var $db;

	function loadDB($params)
	{
		if(!class_exists('myDB')){
			eval('
			class myDB extends CI_DB_'.$params['dbdriver'].'_driver {
				function DUPinsert($table = "", $where = NULL, $limit = FALSE)
				{
					if ($where != NULL){
						$this->where($where);
					}
					
					$table_log=$this->_protect_identifiers($table."_log", TRUE, NULL, FALSE);
					$table=$this->_protect_identifiers($table, TRUE, NULL, FALSE);
					
					$sql = "INSERT INTO ".$table_log." SELECT NULL AS Log_ID,".$table.".* FROM ".$table;
					$sql .= ($this->ar_where != "" AND count($this->ar_where) >=1) ? " WHERE ".implode(" ", $this->ar_where) : "";
					
					$this->query($sql);
				}
				
				function edit($table = "", $set = NULL, $where = NULL, $limit = NULL){
					$where2=$where;
					$sql="CREATE TABLE IF NOT EXISTS ".$table."_log(Log_ID INT AUTO_INCREMENT,PRIMARY KEY(Log_ID)) SELECT * FROM ".$table." WHERE 1!=1";
					$this->query($sql);
					
					$this->DUPinsert($table,$where);
					
					$this->update($table,$set,$where2,$limit);
				}
			}
			');
		}
		return new myDB($params);
	}
	
	function DB($active_record_override = NULL)
	{
		$params = array(
			'dbdriver'	=> _DB_TYPE_,
			'hostname'	=> _DB_HOST_,
			'username'	=> _DB_USER_,
			'password'	=> _DB_PASS_,
			'database'	=> _DB_DATA_
		);
	
		if ($active_record_override !== NULL){
			$active_record = $active_record_override;
		}
		require_once('lib/database/DB_driver.php');
		if (!isset($active_record) OR $active_record == TRUE){
			require_once('lib/database/DB_active_rec.php');
	
			if (!class_exists('CI_DB')){
				eval('class CI_DB extends CI_DB_active_record { }');
			}
		}
		else{
			if (!class_exists('CI_DB')){
				eval('class CI_DB extends CI_DB_driver { }');
			}
		}
		require_once('lib/database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver.php');
		$DB = $this->loadDB($params);
		if ($DB->autoinit == TRUE){
			$DB->initialize();
		}
		if (isset($params['stricton']) && $params['stricton'] == TRUE){
			$DB->query('SET SESSION sql_mode="STRICT_ALL_TABLES"');
		}
		return $DB;
	}
}

?>