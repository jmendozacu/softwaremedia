<?php
class OCM_Fulfillment_Model_Warehouse_Techdata extends OCM_Fulfillment_Model_Warehouse_Abstract
{

    const TECH_DATA_SKU_ATTR = 'tech_data';
    const TECH_DATA_PRICE_NODE = 'UnitPrice1';
    
    protected $_collection;

    public function _construct() {
        parent::_construct();
    }
    
    protected function _getQty($item) {
        
        $qty = 0;
        
        // add up all warehouse data for complete qty
        foreach ($item->WhseInfo as $warehouse) {
            $qty += (int) $warehouse->Qty;
        }
        return $qty;
        
    }
    
    protected function _getRequest($xml) {
    
        $content_length=strlen($xml);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_URL, 'https://tdxml.techdata.com/xmlservlet'); 
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $ch_result = curl_exec($ch);

        curl_close($ch);

        return $ch_result;
    }

    protected function _getHeader() {
        return '
              <Header>
                  <UserName>403634</UserName>  
                  <Password>T3chD123!</Password>
                  <ResponseVersion>1.4</ResponseVersion>
                  <TransSetIDCode>846SEND</TransSetIDCode>
                  <TransControlID>10000</TransControlID>
                  <ResponseVersion>1.4</ResponseVersion>
              </Header>
        ';
    }
    
    protected function _loadProduct($sku) {
        
        
        if($this->getData($sku)) return;

        //flush data if too many products are stored
        if(count($this->getData()) > 100) $this->reset();
        
        $product = Mage::getModel('catalog/product');
        $id = $product->getIdBySku($sku);
        $product->load($id);
       
        $xml_builder = '<XML_PriceAvailability_Submit>';
        $xml_builder .= $this->_getHeader();
        $xml_builder .='
          <Detail>
                <LineInfo>
                      <RefIDQual>VP</RefIDQual>
                      <!-- <RefID>10226038</RefID> -->
                      <RefID>'.$product->getData(self::TECH_DATA_SKU_ATTR).'</RefID>
                </LineInfo>
          </Detail>
        </XML_PriceAvailability_Submit>
        ';  

        $result = $this->_getRequest($xml_builder);

        $xml = new SimpleXMLElement($result);

        $this->setData($sku,$xml->Detail->LineInfo);
        return $this;
    }
    
    public function loadCollectionArray($collection) {

        $collection->addAttributeToSelect(self::TECH_DATA_SKU_ATTR);
        $cloned_collection = clone $collection;
        
        $xml_builder = '<XML_PriceAvailability_Submit>';
        $xml_builder .= $this->_getHeader();
        $xml_builder .= '<Detail>';
         
        foreach ($cloned_collection as $product) {
            if($tech_sku = $product->getData(self::TECH_DATA_SKU_ATTR)) {
                
                //$this->_collection[$tech_sku] = array('id'=>$product->getId(),'price'=>'','qty'=>'');
            
                $xml_builder .= '
                <LineInfo>
                      <RefIDQual>VP</RefIDQual>
                      <RefID>'.$tech_sku.'</RefID>
                </LineInfo>
                ';
            }
        }
          
        $xml_builder .= '</Detail>
        </XML_PriceAvailability_Submit>
        ';  

        try {
            Mage::log($xml_builder,null,'techdata_builder.log');
            
            $result = $this->_getRequest($xml_builder);
            
            Mage::log($result,null,'techdata_response.log');
            
            //Mage::log($result,null,'techdata.log');
            
            $xml = new SimpleXMLElement($result);
            //$this->_collection = array();
            foreach($xml->Detail->LineInfo as $item) {
            	
                $tech_sku = (string) $item->RefID1;
				if ($item->ErrorInfo)
					continue;
					
                $this->_collection[ $tech_sku ] = array();
                $price = (string) str_replace('$','',$item->{ self::TECH_DATA_PRICE_NODE });
                $price = str_replace(',','',$price);
                //echo $price;
                //die();
                $this->_collection[ $tech_sku ]['price'] = $price;
                $this->_collection[ $tech_sku ]['qty'] = $this->_getQty($item);
            }
            
        } catch (Exception $e) {
        	//Set each value to an empty array so it will error out
            foreach($cloned_collection  as $product) {
            	if($tech_sku = $product->getData(self::TECH_DATA_SKU_ATTR)) {
	            	$this->_collection[$tech_sku] = array();
	            }
            }
            //$this->_collection = $cloned_collection;
            Mage::log($e->getMessage(),null,'techdata_error.log');
            
        }
        

        $this->setData('collection_array',$this->_collection);
        return $this;
        
    }

	public function getQty($sku){
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
		$stock = $product->toArray($product);

		return (int)$product->getTechdataQty();
	}
	
    public function getPrice($sku) {
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
        return $product->getTechdataPrice();
    }

    
    
}
