<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the BETTER STORE SEARCH
 * License, which is available at this URL: http://www.betterstoresearch.com/docs/bss_license.txt
 *
 * DISCLAIMER
 * By adding to, editing, or in any way modifying this code, WDCA is not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code. By adding to, editing, or in any way modifying this code, the Licensee terminates any agreement of support
 * offered by WDCA, outlined in the provided Sweet Tooth License.  Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time WDCA spent  during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension. If you did not receive a copy of the license, please send an email to
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy immediately.
 *
 * @category   [TBT]
 * @package    [TBT_RewardsReferral]
 * @copyright  Copyright (c) 2012 WDCA (http://www.wdca.ca)
 * @license    http://www.betterstoresearch.com/docs/bss_license.txt
 */

/**
 *
 * @category   TBT
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

class TBT_RewardsReferral_Model_Mysql4_Setup extends Mage_Core_Model_Resource_Setup
{

    protected $_firstInstallation = false;

    /**
     * Dispatches _preApply() and _postApply() before and after it falls back to its parent
     * method, which will:
     * @return TBT_RewardsReferral_Model_Mysql4_Setup
     */
    public function applyUpdates()
    {
        $dbVer = $this->_getResource()->getDbVersion($this->_resourceName);
        $configVer = (string) $this->_moduleConfig->version;

        $updatesApplied = false;

        if ($dbVer === false) {
            $this->_firstInstallation = true;
        }

        if ($dbVer !== false) {
            $status = version_compare($configVer, $dbVer);
            if ($status == self::VERSION_COMPARE_GREATER) {
                $updatesApplied = true;
            }
        } elseif ($configVer) {
            $updatesApplied = true;
        }

        if ($updatesApplied) {
            $this->_preApply();
        }

        parent::applyUpdates();

        if ($updatesApplied) {
            $this->_postApply();
        }

        return $this;
    }

    public function isFirstInstallation()
    {
        return $this->_firstInstallation;
    }

    /**
     * Runs before install/update SQL has been executed
     * @return TBT_RewardsReferral_Model_Mysql4_Setup
     */
    protected function _preApply()
    {
        return $this;
    }

    /**
     * Runs after install/update SQL has been executed
     * @return TBT_RewardsReferral_Model_Mysql4_Setup
     */
    protected function _postApply()
    {
        return $this;
    }
}