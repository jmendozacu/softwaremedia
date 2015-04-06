<?php

class AW_Advancedreports_Block_Adminhtml_Setup_Edit_Tabs_Columns extends Mage_Adminhtml_Block_Widget_Form
{
    const TEMPLATE_PATH = 'advancedreports/setup/tabs/columns.phtml';

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::TEMPLATE_PATH);
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

    public function getColumns()
    {
        if ($grid = $this->getSetup()->getGrid()) {
            return $grid->getSetupColumns();
        }
        return null;
    }

    /**
     * Retribes serialized custom columns
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getSetup()->getGrid()->getCustomOption('custom_columns');
    }
}