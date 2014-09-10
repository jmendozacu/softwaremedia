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

class SFC_Kount_Model_Observer extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Constructor
     */
    public function _construct()
    {
    }

    /**
     * Admin predispatch
     * @param $observerSD
     */
    public function adminPreDispatch($oObserver)
    {
        // Log
        Mage::log('==== Admin Pre Dispatch Callback ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        try {
            Mage::getSingleton('core/session')->setIsKountAdmin('1');
            // Check config setting
            if (Mage::getStoreConfig('kount/admin/enable') != '1') {
                // Set admin session variables
                Mage::getSingleton('core/session')->setSkipKountAdmin('1');
            }
            else {
                // Set admin session variables
                Mage::getSingleton('core/session')->setSkipKountAdmin('0');
            }
        }
        catch (Exception $e) {

            // Log details of this exception
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);


            // Throw generic payment rejected message
            Mage::throwException(SFC_Kount_Helper_RisRequest::RIS_MESSAGE_REJECTED);
        }

        return $this;
    }

    /**
     * Order payment placed start
     * @param Varien_Event_Observer $oObserver
     */
    public function orderPaymentPlaceStart($oObserver)
    {
        // Log
        Mage::log('==== Payment Place Start Callback ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        try {

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled?
            if (!Mage::getStoreConfig('kount/account/enabled')) {
                Mage::log('Kount not enabled by system configuration, skipping RIS check.', Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Validate Kount settings
            if (!$oPathHelper->validateConfig()) {
                Mage::throwException('Kount settings not configured, skipping Ris inquiry.');
            }

            // Get payment from observer
            $oPayment = $oObserver->getEvent()->getPayment();
            if (empty($oPayment)) {
                throw new Exception('Invalid payment passed to callback.');
            }

            // Get Order
            $oOrder = $oPayment->getOrder();
            if (empty($oOrder)) {
                throw new Exception('Invalid order passed to callback.');
            }

            // Log
            Mage::log("Order Id: {$oOrder->getIncrementId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            Mage::log("Order Store Id: {$oOrder->getStoreId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Admin store is ignored
            $sIsAdmin = Mage::getSingleton('core/session')->getSkipKountAdmin();
            if (!empty($sIsAdmin)) {
                Mage::log('Skipped for admin store.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Create Ris request object
            $oRisRequest = new SFC_Kount_Helper_RisRequest();

            // Validate payment method
            if ($oRisRequest->validatePaymentMethod($oPayment)) {

                // Don't send this inquiry at this piont for paypal standard
                if ($oPayment->getMethodInstance()->getCode() != 'paypal_standard') {

                    // -- Request
                    $sRisRespCode = $oRisRequest->sendRisInquiry($oPayment, $oOrder);

					if ($sRisRespCode == SFC_Kount_Helper_RisRequest::RIS_RESP_APPRV) {
						
						
					}
                    // -- Response
                    if (empty($sRisRespCode) || $sRisRespCode == SFC_Kount_Helper_RisRequest::RIS_RESP_DECLINE) {
                        Mage::throwException(SFC_Kount_Helper_RisRequest::RIS_MESSAGE_REJECTED);
                    }

                }
            }
            else {
                Mage::log(
                    'Not a supported payment method, or disabled in system config. Skipping RIS check.',
                    Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            }

        }
        catch (Kount_Ris_Exception $kre) {
            // Handle Kount_Ris_Exception and let Magento transaction go through without any Kount intervention
            Mage::log(
                'Kount RIS request failed (possibly a network issue?), letting Magento transaction continue without Kount intervention.',
                Zend_Log::ERR,
                SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }
        catch (Exception $e) {

            // Log details of this exception
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);


            // Throw generic payment rejected message
            Mage::throwException(SFC_Kount_Helper_RisRequest::RIS_MESSAGE_REJECTED);
        }

        return $this;
    }

    /**
     * Order payment capture
     * @param Varien_Event_Observer $oObserver
     */
    public function orderServiceQuoteSubmitSuccess($oObserver)
    {
        // Log
        Mage::log('==== Service Quote Submit Success Callback ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        try {

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled?
            if (!Mage::getStoreConfig('kount/account/enabled')) {
                Mage::log('Kount not enabled by system configuration, skipping RIS update.', Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Validate Kount settings
            if (!$oPathHelper->validateConfig()) {
                Mage::throwException('Kount settings not configured, skipping Ris inquiry.');
            }

            // Get order from observer
            $oOrder = $oObserver->getEvent()->getOrder();
            if (empty($oOrder)) {
                throw new Exception('Invalid order passed to callback.');
            }

            // Log
            Mage::log("Order Id: {$oOrder->getIncrementId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            Mage::log("Order Store Id: {$oOrder->getStoreId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Admin store is ignored
            $sIsAdmin = Mage::getSingleton('core/session')->getSkipKountAdmin();
            if (!empty($sIsAdmin)) {
                Mage::log('Skipped for admin store.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Create Ris request object
            $oRisRequest = new SFC_Kount_Helper_RisRequest();

            // Update
            $oRisRequest->sendRisUpdate(true);

            // Increment Kount session id
            /** @var SFC_Kount_Model_Session $session */
            $session = Mage::getSingleton('kount/session');
            $session->incrementKountSessionId();

        }
        catch (Exception $e) {

            // Log details of this exception
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }

        return $this;
    }

    /**
     * Order payment cancel
     * @param Varien_Event_Observer $oObserver
     */
    public function orderServiceQuoteSubmitFailure($oObserver)
    {
        // Log
        Mage::log('==== Service Quote Submit Failure Callback ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        try {

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled?
            if (!Mage::getStoreConfig('kount/account/enabled')) {
                Mage::log('Kount not enabled by system configuration, skipping RIS update.', Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Validate Kount settings
            if (!$oPathHelper->validateConfig()) {
                Mage::throwException('Kount settings not configured, skipping Ris inquiry.');
            }

            // Get order from observer
            $oOrder = $oObserver->getEvent()->getOrder();
            if (empty($oOrder)) {
                throw new Exception('Invalid order passed to callback.');
            }

            // Log
            Mage::log("Order Id: {$oOrder->getIncrementId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            Mage::log("Order Store Id: {$oOrder->getStoreId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Admin store is ignored
            $sIsAdmin = Mage::getSingleton('core/session')->getSkipKountAdmin();
            if (!empty($sIsAdmin)) {
                Mage::log('Skipped for admin store.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Create Ris request object
            $oRisRequest = new SFC_Kount_Helper_RisRequest();

            // Update
            $oRisRequest->sendRisUpdate(false);

            // Increment Kount session id
            /** @var SFC_Kount_Model_Session $session */
            $session = Mage::getSingleton('kount/session');
            $session->incrementKountSessionId();

        }
        catch (Exception $e) {

            // Log details of this exception
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }

        return $this;
    }

    /**
     * Order placed after
     * @param Varien_Event_Observer $oObserver
     */
    public function orderPlacedAfter($oObserver)
    {
        // Log
        Mage::log('==== Order Placed After Callback ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        try {

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled?
            if (!Mage::getStoreConfig('kount/account/enabled')) {
                Mage::log('Kount not enabled by system configuration, skipping RIS update.', Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return $this;
            }

            // Validate Kount settings
            if (!$oPathHelper->validateConfig()) {
                Mage::throwException('Kount settings not configured, skipping Ris inquiry.');
            }

            // Get order from observer
            /** @var Mage_Sales_Model_Order $oOrder */
            $oOrder = $oObserver->getEvent()->getOrder();
            if (empty($oOrder)) {
                throw new Exception('Invalid order passed to callback.');
            }
            // Get payment
            /** @var Mage_Sales_Model_Order_Payment $oPayment */
            $oPayment = $oOrder->getPayment();

            // Log
            Mage::log("Order Id: {$oOrder->getIncrementId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            Mage::log("Order Store Id: {$oOrder->getStoreId()}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Admin store is ignored
            $sIsAdmin = Mage::getSingleton('core/session')->getSkipKountAdmin();
            if (!empty($sIsAdmin)) {
                Mage::log('Skipped for admin store.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
				
				//Mage::helper('kount')->captureOrder($oOrder);
		        $oOrder->setData('state', "pending");
		        $oOrder->setStatus("pending");       
		        //$history = $order->addStatusHistoryComment('Order was set to Complete by our automation tool.', false);
		        //$history->setIsCustomerNotified(false);
		        $oOrder->save();
        
                return $this;
            }

            // Get session variables
            $sRisResponse = Mage::getSingleton('core/session')->getRisResponse();
            $sRisScore = Mage::getSingleton('core/session')->getRisScore();
            $sRisRules = Mage::getSingleton('core/session')->getRisRules();
            $sRisDescription = Mage::getSingleton('core/session')->getRisDescription();
            $aRisAdditionalInfo = Mage::getSingleton('core/session')->getRisAdditionalInfo();
            Mage::getSingleton('core/session')->unsRisResponse();
            Mage::getSingleton('core/session')->unsRisScore();
            Mage::getSingleton('core/session')->unsRisRules();
            Mage::getSingleton('core/session')->unsRisDescription();
            Mage::getSingleton('core/session')->unsRisAdditionalInfo();
            if (empty($sRisResponse) || empty($sRisScore)) {
                throw new Exception('No RIS information for this order. Might not have payment method supported or enabled for Kount extension.');
            }

            // Save to order and payment record
            // Don't call save() on entities, they haven't been saved in OPC or Multi-Addy checkout yet
            $oPayment->setAdditionalInformation('ris_additional_info', json_encode($aRisAdditionalInfo));
            $oOrder->setData('kount_ris_score', $sRisScore);
            $oOrder->setData('kount_ris_response', $sRisResponse);
            $oOrder->setData('kount_ris_rule', $sRisRules);
            $oOrder->setData('kount_ris_description', $sRisDescription);

			if ($sRisResponse == SFC_Kount_Helper_RisRequest::RIS_RESP_APPRV) {	
				Mage::log('KOUNT APPROVED ORDER ' . $oOrder->getId(),NULL,'kount-capture.log');
				Mage::helper('kount')->captureOrder($oOrder);
			}
					
            // Review Status Returned from Kount RIS
            if ($sRisResponse == SFC_Kount_Helper_RisRequest::RIS_RESP_REVIEW ||
                $sRisResponse == SFC_Kount_Helper_RisRequest::RIS_RESP_MANGREV
            ) {
                // Setting order to Kount Review / Hold not supported for Authorize.Net Direct Post
                $oOrder = $oObserver->getEvent()->getOrder();
                $oPayment = $oOrder->getPayment();
                if ($oPayment->getMethod() != 'authorizenet_directpost') {
                    // Set status to Kount Review
                    Mage::helper('kount')->setOrderToKountReview($oOrder);
                }
            }

        }
        catch (Exception $e) {

            // Log details of this exception
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }

        return $this;
    }
}
