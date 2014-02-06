<?php

class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'quotedispatch';
        $this->_controller = 'adminhtml_quotedispatch';
        
        //$this->_updateButton('save', 'label', Mage::helper('quotedispatch')->__('Save Quote'));
        $this->_updateButton('delete', 'label', Mage::helper('quotedispatch')->__('Delete'));
        $this->removeButton('save');
        $this->removeButton('reset');
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_addButton('sendEmail', array(
            'label'     => Mage::helper('adminhtml')->__('Send Email'),
            'onclick'   => 'saveAndSendEmail()',
            //'class'     => 'save',
        ), -100);
        $location = $this->getUrl('*/*/new',array('id'=>$this->getRequest()->getParam('id'),'partial'=>true));
         $this->_addButton('addQuote', array(
            'label'     => Mage::helper('adminhtml')->__('Add Quote'),
            'onclick'   => "setLocation('$location');",
            //'class'     => 'save',
        ), -100);
        $this->_formScripts[] = "
            
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function saveAndSendEmail(){
                editForm.submit($('edit_form').action+'email/edit/');
            }
            document.observe('dom:loaded', function() {
            if($(created_by).getValue() != 0)
                $(created_by).disable();
            });
            
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('quotedispatch_data') && Mage::registry('quotedispatch_data')->getId() ) {
            return Mage::helper('quotedispatch')->__("Edit Quote '%s'", $this->htmlEscape(Mage::registry('quotedispatch_data')->getTitle()));
        } else {
            return Mage::helper('quotedispatch')->__('Add Quote');
        }
    }
}