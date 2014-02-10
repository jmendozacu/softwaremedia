<?php
/**
 * Name renderer controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */


class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Items_Column_Name_Grouped extends Mage_Adminhtml_Block_Sales_Items_Column_Name_Grouped
{

    public function getItem()
    {
        if ($this->_getData('item') instanceof Mage_Sales_Model_Order_Invoice_Item) {
            return $this->_getData('item');
        } else {
        	$orderItem = $this->_getData('item')->getOrderItem();
        	
        	$item = Mage::getModel('sales/order_invoice_item')->load($orderItem->getId(), 'order_item_id');
            return $item;
        }
    }
}
?>
