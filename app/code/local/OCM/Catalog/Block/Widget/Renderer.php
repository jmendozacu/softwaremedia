<?php
class OCM_Catalog_Block_Widget_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return $value;
    }
}