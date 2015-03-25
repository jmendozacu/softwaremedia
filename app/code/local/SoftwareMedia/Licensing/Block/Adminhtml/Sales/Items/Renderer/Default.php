<?php
/**
 * Catalog product tabs controller
 *
 * @category   SoftwareMedia
 * @package	   SoftwareMedia_Substition
 * @author	   Jeff Losee
 */
class SoftwareMedia_Licensing_Block_Adminhtml_Sales_Items_Renderer_Default extends SoftwareMedia_Licensing_Block_Adminhtml_Sales_Items_Abstract
{
 	public function getItem()
    {
        return $this->_getData('item');//->getOrderItem();
    }
    
    	public function isLicense() {
		$item = $this->getItem();
		
		$productId = $item->getProductId();
		
		$invoiceItem = Mage::getModel('sales/order_invoice_item')->load($item->getId(), 'order_item_id');
		if ($invoiceItem->getSku())
			$productId = $invoiceItem->getProductId();
			
		$product = Mage::getModel('catalog/product')->load($productId);
		if ($product->getLicenseNonlicenseDropdown() != 1210)
			return false;
			
		$warehouses = array('synnex','techdata','ingram');
		
		$price = null;
		$warehouse = "none";
		foreach($warehouses as $dist) {
			if ($product->getData($dist . "_price") && ($product->getData($dist . "_price") < $price || !$price)) {
				$warehouse = $dist;
				$price = $product->getData($dist . "_price");
			}
		}
		
		return $warehouse;
	}
}