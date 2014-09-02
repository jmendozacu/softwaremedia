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

class SFC_Kount_Helper_EnsHandler extends Mage_Core_Helper_Abstract
{
    /**
     * Ip Addresses
     */
    const IPADDRESS_1 = '64.128.91.251';
    const IPADDRESS_2 = '209.81.12.251';

    /**
     * Process event
     * @param array Event
     * @return boolean
     * @throws Exception
     */
    public function handleEvent($aEvent)
    {
        // Log event details
        Mage::log('============================', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Kount extension version: ' . Mage::helper('kount')->getExtensionVersion(), Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('==== ENS Event Details =====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('Name: ' . $aEvent['name'], Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('order_number: ' . $aEvent['key']['_attribute']['order_number'], Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('transaction_id: ' . $aEvent['key']['_value'], Zend_Log::INFO,
            SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('old_value: ', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log($aEvent['old_value'], Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('new_value: ', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log($aEvent['new_value'], Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('agent: ' . $aEvent['agent'], Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('occurred: ' . $aEvent['occurred'], Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
        Mage::log('============================', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Validate event
        if (!isset($aEvent['name'])) {
            Mage::throwException('Invalid Event name.');
        }

        // What should we do
        switch ($aEvent['name']) {

            // -- DMC
            case 'DMC_EMAIL_ADD':
            case 'DMC_EMAIL_EDIT':
            case 'DMC_EMAIL_DELETE':
            case 'DMC_CARD_ADD':
            case 'DMC_CARD_EDIT':
            case 'DMC_CARD_DELETE':
            case 'DMC_ADDRESS_ADD':
            case 'DMC_ADDRESS_EDIT':
            case 'DMC_ADDRESS_DELETE':
                // -- -- Log
                Mage::log('DMC event received, but nothing to do.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                break;

            // -- Workflow
            case 'WORKFLOW_QUEUE_ASSIGN':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: Assign transactions to agents.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

            case 'WORKFLOW_NOTES_ADD':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $newValue = $aEvent['new_value'];
                $sComment = $newValue['_value'];
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

            case 'WORKFLOW_REEVALUATE':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: Press Reevaluate Risk button for transaction.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

            case 'WORKFLOW_STATUS_EDIT':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // -- -- Add comment to order
                $sComment = 'Kount ENS Notification: Modify status of an order by agent.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                // Set new RIS Response on order
                $oOrder->setData('kount_ris_response', $aEvent['new_value']);
                $oOrder->save();
                // Handle status change on Magento order
                $this->handleKountStatusChange($aEvent, $oOrder);
                break;

            // -- Risk
            case 'RISK_CHANGE_SCOR':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment and status
                $sNewValue = $aEvent['new_value'];
                $sComment = "Kount ENS Notification: RIS Score changed to {$sNewValue}.";
                $oOrder->addStatusHistoryComment($sComment);
                $oOrder->setData('kount_ris_score', $aEvent['new_value']);
                $oOrder->save();
                break;

            case 'RISK_CHANGE_REPLY':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment and status
                $sNewValue = $aEvent['new_value'];
                $sComment = "Kount ENS Notification: RIS Response changed to {$sNewValue}.";
                $oOrder->addStatusHistoryComment($sComment);
                $oOrder->setData('kount_ris_response', $aEvent['new_value']);
                $oOrder->save();
                // Handle status change on Magento order
                $this->handleKountStatusChange($aEvent, $oOrder);
                break;

            // -- Special
            case 'SPECIAL_ALERT_TRANSACTION':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // -- -- New value
                $sNewValue = 'D';
                $sComment = "Kount ENS Notification: RIS Response changed to {$sNewValue}.";
                $oOrder->addStatusHistoryComment($sComment);
                $oOrder->setData('kount_ris_response', $sNewValue);
                $oOrder->save();
                break;

            case 'RISK_CHANGE_VELO':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: 2 week velocity has changed.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

            case 'RISK_CHANGE_VMAX':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: 6 hour velocity has changed.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

            case 'RISK_CHANGE_GEOX':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: Risk connected to region has changed.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;
            case 'RISK_CHANGE_NETW':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: Network type has changed.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

            case 'RISK_CHANGE_REAS':
                // -- -- Get order
                $oOrder = $this->loadOrder($aEvent);
                // Set comment
                $sComment = 'Kount ENS Notification: One or more risk reasons have changed.';
                $oOrder->addStatusHistoryComment($sComment)->save();
                break;

        }

        return true;
    }

    /**
     * Load an order
     *
     * @param array Event
     * @return Mage_Sales_Model_Order Order with passed in increment Id
     * @throws Exception
     */
    protected function loadOrder($aEvent)
    {
        // -- -- Get order Id
        if (!isset($aEvent['key']['_attribute']['order_number'])) {
            Mage::throwException('Invalid Order number.');
        }
        $sOrderId = $aEvent['key']['_attribute']['order_number'];

        // -- -- Get order
        /** @var Mage_Sales_Model_Order $oOrder */
        $oOrder = Mage::getModel('sales/order')->loadByIncrementId($sOrderId);
        if (!strlen($oOrder->getEntityId())) {
            Mage::throwException("Unable to locate order for: {$sOrderId}");
        }

        // Ensure that the transaction id matches, if not we will ignore this ENS event
        if (!isset($aEvent['key']['_value'])) {
            Mage::throwException('Invalid Transaction ID.');
        }
        // Get ris info from order payment
        $risInfo = get_object_vars(json_decode($oOrder->getPayment()->getAdditionalInformation('ris_additional_info')));
        // Get trans id from order
        if (!isset($risInfo['TRAN'])) {
            Mage::throwException('Invalid Transaction ID.');
        }
        $orderTransactionId = $risInfo['TRAN'];
        if ($aEvent['key']['_value'] != $orderTransactionId) {
            Mage::throwException('Transaction ID does not match order, event must be for discarded version of order!');
        }

        return $oOrder;
    }

    /**
     * Process event 'WORKFLOW_STATUS_EDIT' and other events indicating Kount RIS status changes
     *
     * @param Mage_Sales_Model_Order $oOrder Magento order model on which to operate
     * @param array Event
     * @return boolean
     * @throws Exception
     */
    protected function handleKountStatusChange($aEvent, $oOrder)
    {
        Mage::log('Running handleStatusChange()...', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

        // Detect and handle situation where order is newly declined by Kount, previously at review status
        if ($aEvent['new_value'] == SFC_Kount_Helper_RisRequest::RIS_RESP_DECLINE &&
            $aEvent['old_value'] == SFC_Kount_Helper_RisRequest::RIS_RESP_REVIEW
        ) {
            // Log
            Mage::log('Kount status transitioned from review to decline.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Check if pre-hold status & state were saved
            // If not, we won't do anything here
            if ($oOrder->getHoldBeforeState() == null || $oOrder->getHoldBeforeState() == null) {
                Mage::log('Pre-hold order state / status not preserved.', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return;
            }

            // Move order from Hold to previous status
            Mage::helper('kount')->restorePreHoldOrderStatus($oOrder);

            // Now cancel order or issue refund or fall back on marking order as 'Kount Decline'
            // First, try to issue credit memo & refund
            Mage::log('Attempting to refund / credit memo Magento order.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            $bRefunded = $this->refundOrder($oOrder);

            if (!$bRefunded) {
                // If refund doesn't work, try to cancel order
                Mage::log('Unabled to refund Magento order.', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                Mage::log('Attempting to cancel Magento order.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                // Check canCancel
                if ($oOrder->canCancel()) {
                    // Cancel & save order
                    $oOrder->cancel()->save();;
                    
                    $sComment = "We’re sorry. Because we were unable to validate your payment information, our system detected your order as possible fraud.";

					
                $oOrder->setCustomerComment($sComment);
				$oOrder->setCustomerNoteNotify(true);
				$oOrder->setCustomerNote($sComment);
				$oOrder->sendOrderUpdateEmail(true,$sComment);  
                $oOrder->addStatusHistoryComment($sComment)->setIsCustomerNotified(true)->save();
                }
                else {
                    // Not able to cancel this order
                    Mage::log('Unabled to cancel Magento order.', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                    Mage::log('Setting status to Kount Decline', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                    // Set status to Kount Decline
                    Mage::helper('kount')->setOrderToKountDecline($oOrder);
                }
            }
        }

        // Detect and handle situation where order is newly approved, previously at review status
        if ($aEvent['new_value'] == SFC_Kount_Helper_RisRequest::RIS_RESP_APPRV &&
            $aEvent['old_value'] == SFC_Kount_Helper_RisRequest::RIS_RESP_REVIEW
        ) {
            // Log
            Mage::log('Kount status transitioned from review to allow.', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

			/*
			//Create invoice/capture			
			$qty = array();
			
			$invoice = Mage::getModel('sales/order_invoice_api');
			$invoiceId = $invoice->create($oOrder->getIncrementId(), $qty);
			
			$invoice->capture($invoiceId);

			*/
			
			
			
            // Check if pre-hold status & state were saved
            // If not, we won't do anything here
            /*
            if ($oOrder->getHoldBeforeState() == null || $oOrder->getHoldBeforeState() == null) {
                Mage::log('Pre-hold order state / status not preserved.', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return;
            }
            */

            // Move order from Hold to previous status
            Mage::helper('kount')->restorePreHoldOrderStatus($oOrder);
			Mage::helper('kount')->captureOrder($oOrder);
        }

    }

    /**
     * Attempt to issue credit memo and online refund for Magento order, where possible
     *
     * NOTE: This method is based on copying actions which occur when credit memo / online refund issued in Admin panel
     *
     * @param Mage_Sales_Model_Order $oOrder Magento order model on which to operate
     */
    protected function refundOrder($oOrder)
    {
        try {
            // Check if order will allow us to create 
            if (!$oOrder->canCreditmemo()) {
                // Error, can't create credit memo for htis order
                Mage::log('Cant create credit memo for order: ' . $oOrder->getIncrementId(), Zend_Log::ERR,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return false;
            }
            // Get invoices from order
            if (!$oOrder->hasInvoices()) {
                // Order has no invoice to credit memo
                Mage::log('No invoices found for order: ' . $oOrder->getIncrementId(), Zend_Log::ERR,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                return false;
            }

            // Iterate invoice & attempt to credit memo & refund each
            $invoiceCollection = $oOrder->getInvoiceCollection();
            foreach ($invoiceCollection as $curInvoice) {
                // Log
                Mage::log(
                    'Issueing refund / credit memo for invoice: ' . $curInvoice->getIncrementId(),
                    Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                // Use sales/service_order to prepare memo for all items on invoice
                $service = Mage::getModel('sales/service_order', $oOrder);
                $curCreditmemo = $service->prepareInvoiceCreditmemo($curInvoice);

                // Prepare credit memo comment text
                /*
                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }
                */

                // Set refund requested flag on credit memo
                $curCreditmemo->setRefundRequested(true);

                // Register credit memo
                $curCreditmemo->register();

                // Set email customer flag
                $curCreditmemo->setEmailSent(true);
                $curCreditmemo->getOrder()->setCustomerNoteNotify(true);

                // Save the credit memo
                // Save creditmemo and related order, invoice in one transaction
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($curCreditmemo)
                    ->addObject($curCreditmemo->getOrder())
                    ->addObject($curCreditmemo->getInvoice());
                $transactionSave->save();

                // Send customer email
                $comment = 'Kount Decline';
                $curCreditmemo->sendEmail(true, $comment);

            }

            // Return successfully
            return true;

        }
        catch (Exception $e) {
            // Log details of this exception
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // return false
            return false;
        }

    }

    /**
     * Validate store for merchant Id
     * @param string merchant Id
     * @return boolean
     */
    public function validateStoreForMerchantId($sMerchantId)
    {
        // Check admin first
        $sTest = Mage::getStoreConfig('kount/account/merchantnum', 0);
        if (intval($sTest) == intval($sMerchantId)) {
            return true;
        }

        // All stores
        foreach (Mage::app()->getStores() as $iStoreId => $sVal) {
            $sTest = Mage::getStoreConfig('kount/account/merchantnum', $iStoreId);
            if (intval($sTest) == intval($sMerchantId)) {
                return true;
            }
        }

        return false;
    }
}