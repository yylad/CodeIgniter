<?php
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
 * @since		Version 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Common Functions
 *
 * Loads the base classes and executes the request.
 *
 * @package		CodeIgniter
 * @subpackage	CodeIgniter
 * @category	Common Functions
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/
 */

// ------------------------------------------------------------------------

if ( ! function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is greater then the supplied value
	 *
	 * Since there are a few places where we conditionally test for PHP > 5.3
	 * we'll set a static variable.
	 *
	 * @param	string
	 * @return	bool	TRUE if the current version is $version or higher
	 */
	function is_php($version = '5.3.0')
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = (version_compare(PHP_VERSION, $version) >= 0);
		}

		return $_is_php[$version];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_really_writable'))
{
	/**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @param	string
	 * @return	void
	 */
	function is_really_writable($file)
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR === '/' && (bool) @ini_get('safe_mode') === FALSE)
		{
			return is_writable($file);
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if (is_dir($file))
		{
			$file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));
			if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
			{
				return FALSE;
			}

			fclose($fp);
			@chmod($file, DIR_WRITE_MODE);
			@unlink($file);
			return TRUE;
		}
		elseif ( ! is_file($file) OR ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		return TRUE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('load_class'))
{
	/**
	 * Class registry
	 *
	 * This function acts as a singleton. If the requested class does not
	 * exist it is instantiated and set to a static variable. If it has
	 * previously been instantiated the variable is returned.
	 *
	 * @param	string	the class name being requested
	 * @param	string	the directory where the class should be found
	 * @param	string	the class name prefix
	 * @return	object
	 */
	function &load_class($class, $directory = 'libraries', $prefix = 'CI_')
	{
		static $_classes = array();

		// Does the class exist? If so, we're done...
		if (isset($_classes[$class]))
		{
			return $_classes[$class];
		}

		$name = FALSE;

		// Look for the class first in the local application/libraries folder
		// then in the native system/libraries folder
		foreach (array(APPPATH, BASEPATH) as $path)
		{
			if (file_exists($path.$directory.'/'.$class.'.php'))
			{
				$name = $prefix.$class;

				if (class_exists($name) === FALSE)
				{
					require_once($path.$directory.'/'.$class.'.php');
				}

				break;
			}
		}

		// Is the request a class extension? If so we load it too
		if (file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
		{
			$name = config_item('subclass_prefix').$class;

			if (class_exists($name) === FALSE)
			{
				require_once(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php');
			}
		}

		// Did we find the class?
		if ($name === FALSE)
		{
			// Note: We use exit() rather then show_error() in order to avoid a
			// self-referencing loop with the Exceptions class
			set_status_header(503);
			exit('Unable to locate the specified class: '.$class.'.php');
		}

		// Keep track of what we just loaded
		is_loaded($class);

		$_classes[$class] = new $name();
		return $_classes[$class];
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('is_loaded'))
{
	/**
	 * Keeps track of which libraries have been loaded. This function is
	 * called by the load_class() function above
	 *
	 * @param	string
	 * @return	array
	 */
	function &is_loaded($class = '')
	{
		static $_is_loaded = array();

		if ($class !== '')
		{
			$_is_loaded[strtolower($class)] = $class;
		}

		return $_is_loaded;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_config'))
{
	/**
	 * Loads the main config.php file
	 *
	 * This function lets us grab the config file even if the Config class
	 * hasn't been instantiated yet
	 *
	 * @param	array
	 * @return	array
	 */
	function &get_config($replace = array())
	{
		static $_config;

		if (isset($_config))
		{
			return $_config[0];
		}

		$file_path = APPPATH.'config/config.php';
		$found = FALSE;
		if (file_exists($file_path))
		{
			$found = TRUE;
			require($file_path);
		}

		// Is the config file in the environment folder?
		if (defined('ENVIRONMENT') && file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/config.php'))
		{
			require($file_path);
		}
		elseif ( ! $found)
		{
			set_status_header(503);
			exit('The configuration file does not exist.');
		}

		// Does the $config array exist in the file?
		if ( ! isset($config) OR ! is_array($config))
		{
			set_status_header(503);
			exit('Your config file does not appear to be formatted correctly.');
		}

		// Are any values being dynamically replaced?
		if (count($replace) > 0)
		{
			foreach ($replace as $key => $val)
			{
				if (isset($config[$key]))
				{
					$config[$key] = $val;
				}
			}
		}

		return $_config[0] =& $config;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('config_item'))
{
	/**
	 * Returns the specified config item
	 *
	 * @param	string
	 * @return	mixed
	 */
	function config_item($item)
	{
		static $_config_item = array();

		if ( ! isset($_config_item[$item]))
		{
			$config =& get_config();

			if ( ! isset($config[$item]))
			{
				return FALSE;
			}
			$_config_item[$item] = $config[$item];
		}

		return $_config_item[$item];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_mimes'))
{
	/**
	 * Returns the MIME types array from config/mimes.php
	 *
	 * @return	array
	 */
	function &get_mimes()
	{
		static $_mimes = array();

		if (defined('ENVIRONMENT') && is_file(APPPATH.'config/'.ENVIRONMENT.'/mimes.php'))
		{
			$_mimes = include(APPPATH.'config/'.ENVIRONMENT.'/mimes.php');
		}
		elseif (is_file(APPPATH.'config/mimes.php'))
		{
			$_mimes = include(APPPATH.'config/mimes.php');
		}

		return $_mimes;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_https'))
{
	/**
	 * Is HTTPS?
	 *
	 * Determines if the application is accessed via an encrypted
	 * (HTTPS) connection.
	 *
	 * @return	bool
	 */
	function is_https()
	{
		return ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('show_error'))
{
	/**
	 * Error Handler
	 *
	 * This function lets us invoke the exception class and
	 * display errors using the standard error template located
	 * in application/errors/errors.php
	 * This function will send the error page directly to the
	 * browser and exit.
	 *
	 * @param	string
	 * @param	int
	 * @param	string
	 * @return	void
	 */
	function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
	{
		$_error =& load_class('Exceptions', 'core');
		echo $_error->show_error($heading, $message, 'error_general', $status_code);
		exit;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('show_404'))
{
	/**
	 * 404 Page Handler
	 *
	 * This function is similar to the show_error() function above
	 * However, instead of the standard error template it displays
	 * 404 errors.
	 *
	 * @param	string
	 * @param	bool
	 * @return	void
	 */
	function show_404($page = '', $log_error = TRUE)
	{
		$_error =& load_class('Exceptions', 'core');
		$_error->show_404($page, $log_error);
		exit;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('log_message'))
{
	/**
	 * Error Logging Interface
	 *
	 * We use this as a simple mechanism to access the logging
	 * class and send messages to be logged.
	 *
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	void
	 */
	function log_message($level = 'error', $message, $php_error = FALSE)
	{
		static $_log;

		if (config_item('log_threshold') === 0)
		{
			return;
		}

		$_log =& load_class('Log', 'core');
		$_log->write_log($level, $message, $php_error);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_status_header'))
{
	/**
	 * Set HTTP Status Header
	 *
	 * @param	int	the status code
	 * @param	string
	 * @return	void
	 */
	function set_status_header($code = 200, $text = '')
	{
		$stati = array(
			200	=> 'OK',
			201	=> 'Created',
			202	=> 'Accepted',
			203	=> 'Non-Authoritative Information',
			204	=> 'No Content',
			205	=> 'Reset Content',
			206	=> 'Partial Content',

			300	=> 'Multiple Choices',
			301	=> 'Moved Permanently',
			302	=> 'Found',
			303	=> 'See Other',
			304	=> 'Not Modified',
			305	=> 'Use Proxy',
			307	=> 'Temporary Redirect',

			400	=> 'Bad Request',
			401	=> 'Unauthorized',
			403	=> 'Forbidden',
			404	=> 'Not Found',
			405	=> 'Method Not Allowed',
			406	=> 'Not Acceptable',
			407	=> 'Proxy Authentication Required',
			408	=> 'Request Timeout',
			409	=> 'Conflict',
			410	=> 'Gone',
			411	=> 'Length Required',
			412	=> 'Precondition Failed',
			413	=> 'Request Entity Too Large',
			414	=> 'Request-URI Too Long',
			415	=> 'Unsupported Media Type',
			416	=> 'Requested Range Not Satisfiable',
			417	=> 'Expectation Failed',
			422	=> 'Unprocessable Entity',

			500	=> 'Internal Server Error',
			501	=> 'Not Implemented',
			502	=> 'Bad Gateway',
			503	=> 'Service Unavailable',
			504	=> 'Gateway Timeout',
			505	=> 'HTTP Version Not Supported'
		);

		if (empty($code) OR ! is_numeric($code))
		{
			show_error('Status codes must be numeric', 500);
		}

		is_int($code) OR $code = (int) $code;

		if (empty($text))
		{
			if (isset($stati[$code]))
			{
				$text = $stati[$code];
			}
			else
			{
				show_error('No status text available. Please check your status code number or supply your own message text.', 500);
			}
		}

		$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

		if (strpos(php_sapi_name(), 'cgi') === 0)
		{
			header('Status: '.$code.' '.$text, TRUE);
		}
		else
		{
			header(($server_protocol ? $server_protocol : 'HTTP/1.1').' '.$code.' '.$text, TRUE, $code);
		}
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('_exception_handler'))
{
	/**
	 * Exception Handler
	 *
	 * This is the custom exception handler that is declaired at the top
	 * of Codeigniter.php. The main reason we use this is to permit
	 * PHP errors to be logged in our own log files since the user may
	 * not have access to server logs. Since this function
	 * effectively intercepts PHP errors, however, we also need
	 * to display errors based on the current error_reporting level.
	 * We do that with the use of a PHP error template.
	 *
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	int
	 * @return	void
	 */
	function _exception_handler($severity, $message, $filepath, $line)
	{
		$_error =& load_class('Exceptions', 'core');

		// Should we ignore the error? We'll get the current error_reporting
		// level and add its bits with the severity bits to find out.
		if (($severity & error_reporting()) !== $severity)
		{
			return;
		}

		// Should we display the error?
		if ((bool) ini_get('display_errors') === TRUE)
		{
			$_error->show_php_error($severity, $message, $filepath, $line);
		}

		$_error->log_exception($severity, $message, $filepath, $line);
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('remove_invisible_characters'))
{
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('html_escape'))
{
	/**
	 * Returns HTML escaped variable
	 *
	 * @param	mixed
	 * @return	mixed
	 */
	function html_escape($var)
	{
		return is_array($var)
			? array_map('html_escape', $var)
			: htmlspecialchars($var, ENT_QUOTES, config_item('charset'));
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_stringify_attributes'))
{
	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param	mixed	string, array, object
	 * @param	bool
	 * @return	string
	 */
	function _stringify_attributes($attributes, $js = FALSE)
	{
		$atts = NULL;

		if (empty($attributes))
		{
			return $atts;
		}

		if (is_string($attributes))
		{
			return ' '.$attributes;
		}

		$attributes = (array) $attributes;

		foreach ($attributes as $key => $val)
		{
			$atts .= ($js) ? $key.'='.$val.',' : ' '.$key.'="'.$val.'"';
		}

		return rtrim($atts, ',');
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('function_usable'))
{
	/**
	 * Function usable
	 *
	 * Executes a function_exists() check, and if the Suhosin PHP
	 * extension is loaded - checks whether the function that is
	 * checked might be disabled in there as well.
	 *
	 * This is useful as function_exists() will return FALSE for
	 * functions disabled via the *disable_functions* php.ini
	 * setting, but not for *suhosin.executor.func.blacklist* and
	 * *suhosin.executor.disable_eval*. These settings will just
	 * terminate script execution if a disabled function is executed.
	 *
	 * @link	http://www.hardened-php.net/suhosin/
	 * @param	string	$function_name	Function to check for
	 * @return	bool	TRUE if the function exists and is safe to call,
	 *			FALSE otherwise.
	 */
	function function_usable($function_name)
	{
		static $_suhosin_func_blacklist;

		if (function_exists($function_name))
		{
			if ( ! isset($_suhosin_func_blacklist))
			{
				$_suhosin_func_blacklist = extension_loaded('suhosin')
					? array()
					: explode(',', trim(@ini_get('suhosin.executor.func.blacklist')));

				if ( ! in_array('eval', $_suhosin_func_blacklist, TRUE) && @ini_get('suhosin.executor.disable_eval'))
				{
					$_suhosin_func_blacklist[] = 'eval';
				}
			}

			return in_array($function_name, $_suhosin_func_blacklist, TRUE);
		}

		return FALSE;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_cookie'))
{
	/**
	 * Set cookie
	 *
	 * Sends a cookie, taking care to replace it in the PHP header() queue,
	 * if one with the same name already exists.
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param	string|mixed[]	$name		Cookie name or an array containing parameters
	 * @param	string		$value		Cookie value
	 * @param	int		$expire		Cookie expiration time in seconds
	 * @param	string		$domain		Cookie domain (e.g.: '.yourdomain.com')
	 * @param	string		$path		Cookie path (default: '/')
	 * @param	string		$prefix		Cookie name prefix
	 * @param	bool		$secure		Whether to only transfer cookies via SSL
	 * @param	bool		$httponly	Whether to only makes the cookie accessible via HTTP (no javascript)
	 * @return	void
	 */
	function set_cookie($name, $value = '', $expire = NULL, $path = NULL, $domain = NULL, $prefix = NULL, $secure = NULL, $httponly = NULL)
	{
		// Were the parameters passed as an array?
		if (is_array($name))
		{
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		// Sanitize our parameters
		if ($secure === NULL)
		{
			$secure = config_item('cookie_secure');
		}

		// Do we have to send the cookie at all?
		if ($secure === TRUE && ! is_https())
		{
			return;
		}

		// Prepend our name prefix
		$name = ($prefix === NULL ? config_item('cookie_prefix') : $prefix)
			.$name;

		$payload = 'Set-Cookie: '.$name.'='.urlencode($value).';';

		// If we don't have a valid expiry time - expire the cookie
		is_numeric($expire) OR $expire = -31536000;

		// Only send expiry time if it's not a zero value
		//
		// DO NOT CHANGE THIS EXPRESSION!
		// Multiple side effects can occur if you do, including:
		//
		//	!==	Ignores non-integer values
		//	>, >=	Ignore negative values
		if ($expire != 0)
		{
			// GMT is the most safe choice
			$payload .=' Expires='.gmdate('D, d-M-Y H:i:s T', time() + $expire).';'
				// RFC6265 describes the Max-Age attribute:
				//
				// - Specifies the cookie lifetime in seconds
				// - Not supported by all user agents (naturally, as it was introduced in 2011)
				// - User agents that don't support it will simply ignore it
				// - User agents that support it MUST use it instead of Expires, when present
				//
				// Reference: http://tools.ietf.org/rfc/rfc6265.txt
				//
				// For the above reasons, and because some user agents might not calculate
				// timezone differences properly (e.g. due to the system timezone setting
				// not being correct), sending the Max-Age attribute is our safest option.
				//
				// The downside is - it doesn't accept negative values.
				.($expire > 0 ? ' Max-Age='.$expire.';' : '');
		}

		$payload .= ' Path='.(empty($path) ? config_item('cookie_path') : $path).';'
			.' Domain='.(empty($domain) ? config_item('cookie_domain') : $domain)
			.($secure ? '; Secure' : ''); // We've already initialized this one

		if ($httponly === NULL)
		{
			$httponly = config_item('cookie_httponly');
		}

		$payload .= ($httponly ? '; HttpOnly' : '');

		// Now, let's go through the headers to see if we've already sent any cookies.
		// To allow usage of header_remove() later, iterate in a decremental order.
		$queue = array();
		for ($headers = headers_list(), $i = count($headers) - 1; $i > -1; $i--)
		{
			// Is it a cookie? Get its name and cache it
			if (sscanf($headers[$i], 'Set-Cookie: %[^=]', $cookie_name) !== 1)
			{
				$queue[$cookie_name] = $headers[$i];
			}
		}

		// If a matching cookie name doesn't exist - just send,
		// if it does and IF it is the only cookie - replace it.
		if (($re = isset($queue[$name])) === FALSE OR ($re = (count($queue) === 1)))
		{
			header($payload, $re);
			return;
		}

		// OK, so a matching cookie name is already queued and we have other cookies as well.
		// We need to replace the matching cookie with our own.
		//
		// We'll be relying on the $re variable to determine our procedure's state and behavior:
		//
		//	(bool) Search for the right cookie to replace and when found:
		//
		//		TRUE: Clear all of the headers
		//		FALSE: Use header_remove() to only remove the relevant headers
		//
		//	(null) Re-send previously removed headers
		//
		$re = ! is_php('5.3');
		do
		{
			// Re-send the removed Set-Cookie headers.
			// This will only happen after our cookie was sent.
			if ($re === NULL)
			{
				header($payload);
				if (prev($queue) !== FALSE)
				{
					continue;
				}
				else
				{
					break;
				}
			}

			// Do we use header_remove() or do we have to replace all Set-Cookie headers?
			if ($re === FALSE)
			{
				header_remove('Set-Cookie');
			}

			// If the cookie name matches ours - replace the queue header entry with our own
			if (key($queue) === $name)
			{
				header($payload, $re);

				// If we didn't use header_replace(), remove the old cookie matching our
				// name so it doesn't get re-sent and set the pointer to the last entry.
				if ($re === TRUE)
				{
					unset($queue[$name]);
					end($queue);
				}

				// Switch to re-sending more
				$re = NULL;
				continue;
			}
		}
		// We won't get to this point once $re is set to NULL
		while (next($queue) !== FALSE);
	}
}

/* End of file Common.php */
/* Location: ./system/core/Common.php */