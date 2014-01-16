<?php

class OCM_Frauddetection_Block_Adminhtml_Sales_Order_View_Renderer_Addsub extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $hrml = '<div id="addsub'.$value.'"></div>';
        return $hrml;
    }
}