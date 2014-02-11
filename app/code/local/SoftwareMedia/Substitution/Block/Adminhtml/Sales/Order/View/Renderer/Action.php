<?php

class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getId();
        $id =  $row->getData($this->getColumn()->getIndex());
        $prodId = $row->getData('product_id');
        $product = Mage::getModel('catalog/product')->load($prodId);
        $subs = $product->getSubstitutionProductIds();
        
	    if ($subs && !$this->helper('substitution')->isComplete($id)) {
	    	$subUrl = $this->getUrl('substitution/adminhtml_substitution/add');
	        $html = '<a href="javascript:void(0)" onclick="addSub(\'' . $subUrl . '\',\''.$value.'\');">Add Sub</a>';
		}
		
        return $html;
    }
}