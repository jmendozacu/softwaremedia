<?php
/*
 * This is were values for internal are returned
 */
class OCM_Fulfillment_Model_Warehouse_Peachtree extends OCM_Fulfillment_Model_Warehouse_Abstract
{	
    public function _construct() {
        parent::_construct();
    }
    
	public function getQty($sku){
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
	

		return (int)$product->getPtQty();
	}
	
    public function getPrice($sku) {
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
        return $product->getPtAvgCost();
    }
    
	public function importCsv() {
		$resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $truncateQuery = "TRUNCATE TABLE ocm_peachtree;";
        $writeConnection->query($truncateQuery);
        
        $query = "insert into ocm_peachtree (sku,qty,cost) values ";
        $values = array();
        
        chmod('../media/peachtree.csv',0777);
        //if (!$file_path) {
            $file_path = Mage::getBaseDir() . '/media/peachtree.csv';
        //}
        
        if (!count($headers)) {
            $headers = array(
                'sku',
                'skip',
                'qty',
                'cost',
                'skip'
            ); 
        }
        
        if (($file = fopen($file_path, "r")) == FALSE) {
            Mage::log('count not open file '. $file_path,null,'peachtreeimport.log');
            return false;
        }

        $model = Mage::getModel('catalog/product');
		$stock_model = Mage::getModel('cataloginventory/stock_item');
		
		$count = 0;
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
        	$line = array_combine($headers, $data);
        	$values[] = "('".$line['sku']."','".$line['qty']."','".$line['cost']."')";
        }
        
        $query = $query . implode(',', $values) . ";";
        $writeConnection->query($query);
	}
	
	public function updatePriceQtyFrom() {
		$time = time();
		$to = date('Y-m-d H:i:s', $time);
		$lastTime = $time - (1*60*60); // 60*60*24
		$from = date('Y-m-d H:i:s', $lastTime);
		
		 $collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('peachtree_updated','left')
            ->addattributeToFilter('peachtree_updated',array(array('lt' => $from),array('null' => true)))
            ->addAttributeToSelect('*');
             $collection->getSelect()
				->joinleft(
					array('pv' => 'catalog_product_flat_1'), 'pv.entity_id=e.entity_id', array()
				)
				->joininner(
					array('peach' => 'ocm_peachtree'), 'pv.sku=peach.sku', array('peachtree_qty' => 'qty','peachtree_cost' => 'cost')
				);
				
            $collection->setPageSize(70);
            $this->updatePriceQty($collection);
	}
	
	
	public function updatePriceQty($collection) {
		$target = time() - (60 * 60 * 23);
		$sku_attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'sku');
		
       
        
        $helper = Mage::helper('ocm_fulfillment'); 
      
        
		$collection->load();
		foreach($collection as $product) {
			$stock_model = Mage::getModel('cataloginventory/stock_item');
			
				//Mage::log($line['sku'],null,'peachtreeimport.log');
                $product->setData('peachtree_updated',now());
                $product->setData('pt_qty',$product->getData('peachtree_qty'));
                $product->setData('pt_avg_cost',$product->getData('peachtree_cost'));
                
               
				$product->setData('peachtree_updated',now());
	          $helper->updateStock($product);
		}   
	}
	
}
