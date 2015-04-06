<?php

class AW_Advancedreports_Block_Adminhtml_Setup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Retrieves setup
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return Mage::helper('advancedreports/setup');
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'report_id';
        $this->_blockGroup = 'advancedreports';
        $this->_mode = 'edit';
        $this->_controller = 'adminhtml_setup';

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', Mage::helper('advancedreports')->__('Save'));
    }

    public function getHeaderText()
    {
        return $this->getSetup()->getReportTitle()
            ? $this->getSetup()->getReportTitle()
            : Mage::helper('advancedreports')->__('')
        ;
    }

    public function getBackUrl()
    {
        return $this->getSetup()->getBackUrl();
    }
}