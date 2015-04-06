<?php

class AW_Advancedreports_Model_Form_Element_Text extends Varien_Data_Form_Element_Text
{
    public function getElementHtml()
    {
        $this->_data['disabled'] = Mage::helper('advancedreports/setup')->isDefault($this->getId());
        return parent::getElementHtml() . $this->_getDefaultCheckbox();
    }

    protected function _getDefaultCheckbox()
    {
        $html = '</td><td class="value use-default">';
        $html .= Mage::helper('advancedreports/setup')->getCheckboxScopeHtml(
            $this, $this->getFieldName(), $this->getDisabled()
        );
        return $html;
    }
}