<?php

class AW_Advancedreports_BestsellersController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/bestsellers');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/bestsellers')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Bestsellers'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'),
                Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Bestsellers'),
                Mage::helper('advancedreports')->__('Bestsellers')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_bestsellers'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'bestsellers.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_bestsellers_grid')
            ->setIsExport(true)
            ->getCsv()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'bestsellers.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_bestsellers_grid')
            ->setIsExport(true)
            ->getExcel($fileName)
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }
}