<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Initialize the database
 *
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 * @param 	string
 * @param 	bool	Determines if active record should be used or not
 */

function &DB($active_record_override = NULL)
{
	$params = array(
		'dbdriver'	=> _DB_TYPE_,
		'hostname'	=> _DB_HOST_,
		'username'	=> _DB_USER_,
		'password'	=> _DB_PASS_,
		'database'	=> _DB_DATA_
	);

	if ($active_record_override !== NULL)
	{
		$active_record = $active_record_override;
	}

	require_once('DB_driver.php');

	if ( ! isset($active_record) OR $active_record == TRUE)
	{
		require_once('DB_active_rec.php');

		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_active_record { }');
		}
	}
	else
	{
		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_driver { }');
		}
	}

	require_once('drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver.php');

	// Instantiate the DB adapter
	//$driver = 'CI_DB_'.$params['dbdriver'].'_driver';
	//$DB = new $driver($params);
	
	if ( ! class_exists('eofficeDB')){
		eval('
		class eofficeDB extends CI_DB_'.$params['dbdriver'].'_driver {
			function updateX(){
			}
		}
		');
	}
	$DB = new eofficeDB($params);

	if ($DB->autoinit == TRUE)
	{
		$DB->initialize();
	}

	if (isset($params['stricton']) && $params['stricton'] == TRUE)
	{
		$DB->query('SET SESSION sql_mode="STRICT_ALL_TABLES"');
	}

	return $DB;
}



/* End of file DB.php */
/* Location: ./system/database/DB.php */