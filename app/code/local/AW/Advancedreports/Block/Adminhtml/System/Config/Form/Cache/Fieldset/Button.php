<?php
/**
 * Button block
 */
class AW_Advancedreports_Block_Adminhtml_System_Config_Form_Cache_Fieldset_Button extends Mage_Core_Block_Template
{
    /**
     * Default button template
     */
    const DEFAULT_BUTTON_TEMPLATE = "advancedreports/fieldset/button.phtml";

    /**
     * This is constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::DEFAULT_BUTTON_TEMPLATE);
    }

    /**
     * Retrves ajax url for reset all Extra Downloads Statistics
     *
     * @return string
     */
    public function getResetAllUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl(
            'advancedreports_admin/aggregation/clean', array('_secure' => true)
        );
    }
}