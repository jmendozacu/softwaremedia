<?php
class SoftwareMedia_EmailHistory_Adminhtml_EmailhistoryController extends Mage_Adminhtml_Controller_Action
{
    public function viewAction()
    {
    	$id = $this->getRequest()->getParam('id');
            $current_email = Mage::getModel('emailhistory/email');
            $current_email->load($id);

            Mage::register('current_email',$current_email);
            
            $this->loadLayout();     
            $this->renderLayout();    
    }
    
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }
    
    /**
     * Generate order history for ajax request
     */
    public function listAction()
    {
        $this->_initOrder();
        $html = $this->getLayout()->createBlock('emailhistory/adminhtml_sales_order_view_tab_email')->toHtml();

        $this->getResponse()->setBody($html);
    }
}