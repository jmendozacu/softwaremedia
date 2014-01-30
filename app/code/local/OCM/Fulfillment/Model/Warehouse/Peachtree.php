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
		$stock = $product->toArray($product);

		return (int)$product->getPtQty();
	}
	
    public function getPrice($sku) {
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
        return $product->getPtAvgCost();
    }

    public function updatePriceQtyFromCsv($file_path=null, $headers=array()) {
        
        if (!$file_path) {
            $file_path = Mage::getBaseDir() . DS . 'var' . DS . 'peachtree_import' . DS . 'pt_qty_cost.csv';
        }
        
        if (!count($headers)) {
            $headers = array(
                'sku',
                'qty',
                'cost'
            ); 
        }
        
        if (($file = fopen($file_path, "r")) == FALSE) {
            Mage::log('count not open file '. $file_path,null,'peachtreeimport.log');
            return false;
        }

        $model = Mage::getModel('catalog/product');

		$count = 0;
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            
            $line = array_combine($headers, $data);
            $product_id = $model->getIdBySku( $line['sku'] );
            
            try {
            
                $product = $model->load($product_id);
                
                if(!$product_id) {
                    throw new Exception('Failed to load: '.$line['sku']);
                }
				
				//Update any values older than 23 hours
                $target = time() - (60 * 60 * 23);
                Mage::log(strtotime($product->getData('peachtree_updated')) . "->" . $target,null,'peachtreeimport.log');
                $updated = $product->getData('peachtree_updated');
                if (strtotime($updated) > $target && !empty($updated)) {
                	Mage::log("Skipped -> " . $product->getId() . " -> " . $line['sku'],null,'peachtreeimport.log');
                	continue;
					
				}
				
				$count++;
		
				Mage::log($line['sku'],null,'peachtreeimport.log');
                $product->setData('peachtree_updated',now());
                $product->setData('pt_qty',$line['qty']);
                $product->setData('pt_avg_cost',$line['cost']);
                $product->save();
                
                if ($count > 500) {
                	Mage::log('Break',null,'peachtreeimport.log');
                	break;
                }
                
            } catch (Exception $e) {
                Mage::log($e->getMessage(),null,'peachtreeimport.log');
            }
        }
        fclose($file);
        
    }

}
