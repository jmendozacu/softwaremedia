<?php
/**
 * Substitution controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */


class OCM_ChasePaymentTech_Adminhtml_ChaseController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
    {
        return true;
    }
	public function deleteAction() {
		$customerId = $this->getRequest()->getParam('customer');
		$paymentId = $this->getRequest()->getParam('pid');
		$profile = Mage::getModel('chasePaymentTech/profiles')->load($paymentId);
		$profile->delete();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('peachtree')->__('Removed Card Information'));
		
		$this->_redirect('*/customer/edit',array('id' => $customerId, 'tab' => 'chase'));
	}
	
	public function addAction() {
		$customerId = $this->getRequest()->getParam('customer');
		
		$payment = $this->getRequest()->getParam('payment');

		$cardNum = $payment[0]['cc_number'];
		$cardType = $payment[1]['card_type'];
		$cardExpMonth = $payment[2]['cc_exp_month'];
		$cardExpYear = $payment[3]['cc_exp_year'];
		$cardCVN = $payment[4]['cc_cid'];
		
		$helper = Mage::helper('chasePaymentTech');
		$hasProfile = $helper->hasProfile($customerId,substr($cardNum,-4));

		if ($hasProfile) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Duplicate Card'));
			$this->_redirect('*/customer/edit',array('id' => $customerId,'tab' => 'chase'));
			return;
		}

	    if(!$cardNum || !$cardExpMonth || !$cardExpYear) {
	        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Please Enter All Required Information'));
        } else {
	        $profile = Mage::getModel('chasePaymentTech/profiles');
	        $profile->setData('card_num',$cardNum);
	        $profile->setData('card_type',$cardType);
	        $profile->setData('exp_month',$cardExpMonth);
	        $profile->setData('exp_year',$cardExpYear);
	        $profile->setData('cc_cid',$cardCVN);
	        if ($profile->addProfile()) {
	        	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('peachtree')->__('Saved Card Information'));
	        	$profile->setCustomerId($customerId);
	        	$profile->save();
	        } else {
	        	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Error Saving Card Information'));
	        }
        }
		$this->_redirect('*/customer/edit',array('id' => $customerId,'tab' => 'chase'));
				
		
	}

}