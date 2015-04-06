<?php

class AW_Advancedreports_UsersController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/users');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/users')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Users Activity Report'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Users Activity Report'),
                Mage::helper('advancedreports')->__('Users Activity Report')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_users'))
            ->renderLayout()
        ;
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'product.csv';
        $content = $this->getLayout()->createBlock('advancedreports/advanced_users_grid')->setIsExport(true)
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'product.xml';
        $content = $this->getLayout()->createBlock('advancedreports/advanced_users_grid')->setIsExport(true)
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}