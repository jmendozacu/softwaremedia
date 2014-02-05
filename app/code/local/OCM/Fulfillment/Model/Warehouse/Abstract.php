<?php
abstract class OCM_Fulfillment_Model_Warehouse_Abstract extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
    }

    abstract function getQty($sku);
    abstract function getPrice($sku);

    // $items array(item_id => product_skus)
    public function setItems($items) {
        
        $this->setdata('items',$items);
        return $this;
    }

    // $items array(item_id => product_skus)
    public function getAllItemsData($items, $items_qty) {
    
        $this->setItems($items);
        $all_items = array();
        $can_fulfill = true;
        $totalCost = 0;
        foreach($items as $item_id => $sku) {
            $qty = $this->getQty($sku);
            $price = $this->getPrice($sku);
            $all_items[$item_id] = new Varien_Object(array(
                'sku'   => $sku,
                'qty'   => $qty,
                'price' => $price,
            ));
            $totalCost += $price;
            if ($qty < $items_qty[$item_id]) $can_fulfill = false;
        }
        
        $all_items_data = new Varien_Object(array(
            'allItems' => $all_items,
            'total_cost' => $totalCost,
            'can_fulfill' => $can_fulfill,
        ));
        
        return $all_items_data;
        
    }
    
    /*
     * Not in use should be in each warehouse to fulfill for that warehouse.
     */
    // $order is a Mage_Sales_Model_Order object
    // $items is an array of either Mage_Sales_Model_Order_item object or Varien_Object. Must have methods getSku() getQty()
    public function fulfill($order , $items = null) {
        if (!$items) $items = $order->getAllItems();
        
        if (true == true) { //fulfillent successful
            return true;
        }
        return false;
    }
    
}