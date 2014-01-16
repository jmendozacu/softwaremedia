<?php

class OCM_Fulfillment_Adminhtml_Sales_LicenseorderController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function assignedAction()
    {
        $ids = $this->getRequest()->getPost('id', array());
        $count = 0;
        foreach ($ids as $id) {
            $order = Mage::getModel('ocm_fulfillment/license')->load($id);
            $order->setStatus('License assigned')->save();
            $count++;
        }

        if ($count) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been updated.', $count));
        }

        $this->_redirect('*/*/');
    }

    public function notassignedAction()
    {
        $ids = $this->getRequest()->getPost('id', array());
        $count = 0;
        foreach ($ids as $id) {
            $order = Mage::getModel('ocm_fulfillment/license')->load($id);
            $order->setStatus('Not assigned')->save();
            $count++;
        }

        if ($count) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been updated.', $count));
        }

        $this->_redirect('*/*/');
    }
}