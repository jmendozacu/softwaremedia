<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once('Mage/Adminhtml/controllers/Sales/OrderController.php');

class SoftwareMedia_Sales_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController {


    public function exportLicensingAction()
    {
        $fileName   = 'licensing.csv';
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $grid->getCsvFile();
        $grid->getCollection()->setPageSize(200);
        
        $csv = '';
        
        $headers = array(
        	'company_name',
        	'contact_name',
        	'company_email_address',
        	'phone_number',
        	'address', 
        	'city',
        	'state',
        	'zip',
        	'country'
        );
        
       
        foreach($grid->getCollection() as $order) {
        	$item = array();
        	$custom = $this->getOrderData($order);
        	//var_dump($custom);
        	$item['order_id'] = $order->getIncrementId();
        	foreach($headers as $header) {
        		$item[$header] = '';
	        	foreach($custom as $cData) {
		        	if ($cData['code'] != $header)
		        		continue;
		        		
		        	$item[$header] = $cData['value'];
	        	}
        	}
        	
        	foreach($order->getAllVisibleItems() as $orderItem) {
	        	$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
	        	if ($product->getLicenseNonlicenseDropdown() != 1210)
	        		continue;
	        		
	        	$item['sku'] = $orderItem->getSku();
	        	$item['qty'] = $orderItem->getQtyOrdered();
	        	
	        	$csv .= '"' . implode('","', $item) . '"' . "\r\n";
        	}
			
        }
        
        $this->_sendUploadResponse($fileName, $csv);
    }
    
    public function getOrderData($order) {
        $iStoreId = $order->getStoreId();

        $oFront = Mage::app()->getFrontController();
        $params = $oFront->getRequest()->getParams();
       
        $iOrderId = $order->getId();  
        $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

        $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($iOrderId, $iStoreId, true, true);   
                
        !$aCustomAtrrList ? $aCustomAtrrList = array() : false;
        
        return $aCustomAtrrList;
    }
    
	protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK', '');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}
	
	/**
	 * Cancel order
	 */
	public function cancelAction() {
		if ($order = $this->_initOrder()) {
			try {
				$can_void = $order->canVoidPayment();
				$has_invoices = $order->hasInvoices();
				if ($can_void && $has_invoices) {
					// Void the order when canceled
					$order->getPayment()->void(
						new Varien_Object() // workaround for backwards compatibility
					);
					$order->save();
					$this->_getSession()->addSuccess($this->__('The payment has been voided.'));
				}

				$order->cancel()
					->save();
				$order->sendOrderUpdateEmail();
				$this->_getSession()->addSuccess(
					$this->__('The order has been cancelled.')
				);
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			} catch (Exception $e) {
				$this->_getSession()->addError($this->__('The order has not been cancelled.'));
				Mage::logException($e);
			}
			$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		}
	}

}
