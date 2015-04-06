<?php

class AW_Advancedreports_CountryController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/country');
    }

    public function indexAction()
    {
        Mage::helper('advancedreports')->updatePrototypeJS();
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/country')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales by Country'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales by Country'),
                Mage::helper('advancedreports')->__('Sales by Country')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_country'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'country.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_country_grid')
            ->setIsExport(true)
            ->getCsv()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'country.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_country_grid')
            ->setIsExport(true)
            ->getExcel($fileName)
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }
}