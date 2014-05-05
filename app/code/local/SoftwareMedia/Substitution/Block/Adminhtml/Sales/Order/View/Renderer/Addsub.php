<?php

class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Renderer_Addsub extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $id =  $row->getId();
        $prodId = $row->getData('product_id');
        
        
        //Reload order invoice item to get all columns available
        $product = Mage::getModel('sales/order_invoice_item')->load($row->getId());
        
        //Base subs off original order item
        $product = Mage::getModel('sales/order_item')->load($product->getOrderItemId());
        
        
        //Load product from order item
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$product->getSku());
        //$product = Mage::getModel('catalog/product')->load($product->getProductId());
        //var_dump($product);
        //die();
        $subs = $product->getSubstitutionProductIds();
        //var_dump($subs);
        //die();
        $value = '';
        if ($subs && !$this->helper('substitution')->isComplete($row->getData('parent_id'))) {
        	$value = "<select name='sub_" . $id . "' id='sub_" . $id . "'>";
        	$value .= "<option value=''></option>";
        	if ($prodId != $product->getId()) {
	        	$sub = Mage::getModel('catalog/product')->load($product->getId());
	        	$value .= "<option value='" . $sub->getId() . "'>" . $sub->getName() . "</option>";
        	}
	        foreach ($subs as $subId) {
	        	if ($subId == $prodId)
	        		continue;
	        	$sub = Mage::getModel('catalog/product')->load($subId);
	        	$value .= "<option value='" . $sub->getId() . "'>" . $sub->getName() . "</option>";
	        }
	        $value .= "</select>";
        }
        
        $hrml = '<div id="addsub'.$id.'">' . $value . '</div>';
        return $hrml;
    }
}