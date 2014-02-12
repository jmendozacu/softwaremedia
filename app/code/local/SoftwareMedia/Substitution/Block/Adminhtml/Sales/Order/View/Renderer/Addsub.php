<?php

class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Renderer_Addsub extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $id =  $row->getId();
        $prodId = $row->getData('product_id');
        $product = Mage::getModel('catalog/product')->load($prodId);
        $subs = $product->getSubstitutionProductIds();
        $value = '';
        if ($subs && !$this->helper('substitution')->isComplete($row->getData('parent_id'))) {
        	$value = "<select name='sub_" . $id . "' id='sub_" . $id . "'>";
        	$value .= "<option value=''></option>";
	        foreach ($subs as $subId) {
	        	$sub = Mage::getModel('catalog/product')->load($subId);
	        	$value .= "<option value='" . $sub->getId() . "'>" . $sub->getName() . "</option>";
	        }
	        $value .= "</select>";
        }
        $hrml = '<div id="addsub'.$id.'">' . $value . '</div>';
        return $hrml;
    }
}