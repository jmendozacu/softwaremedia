<?php

/**
 * Description of Observer
 *
 * @author david
 */
class SoftwareMedia_Ubervisibility_Model_Observer extends Varien_Event_Observer {

	public function updateProduct() {
		$from = date('Y-m-d H:i:s', time() - (24*60*60));
		
		Mage::log('Starting ubervis update');
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect('ubervis_updated', 'left');
		//$collection->addAttributeToFilter('sku','TM-CMRN0046');
		$collection->setOrder('ubervis_updated','ASC');
		//$collection->addAttributeToFilter('sku','AC-VMPXRBENS11');
		$collection->addAttributeToSelect('*');
		$collection->getSelect()->where('(at_ubervis_updated.value < \'' . $from . '\' AND e.updated_at > at_ubervis_updated.value) OR at_ubervis_updated.value IS NULL');
		$collection->setPageSize(100);

		foreach ($collection as $prod) {
			$updated_data = $prod->getData();
			$mpn = $updated_data['manufacturer_pn_2'];
			Mage::log('Updating ' . $updated_data['name']);

			$api = new SoftwareMedia_Ubervisibility_Helper_Api();
			$ubervis_prod = $api->callApi(Zend_Http_Client::GET, 'product/sku/' . $prod->getSku() . '/100/0');
			if (is_array($ubervis_prod))
				$ubervis_prod = $ubervis_prod[0];
				
			$prod_id = null;

			if (empty($ubervis_prod)) {
			echo "Empty";
				// create product
				$prodData = array('title' => $updated_data['name']) ;
				if ($prod->getAdminId())
					$prodData['adminId'] = $prod->getAdminId();
				$ubervis_prod = $api->callApi(Zend_Http_Client::POST, 'product/', $prodData);
				
				
				$prod_id = $ubervis_prod->id;
				
				//Add MPN
				$api->callApi(Zend_Http_Client::POST, 'product/mpn/', array('productsId' => $prod_id, 'mpn' => $mpn));

			} else {
			echo "not empty";
				$prod_id = $ubervis_prod->id;
			}

			$data = array();
			$brand = $prod->getResource()->getAttribute('brand')->getFrontend()->getValue($prod);
			$_imageUrl =  Mage::getModel('catalog/product_media_config')->getMediaUrl( $prod->getImage() );
			
			$data['title'] = $updated_data['name'];
			$data['productDescriptionsId'] = array('productsId' => $prod_id, 'clientsId' => 1);
			$data['link'] = $prod->getProductUrl();
			$data['link'] = str_replace('warehouse.php/','',$data['link']);
			$data['link'] = str_replace('index.php/','',$data['link']);
			$data['link'] = str_replace('ubervis.php/','',$data['link']);
			$data['imageLink'] = $_imageUrl;
			$data['sku'] = $updated_data['sku'];
			$data['upc'] = $updated_data['upc'];
			$data['brand'] = $brand;
			$data['description'] = $updated_data['description'];
			$data['message'] = $updated_data['stock_message'];
			$data['edition'] = $updated_data['version'];
			$data['weight'] = $updated_data['weight'];
			$data['cost'] = $updated_data['cost'];
			$data['price'] = $updated_data['price'];
			$data['msrp'] = $updated_data['msrp'];

			
			$data['shippingGroup'] = strtoupper($prod->getResource()->getAttribute('package_id')->getFrontend()->getValue($prod));
			
			if ($prod->getResource()->getAttribute('status')->getFrontend()->getValue($prod) == 'Enabled')
				$data['status'] = 'ACTIVE';
			else
				$data['status'] = 'INACTIVE';
			$data['version']= $prod->getResource()->getAttribute('multi_product_version')->getFrontend()->getValue($prod);
			$data['productType'] = $prod->getResource()->getAttribute('product_type')->getFrontend()->getValue($prod);
			if ($prod->getResource()->getAttribute('license_nonlicense_dropdown')->getFrontend()->getValue($prod) == 'Yes')
				$data['isLicensing'] = true;
			else
				$data['isLicensing'] = false;
			
			var_dump($data);
			$stock_model = Mage::getModel('cataloginventory/stock_item');
			$stock_model->loadByProduct($prod->getId());

			$data['quantity'] = (int) $stock_model->getQty();
			

			if ((!empty($data['quantity']) && $data['quantity'] > 0) || $stock_model->getManageStock() == 0) {
				$data['availability'] = 'IN_STOCK';
			} else {
				$data['availability'] = 'OUT_OF_STOCK';
			}
			
			//var_dump($data);
			//die();
			Mage::log('Updating Descriptions NOw',null,'ubervis.log');
			//echo $ubervis_prod[0]->descriptions;
			if ($ubervis_prod->descriptions) {
				foreach ($ubervis_prod->descriptions as $desc) {
					Mage::log('Updating ' . $desc->productDescriptionsId->marketersId . ' prod ' . $prod_id,null,'ubervis.log');
					$newData =  array_merge((array) $desc, $data);
					$newData['productDescriptionsId']['marketersId']  = $desc->productDescriptionsId->marketersId;
					$return = $api->callApi(Zend_Http_Client::POST, 'product/descriptions/',$newData);
					var_dump( $return );
				}
			} else {
				$marketers = $api->callApi(Zend_Http_Client::GET, 'marketer/comparison/1');
				foreach($marketers as $marketer) {
					$data['productDescriptionsId']['marketersId'] = $marketer->id;
					Mage::log('Marketer ' . $marketer->id . " prod id " . $prod_id,null,'ubervis.log');
					//$data['marketersId'] = $marketer->id;
					$return = $api->callApi(Zend_Http_Client::POST, 'product/descriptions/', $data);
					echo $return;
					//Mage::log($return,NULL,'ubervis.log');
				}
			}
			$prod->setUbervisUpdated(date('Y-m-d H:i:s', strtotime('+1 hour')));
			$prod->save();
		}


		Mage::log('Finished Updating Ubervis');
	}

}
