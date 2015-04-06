<?php

class AW_Advancedreports_SalesoverviewController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/salesoverview');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/salesoverview')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales Overview'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'),
                Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales'),
                Mage::helper('advancedreports')->__('Sales Overview')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/report_sales_salesoverview'))
            ->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'salesoverview.csv';
        $content = $this->getLayout()->createBlock('advancedreports/report_sales_salesoverview_grid')->setIsExport(true)
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportExcelAction()
    {
        $fileName = 'salesoverview.xml';
        $content = $this->getLayout()->createBlock('advancedreports/report_sales_salesoverview_grid')->setIsExport(true)
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}
