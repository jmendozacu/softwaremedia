<?php

class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getId();
        $id =  $row->getData($this->getColumn()->getIndex());
        $prodId = $row->getData('product_id');
        //Reload order invoice item to get all columns available
        $product = Mage::getModel('sales/order_invoice_item')->load($row->getId());
        //$html = $product->getId();
        //Base subs off original order item
        $product = Mage::getModel('sales/order_item')->load($product->getOrderItemId());
        //$html = $product->getSku();
        $product = Mage::getModel('catalog/product')->load($product->getProductId());
        
        //Load product from order item
        //$product = Mage::getModel('catalog/product')->load($product->getProductId());
		
        $subs = $product->getSubstitutionProductIds();
        $html = '';
        
	    if ($subs && !$this->helper('substitution')->isComplete($id)) {
	    	$subUrl = $this->getUrl('substitution/adminhtml_substitution/add');
	        $html = '<a href="javascript:void(0)" onclick="addSub(\'' . $subUrl . '\',\''.$value.'\');">Add Sub</a>';
		}
		
        return $html;
    }
}