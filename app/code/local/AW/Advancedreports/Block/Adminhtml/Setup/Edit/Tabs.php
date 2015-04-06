<?php

class AW_Advancedreports_Block_Adminhtml_Setup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('advancedreports_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('advancedreports')->__('Report Customization'));
    }

    /**
     * Retrieves setup
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return Mage::helper('advancedreports/setup');
    }

    protected function  _beforeToHtml()
    {

        $tabTitle = Mage::helper('advancedreports')->__('General');
        $tabBlock = $this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs_general');
        $this->addTab(
            'general',
            array(
                'label'   => $tabTitle,
                'title'   => $tabTitle,
                'content' => $tabBlock->toHtml(),
            )
        );

        if ($this->getSetup()->getGrid()->getCustomColumnConfigEnabled()) {
            $tabTitle = Mage::helper('advancedreports')->__('Columns');
            $tabBlock = $this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs_columns');
            $this->addTab(
                'columns',
                array(
                    'label'   => $tabTitle,
                    'title'   => $tabTitle,
                    'content' => $tabBlock->toHtml()
                )
            );
        }
        parent::_beforeToHtml();
    }
}