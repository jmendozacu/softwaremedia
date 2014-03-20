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
                'value'
            ); 
        }
        
        if (($file = fopen($file_path, "r")) == FALSE) {
            Mage::log('count not open file '. $file_path,null,'peachtreeimport.log');
            return false;
        }

        $model = Mage::getModel('catalog/product');
		$stock_model = Mage::getModel('cataloginventory/stock_item');
		
		$count = 0;
        while (($data = fgets($file)) !== FALSE) {
        	$data = $this->csv_split($data);
        	$data[3] = str_replace(',','',$data[3]);
        	$values[] = "('".$data[0]."','".$data[2]."','".$data[3]."')";
        }
        
        $query = $query . implode(',', $values) . ";";
        $writeConnection->query($query);
	}
	
	function csv_split( $src, $comma = ',', $esc = '\\' ){
	    $a = array();
	    while( $src ){
	    	$c = $src{0};
	    	switch( $c ){
	    	// permit empty values
	    	case ',':
	    		$a[] = '';
	    		$src = substr( $src, 1 );
	    		continue 2;
	    	// ignore whitespace
	    	case ' ':
	    	case "\t":
	    		preg_match('/^\s+/', $src, $r );
	    		$src = substr( $src, strlen($r[0]) );
	    		continue 2;
	    	// quoted values
	    	case '"':
	    	case "'":
	    	case '`':
	    		$reg = sprintf('/^%1$s((?:\\\\.|[^%1$s\\\\])*)%1$s\s*(?:,|$)/', $c );
	    		break;
	    	// naked values
	    	default:
	    		$reg = '/^((?:\\\\.|[^,\\\\])*)(?:,|$)/';
	    		$c = ',';
	    	}
	    	if( preg_match( $reg, $src, $r ) ){
	    		$a[] = empty($r[1]) ? '' : str_replace( '\\'.$c, $c, $r[1] );
	    		$src = substr( $src, strlen($r[0]) );
	    		continue;
	    	}
	    	// else fail
	    	trigger_error("csv_split failure", E_USER_WARNING );
	    	break;
	    }
	    return $a;
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
