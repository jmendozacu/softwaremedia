<?php
/**
 * @package Kount_Log
 * @subpackage Binding
 */

if (!defined('BINDING_BASE')) {
	define('BINDING_BASE', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
}

require_once BINDING_BASE . 'Logger.php';
require_once(BINDING_BASE . '..' . DS . '..' . DS . '..' . DS . 'app' . DS . 'Mage.php');

/**
 * Implementation of a No OPeration logger. Just silently discards logging.
 * @package Kount_Log
 * @subpackage Binding
 * @author Kount <custserv@kount.com>
 * @version $Id: MagentoLogger.php 10540 2010-07-02 17:47:35Z mmn $
 * @copyright 2010 Keynetics Inc
 */
class Kount_Log_Binding_MagentoLogger implements Kount_Log_Binding_Logger
{

	/**
	 * Constructor for Nop logger.
	 * @param string $name Logger name
	 */
	public function __construct()
	{
	}

	/**
	 * Discard a debug level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function debug($message, $exception = null)
	{
		Mage::log($message, Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	}

	/**
	 * Discard an info level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function info($message, $exception = null)
	{
		Mage::log($message, Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	}

	/**
	 * Discard a warn level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function warn($message, $exception = null)
	{
		Mage::log($message, Zend_Log::WARN, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	}

	/**
	 * Discard an error level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function error($message, $exception = null)
	{
		Mage::log($message, Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	}

	/**
	 * Discard a fatal level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function fatal($message, $exception = null)
	{
		Mage::log($message, Zend_Log::CRIT, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	}

}