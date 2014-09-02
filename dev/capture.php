<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
 ini_set('display_errors', '1');
 
echo "Trying to capture <br />";
$oOrder = Mage::getModel('sales/order')->load(8347);
        if ($oOrder->canUnhold() || $oOrder->isPaymentReview()) {
            echo "PAYMENT REVIEW";
        }
        $state = $oOrder->getState();
        if ($oOrder->isCanceled() || $state === 'complete' || $state === 'closed') {
            echo "IS CANCELED STATUS";
        }

        if ($oOrder->getActionFlag('invoice') === false) {
            echo "ACTION FLAG INVOICE";
        }
        
        foreach ($oOrder->getAllItems() as $item) {
        	$item->setQtyInvoiced(0);
        	$item->setRowInvoiced(NULL);
        	$item->setBaseRowInvoiced(0);
        	$item->save();
        }
        
	try {
		if($oOrder->canInvoice()) {
			echo "yes";
			//Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
		
			$invoice = Mage::getModel('sales/service_order', $oOrder)->prepareInvoice();
			if (!$invoice->getTotalQty()) {
				echo "no Products";
				
			}
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
			$invoice->register();
			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder());
			$transactionSave->save();
			
			$oOrder->setStatus('processing');
		} else {
			echo "Order Doesn't Allow Invoicing";
		}
	}
	catch (Mage_Core_Exception $e) {
		echo "ERROR: " . $e->getMessage();
	}
	
	echo "done";