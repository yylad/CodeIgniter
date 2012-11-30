################
Common Functions
################

CodeIgniter uses a few functions for its operation that are globally
defined, and are available to you at any point. These do not require
loading any libraries or helpers.

is_php()
========

.. php:function:: is_php($version = '5.3.0')

	:param	string	$version: Version number
	:returns:	bool

Determines of the PHP version being used is greater than the
supplied version number.

Example::

	if (is_php('5.3'))
	{
		$str = quoted_printable_encode($str);
	}

Returns boolean TRUE if the installed version of PHP is equal to or
greater than the supplied version number. Returns FALSE if the installed
version of PHP is lower than the supplied version number.

is_really_writable()
====================

.. php:function:: is_really_writeable($file)

	:param	string	$file: File path
	:returns:	bool

``is_writable()`` returns TRUE on Windows servers when you really can't
write to the file as the OS reports to PHP as FALSE only if the
read-only attribute is marked.

This function determines if a file is actually writable by attempting
to write to it first. Generally only recommended on platforms where
this information may be unreliable.

Example::

	if (is_really_writable('file.txt'))
	{
		echo "I could write to this if I wanted to";
	}
	else
	{
		echo "File is not writable";
	}

config_item()
=============

.. php:function:: config_item($key)

	:param	string	$key: Config item key
	:returns:	mixed

The :doc:`Config Library <../libraries/config>` is the preferred way of
accessing configuration information, however ``config_item()`` can be used
to retrieve single keys. See :doc:`Config Library <../libraries/config>`
documentation for more information.

.. important:: This function only returns values set in your configuration
	files. It does not take into account config values that are
	dynamically set at runtime.

show_error()
============

.. php:function:: show_error($message, $status_code, $heading = 'An Error Was Encountered')

	:param	mixed	$message: Error message
	:param	int	$status_code: HTTP Response status code
	:param	string	$heading: Error page heading
	:returns:	void

This function calls ``CI_Exception::show_error()``. For more info,
please see the :doc:`Error Handling <errors>` documentation.

show_404()
==========

.. php:function:: show_404($page = '', $log_error = TRUE)

	:param	string	$page: URI string
	:param	bool	$log_error: Whether to log the error
	:returns:	void

This function calls ``CI_Exception::show_404()``. For more info,
please see the :doc:`Error Handling <errors>` documentation.

log_message()
=============

.. php:function:: log_message($level = 'error', $message, $php_error = FALSE)

	:param	string	$level: Log level
	:param	string	$message: Message to log
	:param	bool	$php_error: Whether we're loggin a native PHP error message
	:returns:	void

This function is an alias for ``CI_Log::write_log()``. For more info,
please see the :doc:`Error Handling <errors>` documentation.

set_status_header()
===============================

.. php:function:: set_status_header($code, $text = '')

	:param	int	$code: HTTP Reponse status code
	:param	string	$text: A custom message to set with the status code
	:returns:	void

Permits you to manually set a server status header. Example::

	set_status_header(401);
	// Sets the header as:  Unauthorized

`See here <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>`_ for
a full list of headers.

remove_invisible_characters()
=============================

.. php:function:: remove_invisible_characters($str, $url_encoded = TRUE)

	:param	string	$str: Input string
	:param	bool	$url_encoded: Whether to remove URL-encoded characters as well
	:returns:	string

This function prevents inserting NULL characters between ASCII
characters, like Java\\0script.

Example::

	remove_invisible_characters('Java\\0script');
	// Returns: 'Javascript'

html_escape()
=============

.. php:function:: html_escape($var)

	:param	mixed	$var: Variable to escape
			(string or array)
	:returns:	mixed

This function acts as an alias for PHP's native ``htmlspecialchars()``
function, with the advantage of being able to accept an array of strings.

It is useful in preventing Cross Site Scripting (XSS).

get_mimes()
===========

.. php:function:: get_mimes()

	:returns:	array

This function returns a *reference* to the MIMEs array from
*application/config/mimes.php*.

is_https()
==========

.. php:function:: is_https()

	:returns:	bool

Returns TRUE if a secure (HTTPS) connection is used and FALSE
in any other case (including non-HTTP requests).

function_usable()
=================

.. php:function:: function_usable($function_name)

	:param	string	$function_name: Function name
	:returns:	bool

Returns TRUE if a function exists and is usable, FALSE otherwise.

This function runs a ``function_exists()`` check and if the
`Suhosin extension <http://www.hardened-php.net/suhosin/>` is loaded,
checks if it doesn't disable the function being checked.

It is useful if you want to check for the availability of functions
such as ``eval()`` and ``exec()``, which are dangerous and might be
disabled on servers with highly restrictive security policies.

set_cookie()
============

.. php:function:: set_cookie($name, $value = '', $expire = NULL, $domain = NULL, $path = NULL, $prefix = NULL, $secure = NULL, $httponly = NULL)

	:param	mixed	$name: Cookie name or an array of parameters
	:param	string	$value: Cookie value
	:param	int	$expire: Cookie expire time in seconds
	:param	string	$domain: Cookie domain
	:param	string	$path: Cookie path
	:param	string	$prefix: Cookie name prefix
	:param	bool	$secure: Whether to only send the cookie over HTTPS
	:param	bool	$httponly: Whether to hide the cookie from non-HTTP resources (e.g. JavaScript)
	:returns:	void

Sets a cookie or replaces one already existing in the PHP headers
queue. If you leave any of the function's parameters (except for
``$name`` and ``$value``) set to NULL, their corresponding values
will be taken from your **application/config/config.php** settings.

Note that the expiration time is in seconds, counted from the
**current time** - you must not pass a full UNIX-timestamp to this
function.
If you set the expire time to 0, then the browser will make the
cookie available *until the current session expires*. This usually
means until the browser is closed, but browser interpretations
of this rule may differ.
Negative values are also accepted, although if you need to force
the deletion of a cookie, you should just pass an empty ``$value``
and any *non-numeric* value for the expiration time.

.. note:: This function will set both *Expires* (using timezone GMT)
	and the relatively new *Max-Age* cookie attribute.

There are 2 ways to pass parameters to ``set_cookie()``:

  -  Discrete parameters::

	set_cookie('CookieName', 'string', 86400, '.yourdomain.com', '/', 'my', TRUE, TRUE);

  -  Associative array::

	$cookie = array(
		'name'    => 'CookieName',
		'value'   => 'string',
		'expire'  => 86400,
		'domain'  => '.yourdomain.com',
		'path'    => '/',
		'prefix'  => 'my',
		'secure'  => TRUE,
		'httponly => TRUE
	);

	set_cookie($cookie);

Only the ``$name`` and ``$value`` parameters are required.

Both of the above examples will set a cookie named "myCookieName"
with a value of "string" that will be available to all websites
hosted on a subdomain of yourdomain.com accessed via HTTPS for a
period of 85400 seconds (24 hours), and the user's browser won't
allow JavaScript to access it. Or if you'd like to see the raw
HTTP Set-Cookie header::

	// (assuming that the current time is: 2012-12-01 16:08:50, CET)

	Set-Cookie: myCookieName=string; Expires=Sun, 02-Dec-2012 15:08:50 GMT; Max-Age=86400; Domain=.yourdomain.com; Path=/; Secure; HttpOnly


.. note:: If you set ``$secure`` to TRUE, then ``set_cookie()``
	will not only set the *Secure* cookie attribute, but it
	will not send it over a non-HTTPS connection.

For site-wide cookies regardless of how your site is requested,
you should prepend your ``$domain`` with a period, like this:

	.yourdomain.com

However, note that some browsers may not send the cookie back, if
your website is not accessed via a subdomain (http://yourdomain.com/).

The prefix is only needed if you need to avoid name collisions with
other identically named cookies that are accessible by your domain.