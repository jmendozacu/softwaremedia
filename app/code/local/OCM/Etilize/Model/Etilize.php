<?php

require('Spex.php');

class OCM_Etilize_Model_Etilize extends Mage_Core_Model_Abstract {    
	
	protected $_deleteAllEtilizeAttributes = false;
	protected $_deleteAllProductImages = false;
	
	protected $_entityTypeId;
	protected $_productID;
	protected $_error;
	protected $_spexCatalogId = 223438;
	protected $_attributeSetId = 9;  //Got from eav_attribute_group table
	protected $_attributeGroupId = 197;
	protected $_productType = "simple";
	protected $_attributeType;
	    

	

	public function updateSpex() {
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		
		Mage::log("\n\n************************ Etilize Updated Start at ".date("l, F d, Y h:i" ,time())." ************************", null, 'OCM_Spex.log');
		
		if ($this->_deleteAllEtilizeAttributes)
			$this->deleteAllEtilizeAttributes();
        
    	$collection = Mage::getModel('catalog/product')
    	->getCollection()
    	->addAttributeToSelect('sku')
    	->addAttributeToSelect('name')
    	->addAttributeToSelect('manufacturer_pn_2')
    	->addAttributeToSelect('etilize_manufactureid')
    	->addAttributeToFilter('etilize_updated',"No")
    	->setPageSize(100);

    	//Cycle through the collection of products
		foreach ($collection as $product) {
			//Setup Time System
			list($usec, $sec) = explode(' ', microtime());
   			$script_start = (float) $sec + (float) $usec;
   			
   			$this->setError(false);
   			$this->setProductID($product->getId());
   			
   			//Get data from Etilize
			$etilizeResult = $this->getEtilizeData($product);
			
			//testing
			Mage::log($etilizeResult    , null, 'OCM_Spex.log');
   						
			//This is where the attributes will be processed.
			$attributes = $etilizeResult->getAttributes();
            $this->buildAttributes($attributes, $product);
			
			$skus = $etilizeResult->getSkus();
            $this->buildSkuAttributes($skus, $product);

			

			
			if (!$this->getError())
			{
				if ($this->_deleteAllProductImages)
				{
					$this->removeAllImages($product->getId());
				
					$resources = null;
					$resources = $etilizeResult->getResources();
					$this->updateImages($resources, $product);
				}
			}
   			
   			if (!$this->getError()){
   				//Setup Time collection
				list($usec, $sec) = explode(' ', microtime());
	   			$script_end = (float) $sec + (float) $usec;
	   			$elapsed_time = round($script_end - $script_start, 5);
	   			$logMessage = "\n".$etilizeResult;
	   			$logMessage .= "\n----------------------------------------------------------------";
	   			$logMessage .= "\nUpdating this product took : ".$elapsed_time." seconds";
	   			$logMessage .= "\n----------------------------------------------------------------";
	   			Mage::log($logMessage, null, 'OCM_Spex.log');
   			
   				$etilizeResult = array(
   					"etilize_result" => "Product last updated at ".date("l, F d, Y h:i" ,time()).$logMessage,
   					"etilize_updated" => "1");
   				try {
					Mage::getSingleton('catalog/product_action')
        				->updateAttributes(array(0 => $this->_productID), $etilizeResult, 0);
   				}
				catch (Exception $e)
				{
						Mage::log($e, null, 'OCM_Spex.log');
				}
				
   			}elseif ($this->getError())
   			{
   				$etilizeResult = array(
   					"etilize_result" => "Errors in product updated check OCM_Spex.log file in /var/log",
   					"etilize_updated" => "0");
   				try {
					Mage::getSingleton('catalog/product_action')
        				->updateAttributes(array(0 => $this->_productID), $etilizeResult, 0);
   				}
				catch (Exception $e)
				{
						Mage::log($e, null, 'OCM_Spex.log');
				}
				
   			}
		}//end of product collection
		Mage::log("\n\n************************ Etilize Updated Ended at ".date("l, F d, Y h:i" ,time())." ************************", null, 'OCM_Spex.log');
	}
	
	private function buildAttributes($attributes, $product)
	{
    	foreach($attributes as $group) {
				foreach($group as $attribute) {
					
					try {
    					$attributeLabel = $this->cleanAttributeName($attribute['name']);
    					$attributeCode = $this->prepareAttributeString($attributeLabel);
    					}
    				catch (Exception $e)
    				{
    				    Mage::log('Error in cleaning attribute name and value',null,"OCM_Spex.log");
        				Mage::log($e,null,"OCM_Spex.log");
    				}
					
					$this->selectAttributeType($attributeLabel);
					
					if ($this->attributeExists($attributeCode)){
						Mage::log($attributeLabel. " does exist", null, 'OCM_Spex.log');
					}
					else {
						if (strlen($attributeCode) <2)
							continue;
							
						Mage::log($attributeLabel. " does not exist", null, 'OCM_Spex.log');
						Mage::log("Creating new attribute ".$attributeCode, null, 'OCM_Spex.log');
						$this->createAttribute($attributeCode, $attributeLabel);
					}

					if (($this->_attributeType == "text") || ($this->_attributeType == "textarea"))
					{
						$this->updateProductAttributes($attributeCode, $attribute, $product);
					}
        			elseif ($this->_attributeType == "multiselect")
        			{
        				$cleanedValues = $this->cleanAttributeValue($attributeCode, $attribute);
        				$optionId = array();
        				foreach ($cleanedValues as $value)
        					$this->optionExist($attributeCode, $value);
        				if (is_array($cleanedValues)) 
        				{
        					foreach ($cleanedValues as $value)
        					{
        						$optionId[] = $this->getOptionValue($attributeCode, $value);
        					} 
                           	$implodedValue = implode(',', $optionId);
                        }
                        $this->updateProductAttributes($attributeCode, $implodedValue, $product);
                        unset ($cleanedValues);
        			}
				}//end of foreach($group as $attribute)
			}//end of foreach($attributes as $group)
	}
	
	private function buildSkuAttributes($attributes, $product)
	{
    	foreach($attributes as $group) {
				foreach($group as $attribute) {
					
					try {
    					$attributeLabel = $this->cleanAttributeName($attribute['type']);
    					$attributeCode = $this->prepareAttributeString($attributeLabel);
    					}
    				catch (Exception $e)
    				{
    				    Mage::log('Error in cleaning attribute name and value',null,"OCM_Spex.log");
        				Mage::log($e,null,"OCM_Spex.log");
    				}
					
					
					if ($this->attributeExists($attributeCode)){
						Mage::log($attributeLabel. " does exist", null, 'OCM_Spex.log');
					}
					else {
						Mage::log($attributeLabel. " does not exist", null, 'OCM_Spex.log');
						Mage::log("Creating new attribute ".$attributeCode, null, 'OCM_Spex.log');
						$this->createAttribute($attributeCode, $attributeLabel);
					}

						$this->updateProductAttributes($attributeCode, $attribute['number'], $product);

				}//end of foreach($group as $attribute)
			}//end of foreach($attributes as $group)
	}

	private function updateImages($resources, $product)
	{
		$pdfList = null; //Var to hold list of pdf files.
		$gotResource = null;
		
		//Reset the media gallery for a new product.
		if ( is_null($product->getMediaGalleryImages()))
		{
			$product->setMediaGallery (array('images'=>array (), 'values'=>array ()));
		}
			
		if (!is_null($resources))
		{
			foreach($resources as $resource)
			{				
				$localPath = Mage::getBaseDir('media')."/import/".basename($resource['type'])."-".basename($resource['url']);
				$gotResource = $this->getEtilizeResource($resource['url'], $localPath);
						
				if (($gotResource)&&((strpos($localPath, ".jpg")== true)))
				{
					$typeOfResource = (string)$resource['type'];
					switch ($typeOfResource)
					{
						case ($typeOfResource == "Large"):
							$product->addImageToMediaGallery($localPath, array('image', 'small_image'),true,true);
							break;
						case ($typeOfResource == "Thumbnail"):
							$product->addImageToMediaGallery($localPath, array('thumbnail'),true,true);
							break;
						case (!is_numeric($typeOfResource)):
							$product->addImageToMediaGallery($localPath, null,true,false);
							break;
						/*default :
							$product->addImageToMediaGallery($localPath, null,true,false);*/
					}
				}//end of if for jpg images
		
				//Add PDF's to product
				if (($gotResource)&&((strpos($localPath, ".pdf")== true)))
				{
					$newPath = Mage::getBaseDir('media')."/pdf/".basename($resource['type'])."-".basename($resource['url']);
					rename($localPath, $newPath);
					$pdfList .= $newPath.",";
							
					try 
					{
						//Update PDF attribute
						Mage::getSingleton('catalog/product_action')
		        			->updateAttributes(array(0 => $this->_productID), array("pdf" => $pdfList), 0);
					}
					catch (Exception $e)
					{
						Mage::log($e, null, 'OCM_Spex_PDF_Error.log');
						$this->setError(true);
					}		
				}
			}//end of foreach($resources as $resource)
				try 
				{
					$product->save();
				}
				catch (Exception $e)
				{
					Mage::log($e, null, 'OCM_Spex.log');
					$this->setError(true);
				}
		}//end of if checking to make sure resources is not null.
	}
	
	private function updateProductAttributes($attributeCode, $attributeData, $product)
	{
		$attributeArray = array();
		$attributeArray[$attributeCode] = $attributeData."";
		
		//Mage::log($attributeArray,null,'OCM_att.log');
		try 
		{
			
			Mage::getSingleton('catalog/product_action')
        		->updateAttributes(array(0 => $this->_productID), $attributeArray , 0);
		}
		catch (Exception $e)
		{
			Mage::log("Product SKU :".$product->getSku(), null, 'OCM_Spex_AtributeError.log' );
			Mage::log("Product ID : ".$product->getId(), null, 'OCM_Spex_AtributeError.log' );
			Mage::log("Array Key : ".$attributeCode, null, 'OCM_Spex_AtributeError.log');
			Mage::log("Array Value : ".$attributeData, null, 'OCM_Spex_AtributeError.log');
			$this->setError(true);
		}
		unset ($attributeArray);
	}
	
	
	private function deleteAllEtilizeAttributes()
	{
		Mage::log('In deleteAllEtilizeAttributes',null,'OCM_Spex.log');
		
		$nodeChildren = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setAttributeGroupFilter($this->_attributeGroupId)
                ->addVisibleFilter()
                ->checkConfigurableProducts()
                ->load();

            if ($nodeChildren->getSize() > 0) {
                foreach ($nodeChildren->getItems() as $child) {
                	Mage::log($child->getAttributeCode(),null,'OCM_Spex.log');
                	$child->delete();
                }
            }
	}
	
	
	private function cleanAttributeValue($attributeCode, $attributeValue)
	{
		$value = array();
		if ( $attributeCode == "weight")
		{
			if (strstr($attributeValue, "lb")!=false)
			{
				$value[] = str_replace('lb', '', $attributeValue);
			}
			elseif (strstr($attributeValue, "oz")!=false)
			{
				$value[] = str_replace('oz', '', $attributeValue);
				$value[] = ((float)$attributeValue/16);
			}
		}
		elseif ($this->_attributeType == "text")
		{
			$value[] = $attributeValue;
		}
		elseif ($this->_attributeType == "multiselect")
		{
			$xml_string = '<?xml version="1.0" encoding="iso-8859-1"?><document>';
			$attributeValue = html_entity_decode($attributeValue);

			if (strlen($attributeValue) != strlen(strip_tags($attributeValue)))
			{
				$xml_string .= $attributeValue."</document>";
				if($xml = simplexml_load_string( $xml_string ) ) 
				{
    				foreach( $xml as $li ) 
    				{
        				$value[] = $li;
        				if (strlen($li > 20))
        					Mage::log($attributeCode." : ".$li,null,'OCM_LongMulti.log');
    				}
				}
			}
			else {
				$value[] = $attributeValue;
			}
		}
		
		if ($attributeValue =="Not Applicable")
		{
			$value = array();
			$value[] ="";
		}
			
		return $value;
	}
	
	private function getEtilizeData($product)
	{
			//get product and catalog id's
			$partNumber = $product->getData('manufacturer_pn_2');
			$mfgId = $product->getEtilizeManufactureid();
			
			Mage::log("\n\n---------- ".$product->getName()." ----------", null, 'OCM_Spex.log');
			Mage::log("Product SKU: ".$product->getSku(), null, 'OCM_Spex.log');
			Mage::log("Etilize Manufacturer Part Number: ".$partNumber, null, 'OCM_Spex.log');
			Mage::log("Etilize Manufacturer ID: ".$mfgId, null, 'OCM_Spex.log');
			
			//Connect to Etilize and get product listing
			try {
				$catalog = new SpexCatalog($this->_spexCatalogId);
				Mage::log("Success creating SpexCatalog Object", null, 'OCM_Spex.log');
			}
			catch (Exception $e){
				Mage::log("Error creating SpexCatalog Object", null, 'OCM_Spex.log');
				Mage::log($e, null, 'OCM_Spex.log');
				$this->setError(true);
			}
			
			try {
				$etilizeResult = $catalog->getProductForId((int)$mfgId, $partNumber);
				Mage::log("Success retrieving product information", null, 'OCM_Spex.log');
			}
			catch (Exception $e){
				Mage::log("Error retrieving product information", null, 'OCM_Spex.log');
				Mage::log($e, null, 'OCM_Spex.log');
				$this->setError(true);
				$etilizeResult = null;
			}
			
			return $etilizeResult;
	}

	private function cleanAttributeName($name)
	{
		$cleanAttribute ="";
		
		switch ($name)
		{
			case ($name == "Marketing Information"):
				//$cleanAttribute = "Description";
				break;
			case ($name == "Features"):
				$cleanAttribute = "Product Features";
				break;
			case ($name == "Weight Approximate"):
				$cleanAttribute = "Weight";
				break;
			default :
				$cleanAttribute = $name;			
		}
		
		return $cleanAttribute;		 
	}
	
	private function getError() {
		return $this->_error;
	}

	private function setError($_error) {
		$this->_error = $_error;
	}

	private function reviewError() {
	
	}
	
	private function getProductID() {
		return $this->_productID;
	}

	private function setProductID($_productID) {
		$this->_productID = $_productID;
	}

	private function getOptionValue($attributeCode, $optionValue)
	{
		$this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
		
		$attribute = Mage::getModel('catalog/resource_eav_attribute')
	            ->loadByCode($this->_entityTypeId, $attributeCode);
		
	    // Checking if the attribute is either select or multiselect type.
		if($attribute->usesSource())
		{
			$options = $attribute->getSource()->getAllOptions(true, true);
			
			$hasValue = null;
			foreach ($options as $x)
			{
				if ($x['label']== $optionValue)
					$hasValue = $x['value'];
			}
		}
		return $hasValue;	    
	}
	
	private function optionExist($attributeCode, $optionValue)
	{
		$this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
		
		$attribute = Mage::getModel('catalog/resource_eav_attribute')
	            ->loadByCode($this->_entityTypeId, $attributeCode);
	            
	    // Checking if the attribute is either select or multiselect type.
		if($attribute->usesSource())
		{
			$options = $attribute->getSource()->getAllOptions(true, true);
			//Mage::log($options,null,'OCM_options.log');
			
			try 
			{
				$hasValue = false;
				foreach ($options as $x)
				{
					if ($x['label']== $optionValue)
						$hasValue = true;
				}
				if (!$hasValue)
				{
					Mage::log('Adding new option value to '.$attributeCode.' with value '.$optionValue,null,'OCM_Spex.log');
					
					/*
					$myData = array ('value'=> array('optionone'=>array(0=>strip_tags($optionValue))));
				    $attribute->setData('option', $myData);
				    $attribute->save();
				    */
					
					//Add new Option
					$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
					$option['attribute_id'] = $setup->getAttributeId($this->_entityTypeId,$attributeCode);
					$option['value'][0][0] = $optionValue;
					
					$setup->addAttributeOption($option);
				}
				else
				{
					Mage::log('Option value already existed in '.$attributeCode.' with value '.$optionValue,null,'OCM_Spex.log');
				}
			}
			catch (Exception $e)
			{
				Mage::log("Error adding option value ".$e,null,'OCM_Spex.log');
			}
		}
	}
	private function removeAllImages($productId)
	{
	
	    $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
	    $items = $mediaApi->items($productId);
	    foreach($items as $item)
	        $mediaApi->remove($productId, $item['file']);
	}

	private function getEtilizeResource($url, $localPath)
	    {
	    	Mage::log("Grabbing Etilize Resource : ".$url, null, 'OCM_Spex.log');
	    	
	    	try {
	    		$ch = curl_init ($url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
				$rawdata=curl_exec($ch);
				curl_close ($ch);
				$fp = fopen($localPath,'w');
				fwrite($fp, $rawdata);
				fclose($fp);
				Mage::log("Success Grabbing Etilize Resource : ".$localPath, null, 'OCM_Spex.log');
				return true;}
	    	catch (Exception $e){
	    		Mage::log("Error Grabbing Etilize Resource : ".$url, null, 'OCM_Spex.log');
	    		Mage::log($e, null, 'OCM_Spex.log');
	    		return false;}	
	    }//end of getEtilizeResource function

	private function selectAttributeType($label)
	{
		$navarray = array();
	

		$navarray[] = 'Operating System';
		$navarray[] = 'Manufacturer';


		if (in_array($label,$navarray))
		 	$this->_attributeType = 'multiselect';
		else 
			$this->_attributeType = 'text';
	} 
	
	 
	private function createAttribute($code, $label)
	{
		$helper = Mage::helper('catalog/product');
		
	    $_attribute_data = array(
	    	'group' => 'General',
	        'attribute_code' => $code,
	        'is_global' => '1',
	        'frontend_input' => $this->_attributeType, //'boolean',
	        'default_value_text' => '',
	        'default_value_yesno' => '0',
	        'default_value_date' => '',
	        'default_value_textarea' => '',
	        'is_unique' => '0',
	        'is_required' => '0',
	        'apply_to' => array($this->_productType), //array('grouped')
	        'is_configurable' => '0',
	        'is_searchable' => '1',
	        'is_visible_in_advanced_search' => '1',
	        'is_comparable' => '1',
	        'is_used_for_price_rules' => '0',
	        'is_wysiwyg_enabled' => '0',
	        'is_html_allowed_on_front' => '1',
	        'is_visible_on_front' => '1',
	        'used_in_product_listing' => '0',
	        'used_for_sort_by' => '0',
	        'frontend_label' => $label,
	    	'is_filterable' => '1',
	    	'is_filterable_in_search' => '1',
	    	'default_value' => '',
	    	'backend_type' => 'varchar'
	    );
	 
	    $model = Mage::getModel('catalog/resource_eav_attribute');
	    
	     $_attribute_data['source_model'] = $helper->getAttributeSourceModelByInputType($_attribute_data['frontend_input']);
	     $_attribute_data['backend_model'] = $helper->getAttributeBackendModelByInputType($_attribute_data['frontend_input']);
	     
	     if ($this->_attributeType == "text")
	     {
	     	$_attribute_data['is_filterable'] = '0';
	     	$_attribute_data['is_filterable_in_search'] = '0';
	     }
	    
	    
	    $model->addData($_attribute_data);
	 
	    $model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
	    $model->setIsUserDefined(1);
	    //$model->setAttributeSetId($this->_attributeSetId);
	    //$model->setAttributeGroupId($this->_attributeGroupId);
	 
	    try {
	        $model->save();
	        
	        $attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code','catalog_product')->getFirstItem(); // This is because the you adding the attribute to catalog_products entity ( there is different entities in magento ex : catalog_category, order,invoice... etc ) 
            $attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection(); // this is the attribute sets associated with this entity 
            $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setCodeFilter($code)
                ->getFirstItem();
            $attCode = $attributeInfo->getAttributeCode();
            $attId = $attributeInfo->getId();
            foreach ($attSetCollection as $a)
            {
                $set = Mage::getModel('eav/entity_attribute_set')->load($a->getId());
                $setId = $set->getId();
                $group = Mage::getModel('eav/entity_attribute_group')->getCollection()
                    ->addFieldToFilter('attribute_set_id',$setId)
                    ->addFieldToFilter('attribute_group_name', 'Etilize')
                    ->setOrder('attribute_group_id',ASC)->getFirstItem();
                    
                $groupId = $group->getId();
                $newItem = Mage::getModel('eav/entity_attribute');
                $newItem->setEntityTypeId($attSet->getId()) // catalog_product eav_entity_type id ( usually 10 )
                          ->setAttributeSetId($setId) // Attribute Set ID
                          ->setAttributeGroupId($groupId) // Attribute Group ID ( usually general or whatever based on the query i automate to get the first attribute group in each attribute set )
                          ->setAttributeId($attId) // Attribute ID that need to be added manually
                          ->setSortOrder(10) // Sort Order for the attribute in the tab form edit
                          ->save()
                ;
                echo "Attribute ".$attCode." Added to Attribute Set ".$set->getAttributeSetName()." in Attribute Group ".$group->getAttributeGroupName()."<br>\n";
            }
	        
	        Mage::log("----- New Attribute Created -----", null, 'OCM_Spex.log');
	        Mage::log("Attribute Code: ".$code, null, 'OCM_Spex.log');
	        Mage::log("Attribute Label: ".$label, null, 'OCM_Spex.log');} 
	    catch (Exception $e) { 
	    	echo '<p>Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage().'</p>'; 
	    	Mage::log("----- Error Creating Attribute -----", null, 'OCM_Spex.log');
	    	Mage::log($e->getMessage(), null, 'OCM_Spex.log');
	    	$this->setError(true);
	    }
	}//end of private function createAttribute



	private function attributeExists($attributeCode)
	{
	    try {
    	   $this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
		
           $attribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode($this->_entityTypeId, $attributeCode);
	    
    	    if ($attribute->getId())
    	    {
    	    		$this->_attributeType = $attribute->getFrontendInput();
    	    		//Mage::log($attributeCode." : ".$attribute->getFrontendInput(),null,'OCM_att_type.log');
    	    		return true;
    
    	    }
    	    else 
    	    	return false;
	    }
	    catch (Exception $e)
	    {
	        Mage::log('In attributeExists try catch',null,'OCM_Spex.log');
	        Mage::log($e,null,'OCM_Spex.log');
        }
		

	}
    
	private function prepareAttributeString($arg_attribute){
		$foo = str_replace(" ","_",$arg_attribute);
		$foo = preg_replace("/[^A-Za-z0-9_]/","",$foo);
		$finalString = substr(strtolower($foo),0,29);
		Mage::log("In prepareAttributeString here is final string : ".$finalString, null, 'OCM_Spex.log');
		return $finalString;
	}   

	
}
