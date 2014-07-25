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

class SFC_Kount_PaypalstandardController extends Mage_Core_Controller_Front_Action
{

    /**
     * Index action
     */
    public function indexAction()
    {
        // Nothing to do here
        // Log
        Mage::log('==== PayPal Standard Controller indexAction ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
    }

    /**
     * When a customer chooses Paypal on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        try {
            // Log
            Mage::log('==== PayPal Standard Controller redirectAction ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Session
            $oSession = Mage::getSingleton('checkout/session');

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled and configured
            if (Mage::getStoreConfig('kount/account/enabled') && $oPathHelper->validateConfig()) {

                // -- Get quote
                $iQuoteId = $oSession->getQuoteId();
                $oQuote = (($iQuoteId) ? Mage::getModel('sales/quote')->load($iQuoteId) : null);
                if (empty($oQuote)) {
                    throw new Exception('Invalid quote passed to controller.');
                }

                // -- Get payment
                $oPayment = $oQuote->getPayment();
                if (empty($oPayment)) {
                    throw new Exception('Invalid payment passed to controller.');
                }

                // -- Get order
                $iOrderId = $oSession->getLastOrderId();
                $oOrder = (($iOrderId) ? Mage::getModel('sales/order')->load($iOrderId) : null);
                if (empty($oPayment)) {
                    throw new Exception('Invalid order passed to controller.');
                }

                // -- Ris request
                $oRisRequest = new SFC_Kount_Helper_RisRequest();

                // -- Ris request
                if ($oRisRequest->validatePaymentMethod($oPayment)) {

                    try {
                        // -- -- Request
                        $sRisRespCode = $oRisRequest->sendRisInquiry($oPayment, $oOrder);

                        // -- -- Response
                        if ($sRisRespCode === null || $sRisRespCode == SFC_Kount_Helper_RisRequest::RIS_RESP_DECLINE) {
                            Mage::throwException(SFC_Kount_Helper_RisRequest::RIS_MESSAGE_REJECTED);
                        }
                    }
                    catch (Kount_Ris_Exception $kre) {
                        // Handle Kount_Ris_Exception and let Magento transaction go through without any Kount intervention
                        Mage::log(
                            'Kount RIS request failed (possibly a network issue?), letting Magento transaction continue without Kount intervention.',
                            Zend_Log::ERR,
                            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                    }

                }

            }
            else {
                // -- Log
                Mage::log('Kount not enabled by system configuration, skipping RIS check.', Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            }

            // Set quote Id
            $oSession->setPaypalStandardQuoteId($oSession->getQuoteId());

            // Set body
            $this->getResponse()->setBody($this->getLayout()->createBlock('paypal/standard_redirect')->toHtml());

            // Cleanup
            $oSession->unsQuoteId();
            $oSession->unsRedirectUrl();

            return;
        }
        catch (Mage_Core_Exception $e) {

            // -- Log
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }
        catch (Exception $e) {

            // -- Log
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }

        // Redirect to cart
        $this->_redirect('checkout/cart');
    }

    /**
     * When a customer cancel payment from paypal.
     */
    public function cancelAction()
    {
        try {
            // Log
            Mage::log('==== PayPal Standard Controller cancelAction ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Session
            $oSession = Mage::getSingleton('checkout/session');
            $oSession->setQuoteId($oSession->getPaypalStandardQuoteId(true));

            // Cancel order
            if ($oSession->getLastRealOrderId()) {
                $oOrder = Mage::getModel('sales/order')->loadByIncrementId($oSession->getLastRealOrderId());
                if ($oOrder->getId()) {
                    $oOrder->cancel()->save();
                }
            }

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled and configured
            if (Mage::getStoreConfig('kount/account/enabled') && $oPathHelper->validateConfig()) {

                // -- Ris inquiry
                $oRisRequest = new SFC_Kount_Helper_RisRequest();

                // -- Update
                $oRisRequest->sendRisUpdate(false);
            }
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to cancel Express Checkout.'));
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        }

        // Redirect
        $this->_redirect('checkout/cart');
    }

    /**
     * when paypal returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function successAction()
    {
        // Log
        Mage::log('==== PayPal Standard Controller successAction ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Helper
        $oPathHelper = new SFC_Kount_Helper_Paths();

        // Is Kount enabled and configured
        if (Mage::getStoreConfig('kount/account/enabled') && $oPathHelper->validateConfig()) {

            // -- Ris inquiry
            $oRisRequest = new SFC_Kount_Helper_RisRequest();

            // -- Send update to Ris
            $oRisRequest->sendRisUpdate(false);
        }

        // Session
        $oSession = Mage::getSingleton('checkout/session');
        $oSession->setQuoteId($oSession->getPaypalStandardQuoteId(true));

        // Save
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

}

