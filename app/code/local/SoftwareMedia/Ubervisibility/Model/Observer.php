<?php

/**
 * Description of Observer
 *
 * @author david
 */
class SoftwareMedia_Ubervisibility_Model_Observer extends Varien_Event_Observer {

	public function updateProduct() {
		$from = date('Y-m-d H:i:s', time() - (24 * 60 * 60));

		Mage::log('Starting ubervis update', null, 'ubervis.log');
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect('ubervis_updated', 'left');
		$collection->setOrder('ubervis_updated', 'ASC');
		$collection->addAttributeToSelect('*');
		$collection->joinField('manages_stock', 'cataloginventory/stock_item', 'use_config_manage_stock', 'product_id=entity_id', '{{table}}.manage_stock=1');
		$collection->getSelect()->where('(at_ubervis_updated.value < \'' . $from . '\' AND e.updated_at > at_ubervis_updated.value) OR at_ubervis_updated.value IS NULL');
		$collection->setPageSize(100);

		foreach ($collection as $prod) {
			$updated_data = $prod->getData();
			$mpn = $updated_data['manufacturer_pn_2'];
			Mage::log('Updating ' . $updated_data['name'], null, 'ubervis.log');
			Mage::log('Sku: ' . $prod->getSku(), null, 'ubervis.log');

			$api = new SoftwareMedia_Ubervisibility_Helper_Api();
			$ubervis_prod = $api->callApi(Zend_Http_Client::GET, 'product/sku/' . $prod->getSku() . '/100/0');
			$data = array();

			if (is_array($ubervis_prod)) {
				$ubervis_prod = $ubervis_prod[0];
				$data = (array) $ubervis_prod;
			}

			$brand = $prod->getResource()->getAttribute('brand')->getFrontend()->getValue($prod);
			$_imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($prod->getImage());

			if ($prod->getAdminId()) {
				$data['adminId'] = $prod->getAdminId();
			}

			$data['title'] = $updated_data['name'];
			$data['link'] = $prod->getProductUrl();
			$data['link'] = str_replace('warehouse.php/', '', $data['link']);
			$data['link'] = str_replace('index.php/', '', $data['link']);
			$data['link'] = str_replace('ubervis.php/', '', $data['link']);
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

			$cats = $prod->getCategoryIds();
			foreach ($cats as $category_id) {
				$_cat = Mage::getModel('catalog/category')->load($category_id);
				if ($_cat->getParentId() == 51) {
					$hasCat = true;
					break;
				}
			}


			$data['shippingGroup'] = strtoupper($prod->getResource()->getAttribute('package_id')->getFrontend()->getValue($prod));

			if ($prod->getResource()->getAttribute('status')->getFrontend()->getValue($prod) == 'Enabled')
				$data['status'] = 'ACTIVE';
			else
				$data['status'] = 'INACTIVE';
			if ($updated_data['multi_product_version'])
				$data['version'] = $prod->getResource()->getAttribute('multi_product_version')->getFrontend()->getValue($prod);
			$data['productType'] = $prod->getResource()->getAttribute('product_type')->getFrontend()->getValue($prod);
			if ($prod->getResource()->getAttribute('license_nonlicense_dropdown')->getFrontend()->getValue($prod) == 'License Product')
				$data['isLicensing'] = true;
			else
				$data['isLicensing'] = false;

			$stock_model = Mage::getModel('cataloginventory/stock_item');
			$stock_model->loadByProduct($prod->getId());

			$data['quantity'] = (int) $stock_model->getQty();


			if ((!empty($data['quantity']) && $data['quantity'] > 0) || $stock_model->getManageStock() == 0) {
				$data['availability'] = 'IN_STOCK';
			} else {
				$data['availability'] = 'OUT_OF_STOCK';
			}

			$prod_id = null;

			// Set the defaults if we must
			if (empty($data['msrp'])) {
				$data['msrp'] = 0;
			}
			if (empty($data['cost'])) {
				$data['cost'] = 0;
			}
			if (empty($data['price'])) {
				$data['price'] = 0;
			}
			if (empty($data['bid'])) {
				$data['bid'] = 0;
			}
			if (empty($data['cpcFloor'])) {
				$data['cpcFloor'] = 0;
			}
			if (empty($data['cpcPrice'])) {
				$data['cpcPrice'] = 0;
			}
			if (empty($data['ceiling'])) {
				$data['ceiling'] = 0;
			}
			if (empty($data['siteFloor'])) {
				$data['siteFloor'] = 0;
			}
			if (empty($data['productCondition'])) {
				$data['productCondition'] = 'NEW';
			}
			if (strcasecmp($data['shippingGroup'], 'ALWAYS_PHYSICAL') == 0) {
				$data['shippingGroup'] = 'PHYSICAL';
			}

			if (empty($ubervis_prod)) {
				Mage::log('Product is being created', null, 'ubervis.log');
				// create product
				$ubervis_prod = $api->callApi(Zend_Http_Client::POST, 'product/', $data);

				$prod_id = $ubervis_prod->id;

				//Add MPN
				$api->callApi(Zend_Http_Client::POST, 'product/mpn/', array('productsId' => $prod_id, 'mpn' => $mpn));
			} else {
				Mage::log('Product is being updated', null, 'ubervis.log');
				$prod_id = $ubervis_prod->id;

				$ubervis_prod = $api->callApi(Zend_Http_Client::PUT, 'product/' . $prod_id, $data);
			}

			$prod->setUbervisUpdated(date('Y-m-d H:i:s', strtotime('+1 hour')));
			$prod->save();
		}


		Mage::log('Finished Updating Ubervis', null, 'ubervis.log');
	}

}
