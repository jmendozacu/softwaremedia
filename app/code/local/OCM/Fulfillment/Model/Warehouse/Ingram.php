<?php
class OCM_Fulfillment_Model_Warehouse_Ingram extends OCM_Fulfillment_Model_Warehouse_Abstract
{

    const INGRAM_SKU_ATTR = 'ingram_micro_usa';
    const INGRAM_PRICE_ATTR = 'cust_cost';
    const INGRAM_QTY_ATTR = 'avail_stock_flag';
    const INGRAM_PRICE_NODE = 'Price';
    
    protected $_collection;

    public function _construct() {
        parent::_construct();
    }
    
    protected function _getQty($item) {
        
        $qty = 0;
        
        // add up all warehouse data for complete qty
        foreach ($item->Branch as $warehouse) {
        	if ((int) $warehouse->Availability > 0)
            	$qty += (int) $warehouse->Availability;
        }
        print_r($item);
        echo "INGRAM QTY: " . $qty;
        return $qty;
        
    }
    
    protected function _getRequest($xml) {
        $content_length=strlen($xml);
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_URL, 'https://newport.ingrammicro.com'); 
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $ch_result = curl_exec($ch);
        
        curl_close($ch);
		//print_r($ch_result);
		//die();
        return $ch_result;
    }

    protected function _getHeader() {
        return '
        <TransactionHeader>
        	<SenderID>MD</SenderID>
			<ReceiverID>YOU</ReceiverID>
			<CountryCode>MD</CountryCode>
			<LoginID>r7yebe4ewR</LoginID>
			<Password>SpeJa2reC3</Password>
			<TransactionID>12345</TransactionID>
		</TransactionHeader>
        ';
    }
    
    protected function _loadProduct($sku) {
        
        
        if($this->getData($sku)) return;

        //flush data if too many products are stored
        if(count($this->getData()) > 100) $this->reset();
        
        $product = Mage::getModel('catalog/product');
        $id = $product->getIdBySku($sku);
        $product->load($id);
       
        $xml_builder = '<PNARequest> <Version>2.0</Version>';
        $xml_builder .= $this->_getHeader();
        $xml_builder .='
        <PNAInformation SKU="' . $product->getData(self::INGRAM_SKU_ATTR) . '" Quantity="1"/>
        </PNARequest>
        ';  

        $result = $this->_getRequest($xml_builder);

        $xml = new SimpleXMLElement($result);

        $this->setData($sku,$xml->PNARequest->PriceAndAvailability);
        return $this;
    }
    
    public function loadCollectionArray($collection) {

        $collection->addAttributeToSelect(self::INGRAM_SKU_ATTR);
        $cloned_collection = clone $collection;
        
        $xml_builder = '<PNARequest> <Version>2.0</Version>';
        $xml_builder .= $this->_getHeader();
        //$xml_builder .= '<Detail>';
         
        foreach ($cloned_collection as $product) {
            if($ingram_sku = $product->getData(self::INGRAM_SKU_ATTR)) {
                
                $this->_collection[$ingram_sku] = array('id'=>$product->getId(),'price'=>'','qty'=>'');
            
                $xml_builder .= '<PNAInformation ManufacturerPartNumber="' . $ingram_sku . '" Quantity="1"/>';
            }
        }
          
        $xml_builder .= '
   
        </PNARequest>
        ';  

        try {
			$result = $this->_getRequest($xml_builder);
            
            $xml = new SimpleXMLElement($result);
            
            foreach($xml->PriceAndAvailability as $item) {
            	if ($item->SKUStatus) {
            		  unset($this->_collection[$ingram_sku]);
            		continue;
            	}
                $ingram_sku = (string) $item->attributes()->ManufacturerPartNumber;
                $this->_collection[ $ingram_sku ]['price'] = $item->{ self::INGRAM_PRICE_NODE };
                $this->_collection[ $ingram_sku ]['qty'] = $this->_getQty($item);
            }
 
        } catch (Exception $e) {
            
            Mage::log($e->getMessage(),null,'techdata.log');
            
        }
        

        $this->setData('collection_array',$this->_collection);
        return $this;
        
    }

    public function getQty($sku){
        
        $product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
		$stock = $product->toArray($product);

		return (int)$product->getIngramQty();
    }

    public function getPrice($sku){
        
        $product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
		$stock = $product->toArray($product);

		return (int)$product->getIngramPrice();
    }
    
    
}
