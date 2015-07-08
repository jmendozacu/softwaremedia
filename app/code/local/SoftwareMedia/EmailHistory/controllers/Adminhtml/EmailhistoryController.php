<?php
class SoftwareMedia_EmailHistory_Adminhtml_EmailhistoryController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }
    public function viewAction()
    {
    	$id = $this->getRequest()->getParam('id');
            $current_email = Mage::getModel('emailhistory/email');
            $current_email->load($id);

            Mage::register('current_email',$current_email);
            $this->loadLayout();
            
            $this->renderLayout();    
    }
    
    public function resendAction()
    {
    		$id = $this->getRequest()->getParam('id');
    		$newemail = $this->getRequest()->getParam('newemail');
    		$newname = $this->getRequest()->getParam('newname');
            $current_email = Mage::getModel('emailhistory/email');
            $current_email->load($id);
            $current_email->setId(null);
            $current_email->setCreatedAt(now());
            if ($newemail)
            	$current_email->setEmail($newemail);
            	
            if ($newname)
            	$current_email->setEmailName($newname);
            	
			

			$template = Mage::getModel('core/email_template');
			$template->loadDefault('blank_email');
			
			$vars = array();
			$vars['content'] = $current_email->getText();
			
			$template->setSenderName('Software Media');
	        $template->setSenderEmail('customerservice@softwaremedia.com');
	        $template->setTemplateSubject($current_email->getSubject());
	        $res = $template->send($current_email->getEmail(), $current_email->getEmailName(), $vars);

            if (!$res) {
	            if ($template->getData('error') == 'cs') {
	            	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Email was sent to client'));
	            	$current_email->save();
				} else
	            	Mage::getSingleton('adminhtml/session')->addError($this->__('Message could not be sent'));
            } else {
	            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Email was sent to client'));
	            $current_email->save();
            }
 
            $this->_redirect('adminhtml/sales_order/view/order_id/' . $current_email->getOrderId());
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