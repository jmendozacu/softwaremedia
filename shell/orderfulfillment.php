<?php

require_once 'abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Compiler extends Mage_Shell_Abstract
{
    public function run()
    {

        $products = Mage::getModel('catalog/product')->getCollection()
            //->addAttributeToFilter('etilize_result',array('neq'=>''))
            //->addAttributeToSelect('etilize_result')
            ->addAttributeToSelect('ingram_micro_canada')
            ->addAttributeToSelect('ingram_micro_usa ')
            ->addAttributeToSelect('ingram_price')
            ->addAttributeToSelect('ingram_qty')
            ->addAttributeToSelect('ingram_sku')
            ->addAttributeToSelect('synnex')
            ->addAttributeToSelect('synnex_ca')
            ->addAttributeToSelect('synnex_price')
            ->addAttributeToSelect('synnex_qty ')
            ->addAttributeToSelect('techdata_price')
            ->addAttributeToSelect('techdata_qty')
            ->addAttributeToSelect('tech_data')
            ->addAttributeToSelect('tech_data_ca')
        ;

            echo //'"",'.
                '"'.'sku'.'","'.
                'ingram_micro_canada'.'","'.
                'ingram_micro_usa'.'","'.
                'ingram_price'.'","'.
                'ingram_qty'.'","'.
                'ingram_sku'.'","'.
                'synnex'.'","'.
                'synnex_ca'.'","'.
                'synnex_price'.'","'.
                'synnex_qty'.'","'.
                'techdata_price'.'","'.
                'techdata_qty'.'","'.
                'tech_data'.'","'.
                'tech_data_ca'.'"'.
                "\r\n";


        foreach ($products as $p) {
            echo //'"'.$p->getData('etilize_result').'","'.
                '"'.$p->getData('sku').'","'.
                $p->getData('ingram_micro_canada').'","'.
                $p->getData('ingram_micro_usa').'","'.
                $p->getData('ingram_price').'","'.
                $p->getData('ingram_qty').'","'.
                $p->getData('ingram_sku').'","'.
                $p->getData('synnex').'","'.
                $p->getData('synnex_ca').'","'.
                $p->getData('synnex_price').'","'.
                $p->getData('synnex_qty').'","'.
                $p->getData('techdata_price').'","'.
                $p->getData('techdata_qty').'","'.
                $p->getData('tech_data').'","'.
                $p->getData('tech_data_ca').'"'.
                "\r\n";
            
        }


die();
/*
        Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFromCsv();
die();
/**/

        $catalog_size = Mage::getModel('catalog/product')->getCollection()->getSize();
        $page_size = ceil($catalog_size / 14);
        $total_pages = ceil($catalog_size / $page_size);
        
        for($i = 2; $i <= $total_pages; $i++) {
        
            echo "Updateing page: " . $i;
        
            Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData($i);
        }


die();

/*
        $this->createAttribute('pt_qty', 'PT Qty');
        die;
*/

        //TODO LOOK UP the cost attribute to look at for pulling from warehouse, and also which one to update.

        //TODO must add price,qty,pt_avg_cost,pt_qty to collection
        $collection = Mage::getModel('catalog/product')->getCollection()
            //->addattributeToFilter('tech_data',array('notnull'=>true))
            //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('cpc_price')
            //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('price')
            //->addAttributeToSelect('qty')
            ->addattributeToFilter('sku','LV-2353ABU')
            ->addAttributeToSelect('pt_avg_cost')
            ->addAttributeToSelect('pt_qty')
            ->setPageSize(5)
            ->setCurPage(1);
            
            
        //die ($collection->getSelect());
        
        
        //echo 'cy '.$collection->count()."\n\n";
        
            
        $techdata = Mage::getModel('ocm_fulfillment/warehouse_techdata')->loadCollectionArray($collection);
        $ingram = Mage::getModel('ocm_fulfillment/warehouse_ingram')->loadCollectionArray($collection);
        $synnex = Mage::getModel('ocm_fulfillment/warehouse_synnex')->loadCollectionArray($collection);


        $foo = $collection->getSelect();


        foreach ($collection as $f) {
            echo $f->getSku().' '.$f->getData('tech_data').' '.$f->getData('ingram_micro_usa')."\n";
        }

        
        echo "techdata\n";
        print_r($techdata->getCollectionArray());
        echo "ingram\n";
        print_r($ingram->getCollectionArray());
        echo "synnex\n";
        print_r($synnex->getCollectionArray());

        
        $techdata_sku_attr = OCM_Fulfillment_Model_Warehouse_Techdata::TECH_DATA_SKU_ATTR;
        $synnex_sku_attr   = OCM_Fulfillment_Model_Warehouse_Synnex::SYNNEX_SKU_ATTR;
        $ingram_sku_attr   = OCM_Fulfillment_Model_Warehouse_Ingram::INGRAM_SKU_ATTR;


        $techdata_products = $techdata->getCollectionArray();
        $synnex_products   = $synnex->getCollectionArray();
        $ingram_products   = $ingram->getCollectionArray();

        $stock_model = Mage::getModel('cataloginventory/stock_item');
       
       foreach($collection as $product) {
       
           $price_array = array();
           $qty = 0;
           
           // Ingram MUST be the end of the array for this to work
           foreach (array('techdata','synnex','ingram') as $warehouse_name) {
           
               if(isset(${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ])) {
                   $product->setData($warehouse_name.'_price',${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['price']);
                   $product->setData($warehouse_name.'_qty',${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['qty']);
    
                   if($product->getData($warehouse_name.'_qty') > 0) {
                       $price_array[ $product->getData($warehouse_name.'_price') ] = true;
                       $qty += $product->getData($warehouse_name.'_qty');
                   }
               }
               
           }
           
           if ($product->getData('pt_qty')<1) {
               ksort($price_array);
               reset($price_array);
               $lowest_cost = key($price_array);
               $product->setData('cost',$lowest_cost);
           } else {
               $product->setData('cost',$product->getData('pt_avg_cost'));
           }
           $product->setData('warehouse_updated_at',now());
           
           $stock_model->loadByProduct($product->getId());
           $stock_model->setData('qty',$qty);
           if($qty) $stock_model->setData('is_in_stock',1);

           
           try {
               $product->save();
               $stock_model->save();
           } catch (Exception $e) {
               Mage::log($e->getMessage());
           }
           
       }


        
        die();
  
  
        $order = Mage::getModel('sales/order')->load(84);
        $order->setStatus('processing')->setState('processing')->save();
        echo $order->getStatus().' '.$order->getState();
        die;
    
/*
    Mage::getModel('ocm_fulfillment/warehouse_ingram')->urlConnect();
    die;
*/


/*
    Mage::getModel('ocm_fulfillment/warehouse_synnex')->urlConnect();
    die;
*/


    
/*
    $collection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToFilter('ingram_sku',array('neq'=>''))
        ->addAttributeToSelect('ingram_sku');
        
    foreach ($collection as $i) {
        echo $i->getSku(). ' ' . $i->getIngramSku()."\n";
    }
    die;
*/
    
    
    
       // $model = Mage::getModel('ocm_fulfillment/warehouse_peachtree');
    
//echo    $model->getCollection()->getSize();
   // die;
    
       // $model = Mage::getModel('ocm_fulfillment/observer');
       // $model->evaluateOrdersDaily();
	//$model->getQty('SZ-SMGIFTCARDV');
	//$model->insertIngramData();

        //$model->setData(array(
          //  'change_code' => 'Hello World',
        //));
    
        //$model->save();
        
        //echo 'done';
        //die;
    
    //echo Mage::getModel('ocm_fulfillment/warehouse_techdata')->getQty('10226038')."\n";
    //echo Mage::getModel('ocm_fulfillment/warehouse_techdata')->getPrice('10226038')."\n";
    
    //die('done');
        $observer = Mage::getModel('ocm_fulfillment/observer');
        $observer->evaluateOrdersDaily();
        echo "DONE.";

    
    }


private function createAttribute($code, $label)
	{
		$helper = Mage::helper('catalog/product');
		
	    $_attribute_data = array(
	    	'group' => 'Warehouse info',
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
	        
	        echo("----- New Attribute Created -----"."\n");
	        echo("Attribute Code: ".$code."\n");
	        echo("Attribute Label: ".$label."\n");} 
	    catch (Exception $e) { 
	    	echo '<p>Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage().'</p>'; 
	    	echo("----- Error Creating Attribute -----"."\n");
	    	echo($e->getMessage()."\n");
	    	$this->setError(true);
	    }
	}//end of private function createAttribute



}

$shell = new Mage_Shell_Compiler();
$shell->run();
