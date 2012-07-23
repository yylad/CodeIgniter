<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 2.1.0
 * @filesource
 */

/**
 * Native Database Interface Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the query builder
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Interfaces
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
abstract class CI_DB_native_driver extends CI_DB {

	public $function_prefix;

	// The character used to escaping
	protected $_escape_char = '"';

	// clause and character used for LIKE escape sequences
	protected $_like_escape_str = " ESCAPE '%s' ";
	protected $_like_escape_chr = '!';

	/**
	 * Load the result drivers
	 *
	 * @return	string	the name of the result class
	 */
	protected function _load_rdriver()
	{
		$driver = 'CI_DB_native_'.$this->dbdriver.'_result';

		if ( ! class_exists($driver))
		{
			require_once(BASEPATH.'database/DB_result.php');
			require_once(BASEPATH.'database/drivers/native_result.php');
			require_once(BASEPATH.'database/drivers/native/'.$this->dbdriver.'_result.php');
		}

		return $driver;
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @return	int
	 */
	public function affected_rows()
	{
		$func = $this->function_prefix.'_affected_rows';
		return @$func($this->conn_id);
	}

}

/* End of file native_driver.php */
/* Location: ./system/database/drivers/native_driver.php */