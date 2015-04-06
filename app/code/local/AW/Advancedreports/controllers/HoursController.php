<?php

class AW_Advancedreports_HoursController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/hours');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/hours')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales by Hour'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales by Hour'),
                Mage::helper('advancedreports')->__('Sales by Hour')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_hours'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'hours.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_hours_grid')
            ->setIsExport(true)
            ->getCsv()
        ;

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'hours.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_hours_grid')
            ->setIsExport(true)
            ->getExcel($fileName)
        ;

        $this->_prepareDownloadResponse($fileName, $content);
    }
}