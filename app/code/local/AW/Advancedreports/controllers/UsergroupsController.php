<?php

class AW_Advancedreports_UsergroupsController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/usergroups');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/usergroups')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales by Customer Group'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales by Customer Group'),
                Mage::helper('advancedreports')->__('Sales by Customer Group')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_usergroups'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'country.csv';
        $content = $this->getLayout()->createBlock('advancedreports/advanced_usergroups_grid')->setIsExport(true)
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'country.xml';
        $content = $this->getLayout()->createBlock('advancedreports/advanced_usergroups_grid')->setIsExport(true)
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
}