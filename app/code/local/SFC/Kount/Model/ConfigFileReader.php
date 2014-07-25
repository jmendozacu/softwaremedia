<?php

class SFC_Kount_Model_ConfigFileReader
{

    /**
     * An instance of this class.
     * @var SFC_Kount_Model_ConfigFileReader
     */
    protected static $instance = null;

    /**
     * Private constructor to prevent direct object instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Get an instance of this class.
     * @return SFC_Kount_Model_ConfigFileReader
     */
    public static function instance()
    {
        if (null == self::$instance) {
            self::$instance = new SFC_Kount_Model_ConfigFileReader();
        }

        return self::$instance;
    }

    /**
     * Get static RIS settings from Magento config
     * @return array settings Hash map
     */
    public function getSettings()
    {
        /** @var SFC_Kount_Helper_Paths $helperPaths */
        $helperPaths = Mage::helper('kount/paths');
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        // Build array of settings
        $settings = array();
        $settings['MERCHANT_ID'] = Mage::getStoreConfig('kount/account/merchantnum');
        $settings['URL'] = Mage::getStoreConfig('kount/servers/ris');
        // Logging config
        if (Mage::getStoreConfig('dev/log/active') == '1' && Mage::getStoreConfig('kount/log/enable') == '1') {
            $settings['LOGGER'] = 'SIMPLE';
            $settings['SIMPLE_LOG_LEVEL'] = 'DEBUG';
            $settings['SIMPLE_LOG_FILE'] = SFC_Kount_Helper_Paths::KOUNT_LOG_FILE;
            $settings['SIMPLE_LOG_PATH'] = Mage::getBaseDir('log');
        }
        else {
            $settings['LOGGER'] = 'NOP';
            $settings['SIMPLE_LOG_LEVEL'] = 'FATAL';
            $settings['SIMPLE_LOG_FILE'] = null;
            $settings['SIMPLE_LOG_PATH'] = null;
        }
        $settings['PEM_CERTIFICATE'] = $helperPaths->getCertFilePath();
        $settings['PEM_KEY_FILE'] = $helperPaths->getKeyFilePath();
        $settings['PEM_PASS_PHRASE'] = $coreHelper->decrypt(trim(Mage::getStoreConfig('kount/cert/password')));
        $settings['CONNECT_TIMEOUT'] = 10;

        // Return the settings
        return $settings;
    }

    /**
     * Get a named configuration setting.
     * @param string $name Get a named configuration file setting
     * @return string
     * @throws Exception If the specified setting name does not exist.
     */
    public function getConfigSetting($name)
    {
        $settings = $this->getSettings();
        if (array_key_exists($name, $settings)) {
            return $settings[$name];
        }
        throw new Exception("The configuration setting [{$name}] is not defined");
    }

}
