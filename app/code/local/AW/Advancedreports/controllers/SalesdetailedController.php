<?php

class AW_Advancedreports_SalesdetailedController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/salesdetailed');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/salesdetailed')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales Detailed'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'),
                Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales Report'),
                Mage::helper('advancedreports')->__('Sales Detailed')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_salesdetailed'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'product.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_salesdetailed_grid')
            ->setIsExport(true)
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'product.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_salesdetailed_grid')
            ->setIsExport(true)
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('advancedreports/advanced_salesdetailed_grid')->toHtml()
        );
    }
}
