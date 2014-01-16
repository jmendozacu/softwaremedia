<?php
class OCM_Testimonial_Block_Adminhtml_Testimonial_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
    public function __construct(){
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = "ocm_testimonial";
        $this->_controller = "adminhtml_testimonial";
        $this->_mode = 'edit';
        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('ocm_testimonial')->__('Save Message'));
        $this->_updateButton('delete', 'label', Mage::helper('ocm_testimonial')->__('Delete'));
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }
 
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    public function getHeaderText()
    {
        if( Mage::registry('message_data')&&Mage::registry('message_data')->getId())
        {
            return 'Edit Message'.$this->htmlEscape(
                Mage::registry('message_data')->getTitle()).'<br />';
        }
        else
        {
            return 'Add a Message';
        }
    }
}