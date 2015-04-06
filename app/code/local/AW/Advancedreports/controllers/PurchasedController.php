<?php

class AW_Advancedreports_PurchasedController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/purchased');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/purchased')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Products by Customer'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Products by Customer'),
                Mage::helper('advancedreports')->__('Products by Customer')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_purchased'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'product.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_purchased_grid')
            ->setIsExport(true)
            ->getCsv()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'product.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_purchased_grid')
            ->setIsExport(true)
            ->getExcel($fileName)
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }
}