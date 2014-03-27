<?php

/**
 * Description of Observer
 *
 * @author david
 */
class SoftwareMedia_Ubervisibility_Model_Observer extends Varien_Event_Observer {

	public function updateProduct() {
		Mage::log('Starting ubervis update');
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect('ubervis_updated', 'left');
//		$collection->addAttributeToFilter(
//			array(
//				array(
//					'attribute' => 'updated_at',
//					'gt' => new Zend_Db_Expr('at_ubervis_updated.value')
//				),
//				array(
//					'attribute' => new Zend_Db_Expr('at_ubervis_updated.value'),
//					'null' => true
//				)
//			)
//		);
		$collection->getSelect()->where('e.updated_at > at_ubervis_updated.value OR at_ubervis_updated.value IS NULL');
		$collection->setPageSize(100);

		echo $collection->getSelect();

		foreach ($collection as $prod) {
//			$updated_data = $prod->getData();
//			$mpn = $updated_data['manufacturer_pn_2'];
//			Mage::log('Updating ' . $updated_data['name']);
//
//			$api = new SoftwareMedia_Ubervisibility_Helper_Api();
//			$ubervis_prod = $api->callApi(Zend_Http_Client::GET, 'product/mpn/' . $mpn . '/100/0');
//			$prod_id = null;
//
//			if (empty($ubervis_prod)) {
//				// create product
//				$new_prod = $api->callApi(Zend_Http_Client::POST, 'product/', array('title' => $updated_data['name']));
//				$prod_id = $new_prod->id;
//			} else {
//				$prod_id = $ubervis_prod[0]->id;
//			}
//
//			$data = array();
//
//			$data['title'] = $updated_data['name'];
//
//			$data['link'] = Mage::getBaseUrl() . $updated_data['url_path'];
//			$data['imageLink'] = Mage::getBaseUrl() . $updated_data['image'];
//			$data['sku'] = $updated_data['sku'];
//			$data['upc'] = $updated_data['upc'];
//			$data['description'] = $updated_data['description'];
//			$data['message'] = $updated_data['stock_message'];
//			$data['edition'] = $updated_data['version'];
//			$data['weight'] = $updated_data['weight'];
//			$data['cost'] = $updated_data['cost'];
//			$data['price'] = $updated_data['price'];
//			$data['msrp'] = $updated_data['msrp'];
//
//			$data['quantity'] = $updated_data['stock_item']->getQty();
//
//			if (!empty($data['quantity']) && $data['quantity'] > 0) {
//				$data['availability'] = 'IN_STOCK';
//			} else {
//				$data['availability'] = 'OUT_OF_STOCK';
//			}
//
//			foreach ($ubervis_prod[0]->descriptions as $desc) {
//				$api->callApi(Zend_Http_Client::POST, 'product/descriptions/', array_merge((array) $desc, $data));
//			}
//
//			$prod->setUbervisUpdated(date('Y-m-d H:i:s'));
//			$prod->save();
		}


		Mage::log('Finished Updating Ubervis');
	}

}
