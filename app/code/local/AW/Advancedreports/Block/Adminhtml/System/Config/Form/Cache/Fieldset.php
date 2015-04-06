<?php
/**
 * Special block of Admin Config Fieldset
 */
class AW_Advancedreports_Block_Adminhtml_System_Config_Form_Cache_Fieldset
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        foreach ($element->getElements() as $field) {
            $html .= $field->toHtml();
        }
        $html .= $this->getLayout()
            ->createBlock('advancedreports/adminhtml_system_config_form_cache_fieldset_button')
            ->setTitle($this->__('Clean Cache'))
            ->toHtml();
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}