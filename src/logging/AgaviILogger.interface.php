<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviILogger is the interface for all Logger implementations
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Steffen Gransow <agavi@mivesto.de>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface AgaviILogger
{
	/**
	 * System is unusable or urgent alert messages.
	 *
	 * @since      1.0.8
	 */
	const EMERGENCY = 1;

	/**
	 * Fatal level. Same as EMERGENCY.
	 *
	 * @since      0.9.0
	 */
	const FATAL = 1;

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website is down, services are unavailable etc.
	 *
	 * This should trigger SMS alerts or similar and wake someone up.
	 *
	 * @since      1.0.8
	 */
	const ALERT = 2;

	/**
	 * Critical condition messages.
	 *
	 * Example: Application component unavailable or unexpected exceptions.
	 *
	 * @since      1.0.8
	 */
	const CRITICAL = 4;

	/**
	 * Error level messages for runtime errors.
	 *
	 * @since      0.9.0
	 */
	const ERROR = 8;

	/**
	 * Warning level for exceptional occurrences that are not errors.
	 *
	 * Examples: Use of deprecated APIs, poor use of an API
	 * or undesirable things that are not necessarily wrong.
	 *
	 * @since      1.0.8
	 */
	const WARNING = 16;

	/**
	 * Warning level. Same as WARNING.
	 *
	 * @since      0.9.0
	 */
	const WARN = 16;

	/**
	 * Normal but significant conditions or uncommon events.
	 *
	 * @since      1.0.8
	 */
	const NOTICE = 32;

	/**
	 * Information level messages for interesting events.
	 *
	 * Examples: User logs in or validation fails.
	 *
	 * @since      0.9.0
	 */
	const INFO = 64;

	/**
	 * Debug level messages with detailed debug information.
	 *
	 * @since      0.9.0
	 */
	const DEBUG = 128;

	/**
	 * Verbose debug level messages including extensive debug information.
	 *
	 * @since      1.0.8
	 */
	const TRACE = 256;

	/**
	 * All levels (2^32-1).
	 *
	 * @since      0.11.0
	 */
	const ALL = 4294967295;

	/**
	 * Log a message.
	 *
	 * @param      AgaviLoggerMessage A Message instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function log(AgaviLoggerMessage $message);

	/**
	 * Set an appender.
	 *
	 * If an appender with the name already exists, an exception will be thrown.
	 *
	 * @param      string              An appender name.
	 * @param      AgaviLoggerAppender An Appender instance.
	 *
	 * @throws     <b>AgaviLoggingException</b> If an appender with the name
	 *                                          already exists.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setAppender($name, AgaviLoggerAppender $appender);

	/**
	 * Returns a list of appenders for this logger.
	 *
	 * @return     array An associative array of appender names and instances.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getAppenders();

	/**
	 * Set the level.
	 *
	 * @param      int A log level.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setLevel($level);

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function shutdown();

}

?>
