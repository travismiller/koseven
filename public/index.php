<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 */
$application = 'application';

/**
 * The directory in which your modules are located.
 */
$modules = 'modules';

/**
 * The directory in which the KO7 resources are located. The system
 * directory must contain the classes/KO7.php file.
 */
$system = 'system';

/**
 * The directory in which the KO7 public files are located. The public
 * directory contains for example the index.php and .htaccess files.
 */
$public = 'public';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 */
define('EXT', '.php');

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and warnings. Enable them by using: E_ALL
 *
 * In a production environment, it is safe to ignore notices. Disable them by
 * using: E_ALL & ~E_NOTICE
 *
 * When using a legacy application, it is recommended to disable deprecated
 * notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of KO7 internals.
 */

// Set the full path to the docroot
if ( ! defined('DOCROOT'))
	define('DOCROOT', dirname(__DIR__).DIRECTORY_SEPARATOR);

// Make the application relative to the docroot, for symlink'd index.php
if ( ! is_dir($application) && is_dir(DOCROOT.$application))
	$application = DOCROOT.$application;

// Make the modules relative to the docroot, for symlink'd index.php
if ( ! is_dir($modules) && is_dir(DOCROOT.$modules))
	$modules = DOCROOT.$modules;

// Make the system relative to the docroot, for symlink'd index.php
if ( ! is_dir($system) && is_dir(DOCROOT.$system))
	$system = DOCROOT.$system;

// Make the public relative to the docroot, for symlink'd index.php
if ( ! is_dir($public) && is_dir(DOCROOT.$public))
	$public = DOCROOT.$public;

// Define the absolute paths for configured directories
define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);
define('PUBPATH', realpath($public).DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($application, $modules, $system, $public);

if (file_exists('install'.EXT))
{
	// Load the installation check
	return include 'install'.EXT;
}

/**
 * Define the start time of the application, used for profiling.
 */
if ( ! defined('KO7_START_TIME'))
{
	define('KO7_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if ( ! defined('KO7_START_MEMORY'))
{
	define('KO7_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH.'bootstrap'.EXT;

if (PHP_SAPI === 'cli') // Try and load minion
{
	class_exists('Minion_Task') OR die('Please enable the Minion module for CLI support.');
	set_exception_handler(['Minion_Exception', 'handler']);

	Minion_Task::factory(Minion_CLI::options())->execute();
}
else
{
	/**
	 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	 * If no source is specified, the URI will be automatically detected.
	 */
	echo Request::factory(TRUE, [], FALSE)
		->execute()
		->send_headers(TRUE)
		->body();
}
