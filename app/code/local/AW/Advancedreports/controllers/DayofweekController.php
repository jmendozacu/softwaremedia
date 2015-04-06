<?php

class AW_Advancedreports_DayofweekController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/dayofweek');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/dayofweek')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales by Day of Week'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales by Day of Week'),
                Mage::helper('advancedreports')->__('Sales by Day of Week')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_dayofweek'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'dayofweek.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_dayofweek_grid')
            ->setIsExport(true)
            ->getCsv()
        ;

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'dayofweek.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_dayofweek_grid')
            ->setIsExport(true)
            ->getExcel($fileName)
        ;

        $this->_prepareDownloadResponse($fileName, $content);
    }
}