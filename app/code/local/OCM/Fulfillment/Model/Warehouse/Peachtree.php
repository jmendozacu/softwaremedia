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
		return (int)$stock['stock_item']['qty'];
	}
	
    public function getPrice($sku) {
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
        return $product->getCost();
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

        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            
            $line = array_combine($headers, $data);
            $product_id = $model->getIdBySku( $line['sku'] );
            
            try {
            
                $product = $model->load($product_id);
                if(!$product->getId()) {
                    throw new Exception('Failed to load: '.$line['sku']);
                }
                $product->setData('pt_qty',$line['qty']);
                $product->setData('pt_avg_cost',$line['cost']);
                $product->save();
                echo $line['sku'] . " saved \n";
                
            } catch (Exception $e) {
                Mage::log($e->getMessage(),null,'peachtreeimport.log');
            }
        }
        fclose($handle);
        
    }

}
