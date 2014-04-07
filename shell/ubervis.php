<?php

require "/var/www/magento.softwaremedia.com/htdocs/app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');
//OCM_Fulfillment_Model_Warehouse_Ingram
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();


/*
  $product = Mage::getModel('catalog/product')->load(7169);
  echo $product->setData('etilize_manufactureid','Test1');
  $product->save();

  $collection =Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('sku', array('like'=>array('%KA-KL1843ACCFS%')))->addAttributeToSelect('*');
  foreach($collection as $item) {
  //echo $item->setData('etilize_manufactureid','Test');
  //$item->save();
  }
  die();
  $order = Mage::getModel('sales/order')->load(121);

  $result = Mage::helper('ocm_frauddetection')->isViolations($order);
  if($result){
  $order->setState('new','orders_suspect_hold',$result,false)->save();
  }

  die();

  /*
  $collection = Mage::getModel('catalog/product')->getCollection()
  ->addAttributeToSelect('*')
  ->addAttributeToFilter('sku','AD-61101754');
 */



//Mage::getModel('ocm_fulfillment/observer');
//updatePriceQtyFromCsv
//Mage::getModel('ocm_fulfillment/warehouse_synnex')->urlConnect();
//$model = Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFrom();
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
//$ubervis_prod = $api->callApi(Zend_Http_Client::GET, 'product/mpn/VMPXRBENS14/100/0');
//var_dump($ubervis_prod);
//$marketers = $api->callApi(Zend_Http_Client::GET, 'marketer/comparison/1');
//var_dump($ubervis_prod);
//die();
Mage::log('ubervis started',null,'ubervis.log');

Mage::getModel('ubervisibility/observer')->updateProduct();
//Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQty($collection);


//            ->addAttributeToFilter('sku','AC-VMPXRPENS13');
/*          //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('cpc_price')
            //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('price')
            //->addAttributeToSelect('qty')
*/

//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();
//Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFrom();
