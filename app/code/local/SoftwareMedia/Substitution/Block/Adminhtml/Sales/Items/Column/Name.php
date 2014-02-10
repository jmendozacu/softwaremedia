<?php
/**
 * Name renderer controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */


class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
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
    public function getOrderItem()
    {
        if ($this->_getData('item') instanceof Mage_Sales_Model_Order_Item) {
            return $this->_getData('item');
        } else {
            return $this->_getData('item')->getOrderItem();
        }
    }
    
    public function getOrderOptions()
    {
        $result = array();
        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }
}
?>
