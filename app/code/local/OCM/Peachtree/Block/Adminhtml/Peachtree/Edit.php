<?php

class OCM_Peachtree_Block_Adminhtml_Peachtree_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'peachtree';
        $this->_controller = 'adminhtml_peachtree';
        
        $this->_updateButton('save', 'label', Mage::helper('peachtree')->__('Export CSV'));
        
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('reset');
		
    }

    public function getHeaderText()
    {
            return Mage::helper('peachtree')->__('Peachtree Export');
    }
}