<?php
/**
 * Substitution controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */


class SoftwareMedia_Substitution_Adminhtml_SubstitutionController extends Mage_Adminhtml_Controller_Action
{
	public function addAction() {
		$invoiceId = $this->getRequest()->getParam('invoiceId');
		$productId = $this->getRequest()->getParam('productId');
		
		$invoiceItem = Mage::getModel('sales/order_invoice_item')->load($invoiceId);
		$productItem = Mage::getModel('catalog/product')->load($productId);
		
		$output = array();
        $output['resp'] = $invoiceId;
        
		Mage::helper('substitution')->addSub($invoiceId,$productId);
		
		$invoice = Mage::getModel('sales/order_invoice')->load($invoiceItem->getParentId());
		$orderId = $invoice->getOrderId();
			
		$newUrl = $this->getUrl('adminhtml/sales_order/view/order_id/' . $orderId);
		$output['resp'] = $newUrl;
		
		$invoice = Mage::getModel('sales/order_invoice')->load($invoiceItem->getParentId());
		$orderId = $invoice->getOrderId();
		
		Mage::getSingleton('adminhtml/session')->addSuccess("Substitution Added Successfully"); 
			
        $json = json_encode($output);

         $this->getResponse()
         ->clearHeaders()
         ->setHeader('Content-Type', 'application/json')
         ->setBody($json);
	}
}