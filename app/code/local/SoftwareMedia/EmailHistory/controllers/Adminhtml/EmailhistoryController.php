<?php
class SoftwareMedia_EmailHistory_Adminhtml_EmailhistoryController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
    	$id = $this->getRequest()->getParam('id');
            $current_email = Mage::getModel('emailhistory/email');
            //$current_email->load($id);

            Mage::register('current_email',$current_email);
            
            $this->loadLayout();     
            $this->renderLayout();    
    }
}