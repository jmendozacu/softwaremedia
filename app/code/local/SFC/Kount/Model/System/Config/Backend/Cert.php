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

class SFC_Kount_Model_System_Config_Backend_Cert extends Mage_Core_Model_Config_Data
{
    /**
     * Constants
     */
    const FILE_NAME = 'cert';

    /**
     * Process additional data before save config
     *
     * @return SFC_Kount_Model_System_Config_Backend_Cert
     */
    protected function _beforeSave()
    {
        // Parent
        parent::_beforeSave();

        // Helper
        $oPathsHelper = new SFC_Kount_Helper_Paths();
        $oIo = new Varien_Io_File();

        // Get path
        $sDataPath = $oPathsHelper->getDataPath();
        $bResult = $oIo->open(array('path' => $sDataPath));
        if(!$bResult) {
            Mage::throwException('Failed to save certificate file!');
        }
        // Incoming value
        $aValue = $this->getValue();

        // What are we doing?
        // -- Deleting?
        if (is_array($aValue) && !empty($aValue['delete'])) {
            $bResult = $oIo->rm($sDataPath . $aValue['value']);
            if(!$bResult) {
                Mage::throwException('Failed to save certificate file!');
            }
            $this->setValue('');
        }
        // -- Changing
        else {
            if (is_string($aValue)) {

                // -- -- Make a new file
                $i = 0;
                do {
                    $i++;
                    $sFilename = self::FILE_NAME . '_' . $i . '.pem';
                    $sFilePath = $sDataPath . $sFilename;
                }
                while ($oIo->fileExists($sFilePath));
                $this->setValue($sFilename);
                // -- -- No file set?
                if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
                    Mage::throwException('Failed to save certificate file!');
                    return $this;
                }

                // -- -- Save
                $sPath = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
                if ($sPath && $oIo->fileExists($sPath)) {
                    $bResult = $oIo->mv($sPath, $sFilePath);
                    if(!$bResult) {
                        Mage::throwException('Failed to save certificate file!');
                    }
                    $bResult = $oIo->chmod($sFilePath, 0644);
                    if(!$bResult) {
                        Mage::throwException('Failed to save certificate file!');
                    }
                }
            }
        }

        return $this;
    }

}
