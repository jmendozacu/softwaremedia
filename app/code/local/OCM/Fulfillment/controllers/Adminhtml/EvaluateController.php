<?php

class OCM_Fulfillment_Adminhtml_EvaluateController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $observer = Mage::getModel('ocm_fulfillment/observer');
        $observer->evaluateOrdersDaily();

        $this->_getSession()->addSuccess($this->__('Your order(s) have been updated.'));
        $this->_redirect('adminhtml/sales_order/index');

    }

}