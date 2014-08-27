<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');
//OCM_Fulfillment_Model_Warehouse_Ingram
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();

$oOrder = Mage::getModel('sales/order')->load(8352);


try {
					if($oOrder->canInvoice()) {
					
						$invoice = Mage::getModel('sales/service_order', $oOrder)->prepareInvoice();
						if (!$invoice->getTotalQty()) {
							Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
						}
						$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
						$invoice->register();
						$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($invoice)
							->addObject($invoice->getOrder());
						$transactionSave->save();
					} else {
						Mage::log('Order does not allow invoicing ' . $oOrder->getId(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
					}
				}
				catch (Mage_Core_Exception $e) {
					Mage::log($e->getMessage(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
				}
				

//Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFrom();
