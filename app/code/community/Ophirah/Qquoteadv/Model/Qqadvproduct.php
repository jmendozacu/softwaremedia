<?php

class Ophirah_Qquoteadv_Model_Qqadvproduct extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvproduct');
    }

    /**
     * Delete product from quote
     * @param integer $id id
     */
    public function deleteQuote($id)
    {
            $this->setId($id)
                  ->delete()
                  ;
            return $this;
    }

    /**
     * Get product for the particular quote
     * @param integer $quoteId
     * @return object product information
     */
    public function getQuoteProduct($quoteId)
    {
            return $this->getCollection()
                        ->addFieldToFilter('quote_id',$quoteId)
                        ;
    }

    public function getQuoteItemChildren($quoteItem, $quoteProductId)
    {
        // Get Product from Database
        $quoteProduct = Mage::getModel('catalog/product')->load($quoteItem);

        // Configurable Product
        if($quoteProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
        {
            $childProduct = $this->getConfChildProduct($quoteProductId);
            /* REMARK:
             * Increment check method filters out items with
             * Parent_Item_Id set, so leave this unset for now
             */
//            $childProduct->setParentItemId($quoteItem);
            // Create link with parent Quote Item
            $childProduct->setParentQuoteItemId($quoteProductId);

            $quoteChildProduct[] =  $childProduct;


        }
        
        // Bundle Product
        if($quoteProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
        {
            $buyRequest = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                    ->load($quoteProductId)
                    ->getData('attribute')
            );
            $quoteChildProduct = $this->getBundleOptionProducts($quoteProduct, $buyRequest, $quoteProductId);
        }

        if(isset($quoteChildProduct)){
            $quoteProduct->setChildren($quoteChildProduct);
        }
        
        return $quoteProduct;
    }

    /**
     * Retrieve Custom Options from
     * Product or ProductId
     * 
     * @param int|Mage_Catalog_Model_Product $product
     * @return \Varien_Object|boolean
     */
    public function getCustomOptionsArray($product){
        if(is_int($product)){
            $product = Mage::getModel('catalog/product')->load($productId);
        }
                
        if(is_object($product) && $product instanceof Mage_Catalog_Model_Product && $product->getOptions()):
            // collect Product Options
            $prodOptions = new Varien_Object();
            foreach($product->getOptions() as $option){
                $valuesArray    = array();
                $optionTypeId   = $option->getOptionId();           
                $values         = $option->getValuesCollection();                
                if($values){
                    foreach($values->getData() as $value){
                        $valuesArray[$value['option_type_id']] = $value;
                    }
                }

                $prodOptions->setData($optionTypeId, $valuesArray);
            }
            return $prodOptions;
            
        endif;
        
        return false;
    }
    

    /**
     *  For configurable products,
     *  get configured simple product
     *  @param integer $productQuoteId
     *  @return childproductId
     */ 
    public function getConfChildProduct($productQuoteId){
        $quote_prod = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                                    ->load($productQuoteId)
                                    ->getData('attribute')
                                 );           

        $product = Mage::getModel('catalog/product')->load($quote_prod['product']);
        $childProduct = Mage::getModel('catalog/product_type_configurable')
                        ->getProductByAttributes($quote_prod['super_attribute'], $product);

        return Mage::getModel('catalog/product')->load($childProduct->getId());
    }

    /**
     *  For bundeld products,
     *  get bundle child products
     *  @param integer $productQuoteId
     *  @return childproductIds
     */        
    public function getBundleChildProduct($productQuoteId){
        $quote_prod         = unserialize(Mage::getModel('qquoteadv/qqadvproduct')
                                    ->load($productQuoteId)
                                    ->getData('attribute')
                                 );
        $product            = Mage::getModel('catalog/product')->load($quote_prod['product']);
        $childProductArray  = $product->getTypeInstance(true)->getChildrenIds($product->getId(), false);

        return $childProductArray;
    }

    /*
     * @param   object      // Mage_Catalog_Model_Product
     * @param   array       // Buy Request Bundle Parent Item
     * @param   integer     // Quote Product Id
     * @return  object      // Mage_Catalog_Model_Product
     */
    public function getBundleOptionProducts($product, $buyRequest, $quoteProductId = NULL)
    {
        $bundleOptions = Mage::getModel('qquoteadv/bundle')->getBundleOptionsSelection($product, $buyRequest);
        foreach($bundleOptions as $option)
        {
            foreach($option['value'] as $optionItem)
            {
                $childId    = $optionItem['id'];
                $qty        = $optionItem['qty'];
                $childProd  = Mage::getModel('catalog/product')->load($childId);
                /* REMARK:
                 * Increment check method filters out items with
                 * Parent_Item_Id set, so leave this unset for now
                 */
//                $childProduct->setParentItemId($quoteItem);
                // Create link with parent Quote Item
                if($quoteProductId != NULL){
                    $childProd->setParentQuoteItemId($quoteProductId);
                }
                $childProd->setQuoteItemQty($qty);
                $quoteChildProduct[] = $childProd;

            }
        }

        return $quoteChildProduct;
    }

	/**
	 * Add product for the particular quote to qquote_product table
	 * @param array $params product information to be added
	 *
	 */
	public function addProduct($params)
	{
	
		$checkQty =  $this->checkQuantities($params['product_id'], $params['qty']);
		if($checkQty->getHasError()){
                    return $checkQty;
		}
	
		$this->setData($params)
		      ->save()
		      ;
              
		return $this;
	}

	/**
	 * Update product if the product is already added to the table by the customer for the particular session
	 * @param integer $id row id to be updated
	 * @param array $params array of field(s) to be updated
	 */
	public function updateProduct($id,$params)
	{
		$pid = $this->load($id)->getData('product_id');
		
		$checkQty =  $this->checkQuantities($pid, $params['qty']);
		if($checkQty->getHasError()){
				return $checkQty;
		}
	
		
		$this->addData($params)
		->setId($id)
		->save()
		;

		return $this;
	}
        
	public function updateQuoteProduct($params){
	    foreach($params as $key=>$arr){
	        $item =  Mage::getModel('qquoteadv/qqadvproduct')->load($arr['id']);
                try{
                    $item->setQty($arr['qty']);
                    if($arr['client_request']){
                        $item->setClientRequest($arr['client_request']);
                    }
                    if(array_key_exists('attribute', $arr)){
                        $item->setAttribute($arr['attribute']);
                    }
                    $item->save();
                }catch(Exception $e){

                }
	   }
	    return $this;
	}
        
        /**
         * Update Product Qty
         * Used for tier selection
         * 
         * @param int $itemId
         * @param int $itemQty
         */
        public function updateProductQty($itemId, $itemQty){
            if(!(int)$itemId && !(int)$itemQty){return false;}
            $item =  Mage::getModel('qquoteadv/qqadvproduct')->load($itemId);
            if($item && $itemQty > 0){
                try{
                    $attribute  = unserialize($item->getAttribute());
                    $attribute['qty'] = (string)$itemQty;
                    $item->setAttribute(serialize($attribute));
                    $item->setQty((string)$itemQty);                    
                    $item->save();
                }catch(Exception $e){
                    Mage::log($e->getMessage());
                    return false;
                }
                return true;
            }
            return false;
        }
        
	public function getIdsByQuoteId($quoteId){
	   $ids = array();
	   $collection =  Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
                ->addFieldToFilter('quote_id',$quoteId);

	   foreach($collection as $item){
	       $ids[] = $item->getId();
	   }

	    return $ids;
	}

	public function checkQuantities($id, $qty){
            return Mage::helper('qquoteadv')->checkQuantities($id, $qty);
	}


	public function checkQtyIncrements($id, $qty){
            return Mage::helper('qquoteadv')->checkQtyIncrements($id, $qty);
	}
        
        
        /*
         * Create Array with quoted products and custom prices
         * 
         * @param   $quoteId -> Quote Id
         * @return  Array with products and custom prices 
         */        
        public function getQuoteCustomPrices($quoteId){ 
            
            // Get Custom Quote product price data from database
            $quoteItems = Mage::getModel('qquoteadv/requestitem')->getCollection()
                            ->addFieldToFilter('quote_id', $quoteId);

            // Create Array with custom quote prices, per tier
            $quoteProductPrices = array();
            foreach($quoteItems as $quoteItem){                
                $quoteProductPrices[$quoteItem->getData('quoteadv_product_id')][$quoteItem->getData('request_qty')] = $quoteItem->getData();                
            }


            // Get Custom Quote product data from database
            $quoteProducts = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
                                ->addFieldToFilter('quote_id', $quoteId);
            
            foreach($quoteProducts as $quoteProduct){
                
                // Get Attribute from product
                $attribute = unserialize($quoteProduct->getData('attribute'));                           

                // If product is configurable, super_attribute is set
                if(isset($attribute['super_attribute'])){
                    $childProd = $this->getConfChildProduct($quoteProduct->getData('id'));

                    $childInfoArray = array('entity_id', 'sku', 'allowed_to_quotemode');

                    foreach($childInfoArray as $prodData){
                        $childInfo[$prodData] = $childProd->getData($prodData);
                    }

                    $quoteProduct->setData('child_item', $childInfo);
                }              
                
                // If product is bundle, bundle_option is set
                if(isset($attribute['bundle_option'])){
                    // Get childproduct Id's
                    $childProdIds = $this->getBundleChildProduct($quoteProduct->getData('id'));

                    $childPricesArray = array();
                    $bundleInfo = array();
                    // create array with child id's and original child price
                    foreach($childProdIds as $childProdId) {
                        $prodPrices = array();
                        foreach($childProdId as $childId){
                            $prodPrice = Mage::getModel('catalog/product')->load($childId)->getPrice();                       
                            $prodPrices[$childId] = $prodPrice;   
                        }       
                        $childPricesArray[] = $prodPrices;
                    }
                    // get original bundle price
                    $bundlePrice = Mage::getModel('catalog/product')
                                        ->load($quoteProduct->getData('product_id'))
                                        ->getPrice();
                    
                    $bundleInfo['bundle_orgprice'] = $bundlePrice; 
                    $bundleInfo['child_orgprices'] = $childPricesArray;
                    // set info in object
                    $quoteProduct->setData('bundle_info', $bundleInfo);                 
                }      
                
                // set custom price
                $customBasePrice = array();
                $customCurPrice  = array();
                foreach($quoteProductPrices[$quoteProduct->getData('id')] as $key=>$value ){
                    $customBasePrice[$key] = $value['owner_base_price'];
                    $customCurPrice[$key] = $value['owner_cur_price'];
                }

                $quoteProduct->setData('custom_base_price', $customBasePrice);
                $quoteProduct->setData('custom_cur_price', $customCurPrice);         
            }
       
           return $quoteProducts;        
        }
        
        /*
         * Set custom prices to item object
         * 
         * @param   $quoteCustomPrices  -> Array with custom prices
         * @param   $quoteId            -> Quote Item
         * @param   $optionCount        -> Counter for current product option number
         * @return  Quote item object with custom prices
         */
        
        public function getCustomPriceCheck($quoteCustomPrices, $item, $optionCount = null){            

            // Get product id the current item belongs to 
            if( $item->getBuyRequest()->getData('product') ){
                $buyRequest  = $item->getBuyRequest();
                $product_id  = $buyRequest->getData('product');
            }           
            
            // Check if current item has a custom price.
            foreach($quoteCustomPrices as $quoteCustomPrice){
                
                $attribute      = unserialize($quoteCustomPrice->getData('attribute'));
                
                // Basic Compare
                $compareQuote   = $quoteCustomPrice->getData('product_id');
                $compareItem    = $item->getData('product_id');             
                
                // For products with options and parent-child relations
                if(isset($product_id) && $product_id == $quoteCustomPrice->getData('product_id') ){
                    
                    // Custom Options
                    if(isset($buyRequest['options'])){
                        if($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
                            $compareQuote   = $attribute['options'];
                            $compareItem    = $buyRequest['options'];
                        }
                        
                    }


                    // Configurable products
                    if(isset($buyRequest['super_attribute'])){
                        if($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                            $compareQuote   = $attribute['super_attribute'];
                            $compareItem    = $buyRequest['super_attribute'];
                        }
                    }

                    // Bundled Products
                    if(isset($buyRequest['bundle_option'])){
                       
                        if($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){

                            $quoteCustomPrice->getData('bundle_info')? $bundleInfo = $quoteCustomPrice->getData('bundle_info') : '';
                            
                            if($bundleInfo['bundle_orgprice'] == 0 ){

                                /*
                                * Item is bundle with dynamic pricing
                                * This product type is not supported
                                *
                                * TODO:
                                * Support this product type.
                                *  
                                */

                                return $item;

                            }

                            $compareQuote   = $attribute['bundle_option'];
                            $compareItem    = $buyRequest['bundle_option'];

                        } else {          

                            /* For bundle products with fixed prices
                             * the prices are allready set for the bundle parent item.
                             * 
                             * Bundle products with dynamic prices
                             * are not supported.
                             */

                            // #### TESTING CODE ######
                            if($quoteCustomPrice->getData('bundle_info')){
                                $bundleInfo = $quoteCustomPrice->getData('bundle_info');
                                $childOrgPrices = $bundleInfo['child_orgprices'];

                                $customBasePrice = $quoteCustomPrice->getData('custom_base_price');
                                $customCurPrice = $quoteCustomPrice->getData('custom_cur_price');
                                $divide = count($childOrgPrices) * $item->getData('qty');

                                $childCustomBasePrice = $customBasePrice[$quoteCustomPrice->getData('qty')] / $divide;                            
                                $childCustomCurPrice = $customCurPrice[$quoteCustomPrice->getData('qty')] / $divide;

                                $item->setData('custom_base_price', $childCustomBasePrice);
                                $item->setData('custom_cur_price', $childCustomCurPrice);
                            }
                            // #### END TESTING CODE ######
                            
                            return $item;                        
                        }                                              
                    }
                    
                                            
                    // Grouped products
                    if(isset($buyRequest['super_product_config']) || isset($attribute['super_product_config'])){                        
                            $compareQuote   = !empty($attribute['super_product_config']) ? $attribute['super_product_config']  : $compareQuote;
                            $compareItem    = !empty($buyRequest['super_product_config'])? $buyRequest['super_product_config'] : $buyRequest ;
                    }
                    
                }
               
                if($compareQuote == $compareItem){                  
                   
                    $customBasePrice = $quoteCustomPrice->getData('custom_base_price');
                    $customCurPrice = $quoteCustomPrice->getData('custom_cur_price');
                    
                    $item->setData('qty', $quoteCustomPrice->getData('qty'));
                    $item->setData('custom_base_price', $customBasePrice[$quoteCustomPrice->getData('qty')]);
                    $item->setData('custom_cur_price', $customCurPrice[$quoteCustomPrice->getData('qty')]) ;
                }
                
            }

            return $item;
        }     
        
        
        /*
         * Gets the amount of selected options
         * 
         * @params  $buyRequest -> the products buy request
         * @return  $return     -> number of selected options  
         */
        public function getCountMax($buyRequest){
            
            $return = 0;
            
            // array of possible options in buyRequest
            $optionAttributes = array("options", "super_attribute", "bundle_option" );
            
            foreach($optionAttributes as $optionAttribute){
                if($buyRequest->getData($optionAttribute)){
                    $return = count($buyRequest->getData($optionAttribute));
                }
            }

            return $return;
            
        }
}
