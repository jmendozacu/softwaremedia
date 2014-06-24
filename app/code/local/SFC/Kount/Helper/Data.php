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

class SFC_Kount_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Data collector dimensions
     */
    const RIS_DATACOLLECTOR_WIDTH = 1;
    const RIS_DATACOLLECTOR_HEIGHT = 1;

    /**
     * Order status'
     */
    const ORDER_STATUS_KOUNT_REVIEW = 'review_kount';
    const ORDER_STATUS_KOUNT_REVIEW_LABEL = 'Review';
    const ORDER_STATUS_KOUNT_DECLINE = 'decline_kount';
    const ORDER_STATUS_KOUNT_DECLINE_LABEL = 'Decline';

    /**
     * Test if this Magento Edition / Version is fully compatible
     */
    public function isCompatibleVersion()
    {
        // Get Version info
        $aVersion = Mage::getVersionInfo();
        // Test
        if ((intval($aVersion['major']) == 1) &&
            ((intval($aVersion['minor']) >= 10 && intval($aVersion['minor']) <= 13) ||
                (intval($aVersion['minor']) >= 5 && intval($aVersion['minor']) <= 8))
        ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Test if this Magento Edition / Version is partially compatible
     */
    public function isPartiallyCompatibleVersion()
    {
        // Get Version info
        $aVersion = Mage::getVersionInfo();
        // Test
        if ((intval($aVersion['major']) == 1) &&
            ((intval($aVersion['minor']) == 9) ||
                (intval($aVersion['minor']) == 4))
        ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Put order on hold / at Kount Review status
     *
     * @param Mage_Sales_Model_Order $oOrder Order to operate on
     */
    public function setOrderToKountReview($oOrder)
    {
        // -- Log
        Mage::log('Putting order to Kount Review', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Save order state & status before we start
        if ($this->isCompatibleVersion()) {
            // Save prior order state & status
            $oOrder->setHoldBeforeState($oOrder->getState());
            $oOrder->setHoldBeforeStatus($oOrder->getStatus());
        }

        // Get appropriate order status
        $orderStatus = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
        if ($this->isCompatibleVersion()) {
            $orderStatus = self::ORDER_STATUS_KOUNT_REVIEW;
        }

        // Set state & status on Magento order
        $oOrder->setState(
            Mage_Sales_Model_Order::STATE_HOLDED,
            $orderStatus,
            SFC_Kount_Helper_RisRequest::RIS_MESSAGE_ORDERREVIEW,
            false
        );
        $oOrder->save();
    }

    /**
     * Put order to Kount Decline status
     *
     * @param Mage_Sales_Model_Order $oOrder Order to operate on
     */
    public function setOrderToKountDecline($oOrder)
    {
        // -- Log
        Mage::log('Putting order to Kount Decline', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Save order state & status before we start
        if ($this->isCompatibleVersion()) {
            // Save prior order state & status
            $oOrder->setHoldBeforeState($oOrder->getState());
            $oOrder->setHoldBeforeStatus($oOrder->getStatus());
        }

        // Get appropriate order status
        $orderStatus = Mage_Sales_Model_Order::STATE_HOLDED;
        if ($this->isCompatibleVersion()) {
            $orderStatus = self::ORDER_STATUS_KOUNT_DECLINE;
        }

        // Set state & status on Magento order
        $oOrder->setState(
            Mage_Sales_Model_Order::STATE_HOLDED,
            $orderStatus,
            SFC_Kount_Helper_RisRequest::RIS_MESSAGE_ORDERDECLINE,
            false
        );
        $oOrder->save();
    }

    /**
     * Restore order status from before hold
     *
     * @param Mage_Sales_Model_Order $oOrder Order to operate on
     */
    public function restorePreHoldOrderStatus($oOrder)
    {
        // Move order from Hold to previous status
        $oOrder->setState($oOrder->getHoldBeforeState(), $oOrder->getHoldBeforeStatus());
        $oOrder->setHoldBeforeState(null);
        $oOrder->setHoldBeforeStatus(null);
        $oOrder->save();
    }

    /**
     * Return the version number of the installed extension
     */
    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->SFC_Kount->version;
    }

    /**
     * Check users Ip address against settings
     * @return boolean
     */
    public function checkIPAddress($ipAddress)
    {
        // Enabled?
        if (!Mage::getStoreConfig('kount/phonetoweb/enable')) {
            return false;
        }

        // Ips, what we got?
        $aIps = explode("\n", str_replace("\r", '', Mage::getStoreConfig('kount/phonetoweb/ipaddresses')));
        $sIp = $ipAddress;
        if (in_array($sIp, $aIps)) {
            Mage::log("IP Address {$sIp} in white-listed, bypassing Data Collector.", Zend_Log::ERR,
                SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            return true;
        }

        return false;
    }

}