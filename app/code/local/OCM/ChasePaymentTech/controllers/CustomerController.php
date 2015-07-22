<?php
class OCM_ChasePaymentTech_CustomerController extends Mage_Core_Controller_Front_Action
{
		protected function _isAllowed()
    {
        return true;
    }
	public function indexAction()
	{
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
		$collection = Mage::getModel('chasePaymentTech/profiles')->getCollection()
                ->addFieldToFilter('customer_id',Mage::getSingleton('customer/session')->getCustomer()->getId());

        Mage::register('profiles',$collection);
        $this->getLayout()->getBlock('head')->setTitle($this->__('Payment Methods'));
        $this->renderLayout();
	}
	public function deleteAction()
	{
        $paymentId = $this->getRequest()->getParam('pid');
		$profile = Mage::getModel('chasePaymentTech/profiles')->load($paymentId);
		if ($profile->getCustomerId() == Mage::getSingleton('customer/session')->getCustomer()->getId())
			$profile->delete();
		else
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Not Authorized To Remove Card Information'));
			
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('peachtree')->__('Removed Card Information'));
		
		$this->_redirect('chase/customer/index');
	}
}