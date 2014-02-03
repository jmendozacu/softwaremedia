<?php
class OCM_Fulfillment_Model_Warehouse extends Mage_Core_Model_Abstract
{

    protected $items_for_update = array();

    public $warehouses = array('peachtree','ingram','techdata','synnex'); // important keep order to assign preference

    public function _construct()
    {
        parent::_construct();
    }

    public function loadWarehouseData($items){
    
        $all_items = array();
        $all_items_qty = array();
        foreach($items as $item){
            if ($item->getParentItemId()) continue;
            $all_items[$item->getId()] = $item->getSku();
            $all_items_qty[$item->getId()] = $item->getQtyOrdered();
            $this->items_for_update[$item->getSku()] = array();

        }

        foreach ($this->warehouses as $warehouse) {
            $model = $this->getWarehouseModel($warehouse);
            $products_data = $model->getAllItemsData($all_items, $all_items_qty);

            $this->setData($warehouse , $products_data );
            
            
            foreach($this->getData($warehouse)->getData('allItems') as $item) {
                $this->items_for_update[$item->getSku()][$warehouse.'_price'] = $item->getPrice();
            }
            
        }
        
        //$this->_updateProductWarehousePrices();
        return $this;

    }

    public function getWarehouseModel($name) {
        $warehouse = strtolower( $name );
        $model_name = 'ocm_fulfillment/warehouse_' . $warehouse;
        return Mage::getModel($model_name);

    }
    
    protected function _updateProductWarehousePrices() {

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('sku',array('in'=>array_keys($this->items_for_update)));

        foreach($products as $item) {
        echo $item->getSku()."\n";
        print_r($this->items_for_update[$item->getSku()]);
        
            foreach($this->items_for_update[$item->getSku()] as $attr => $val) {
                $item->setData($attr,$val);
            }
            $item->save();
        }
    }
    
}