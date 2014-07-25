<?php
// @codingStandardsIgnoreStart
/**
 * StoreFront Consulting Kount Magento Extension
 *
 * PHP version 5
 *
 * @category  SFC
 * @package   SFC_Kount
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 *
 */
// @codingStandardsIgnoreEnd

class SFC_Kount_Helper_Paths extends Mage_Core_Helper_Abstract
{
    /**
     * Log file
     */
    const KOUNT_LOG_FILE = 'kount.log';

    /**
     * Get export path
     * @return string or NIL
     */
    public function getDataPath()
    {

        try {

            // Io
            $oIo = new Varien_Io_File();

            // Get path
            $sPath = Mage::getConfig()->getVarDir() . DS . Mage::getStoreConfig('kount/paths/data');

            // Exist?
            $bResult = $oIo->checkAndCreateFolder($sPath);
            if (!$bResult) {
                Mage::throwException('Failed to locate data path: "' . $sPath . '"!');
            }

            return $sPath;
        }
        catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            throw $e;
        }
    }

    /**
     * Get ris server
     * @return string
     */
    public function getRisServer()
    {
        return Mage::getStoreConfig('kount/servers/ris');
    }

    /**
     * Get data collector server
     * @return string
     */
    public function getDataCollectorServer()
    {
        return Mage::getStoreConfig('kount/servers/datacollector');
    }

    /**
     * Get certificate file path
     * @return string
     */
    public function getCertFilePath()
    {
        return $this->getDataPath() . Mage::getStoreConfig('kount/cert/cert');
    }

    /**
     * Get key file path
     * @return string
     */
    public function getKeyFilePath()
    {
        return $this->getDataPath() . Mage::getStoreConfig('kount/cert/key');
    }

    /**
     * Validate configuration
     * @return boolean
     */
    public function validateConfig()
    {
        // Helper
        $oIo = new Varien_Io_File();

        // Log
        Mage::log('Checking for valid config.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Log configuration info
        Mage::log('==== Extension Configuration ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('=================================', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured Merchant Num: ' . Mage::getStoreConfig('kount/account/merchantnum'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured website: ' . Mage::getStoreConfig('kount/account/website'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured Test Mode: ' . Mage::getStoreConfig('kount/account/test'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured ris: ' . Mage::getStoreConfig('kount/servers/ris'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured datacollector: ' . Mage::getStoreConfig('kount/servers/datacollector'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured awc: ' . Mage::getStoreConfig('kount/servers/awc'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured cert: ' . Mage::getStoreConfig('kount/cert/cert'), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Configured key: ' . Mage::getStoreConfig('kount/cert/key'), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('=================================', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Validate multibyte
        if (!function_exists('mb_strpos')) {

            Mage::log(
                'Multibyte string is not installed for your PHP version. Kount\'s Sdk and Api require this to run this extension.',
                Zend_Log::INFO,
                SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            return false;
        }


        // Lets get required values
        $aValues = array();
        $aValues[] = Mage::getStoreConfig('kount/account/merchantnum');
        $aValues[] = Mage::getStoreConfig('kount/account/website');
        $aValues[] = Mage::getStoreConfig('kount/servers/ris');
        $aValues[] = Mage::getStoreConfig('kount/servers/datacollector');
        $aValues[] = Mage::getStoreConfig('kount/servers/awc');
        $aValues[] = Mage::getStoreConfig('kount/cert/password');
        $aValues[] = Mage::getStoreConfig('kount/cert/cert');
        $aValues[] = Mage::getStoreConfig('kount/cert/key');

        // Validate
        foreach ($aValues as $sValue) {
            if (!$sValue || strlen(trim($sValue)) == 0) {

                // Log
                Mage::log("Failed for config value: {$sValue}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return false;
            }
        }

        // Check certificate
        if (!$oIo->fileExists($this->getCertFilePath())) {
            Mage::log("Failed for key file: {$this->getCertFilePath()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            return false;
        }

        // Check key
        if (!$oIo->fileExists($this->getKeyFilePath())) {
            Mage::log("Failed for key file: {$this->getKeyFilePath()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            return false;
        }

        return true;
    }
}