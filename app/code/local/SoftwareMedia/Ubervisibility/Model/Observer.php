<?php

/**
 * Description of Observer
 *
 * @author david
 */
class SoftwareMedia_Ubervisibility_Model_Observer extends Varien_Event_Observer {

	public function updateProduct() {
		$from = date('Y-m-d H:i:s', time() - (24 * 60 * 60));
		Mage::app()->setCurrentStore(1);
		Mage::log('Starting ubervis update', null, 'ubervis.log');
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect('ubervis_updated', 'left');
		$collection->addAttributeToSelect('*');
		$collection->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array('manage_stock', 'min_sale_qty'));
		$collection->getSelect()->where('(at_ubervis_updated.value < \'' . $from . '\' AND e.updated_at > at_ubervis_updated.value) OR at_ubervis_updated.value IS NULL');
		$collection->getSelect()->where('sku NOT LIKE "%HOME" AND sku NOT LIKE "%FBA"');
		$collection->getSelect()->where('type_id != "bundle"');
//		$collection->getSelect()->where('sku = "MS-T5D01575"');
		$collection->setOrder('ubervis_updated', 'ASC');
		$collection->setPageSize(100);

		foreach ($collection as $prod) {
			$updated_data = $prod->getData();
			$mpn = $updated_data['manufacturer_pn_2'];
			Mage::log('Updating ' . $updated_data['name'], null, 'ubervis.log');
			Mage::log('Sku: ' . $prod->getSku(), null, 'ubervis.log');

			$api = new SoftwareMedia_Ubervisibility_Helper_Api();
			$ubervis_prod = $api->callApi(Zend_Http_Client::GET, 'product/sku/' . $prod->getSku() . '/100/0');
			$data = array();

			if (!empty($ubervis_prod->errorMessage)) {
				Mage::log('Error: ' . $ubervis_prod->errorMessage, null, 'ubervis.log');
				continue;
			}

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
			$data['cpcPrice'] = $updated_data['cpc_price'];
			$data['msrp'] = $updated_data['msrp'];
			$data['minimumSalesQuantity'] = intval($updated_data['min_sale_qty']);
			$data['manageStock'] = ($updated_data['manage_stock'] > 0);

			$cats = $prod->getCategoryIds();
			foreach ($cats as $category_id) {
				$_cat = Mage::getModel('catalog/category')->load($category_id);
				if ($_cat->getParentId() == 51) {
					$hasCat = true;
					break;
				}
			}

			$subs = $prod->getSubstitutionLinkCollection();

			if ($subs) {
				$sync = false;
				foreach ($subs as $sub) {
					if ($sub->getPriceSync() > 0) {
						$sync = true;
						break;
					}
				}

				$data['isPriceSynchronized'] = $sync;
			} else {
				$data['isPriceSynchronized'] = false;
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

			if (substr_compare($mpn, 'DL', strlen($mpn) - 2, 2) === 0) {
				$data['productCondition'] = 'Downloadable';
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
				$data['cpcPrice'] = $data['price'];
			}
			if (empty($data['ceiling'])) {
				$data['ceiling'] = 0;
			}
			if (empty($data['siteFloor'])) {
				$data['siteFloor'] = 0;
			}
			if (empty($data['weight'])) {
				$data['weight'] = 0;
			}
			if (empty($data['productCondition'])) {
				$data['productCondition'] = 'NEW';
			}
			if (empty($data['upc'])) {
				$data['upc'] = '';
			}
			if (strcasecmp($data['shippingGroup'], 'ALWAYS_PHYSICAL') == 0) {
				$data['shippingGroup'] = 'PHYSICAL';
			}
			if (empty($data['minimumSalesQuantity'])) {
				$data['minimumSalesQuantity'] = 0;
			}

			if (empty($ubervis_prod)) {
				Mage::log('Product is being created', null, 'ubervis.log');
				// create product
				$ubervis_prod = $api->callApi(Zend_Http_Client::POST, 'product/', $data);

				if (!empty($ubervis_prod->errorMessage)) {
					Mage::log('Error: ' . $ubervis_prod->errorMessage, null, 'ubervis.log');
					continue;
				}

				$prod_id = $ubervis_prod->id;

				//Add MPN
				$api->callApi(Zend_Http_Client::POST, 'product/mpn/', array('productsId' => $prod_id, 'mpn' => $mpn));
			} else {
				Mage::log('Product is being updated', null, 'ubervis.log');
				$prod_id = $ubervis_prod->id;

				$ubervis_prod = $api->callApi(Zend_Http_Client::PUT, 'product/' . $prod_id, $data);

				if (!empty($ubervis_prod->errorMessage)) {
					Mage::log('Error: ' . $ubervis_prod->errorMessage, null, 'ubervis.log');
					continue;
				}
			}

			$prod->setUbervisUpdated(date('Y-m-d H:i:s', strtotime('+1 hour')));
			$prod->save();
		}


		Mage::log('Finished Updating Ubervis', null, 'ubervis.log');
	}

	public function retrieveProducts() {
		$api = new SoftwareMedia_Ubervisibility_Helper_Api();
		$csv_content = array();

		$ubervis_updated_site_prods = $api->callApi(Zend_Http_Client::GET, 'product/updated-price/site', array(), 999);
		
		$ubervis_updated_cpc_prods = $api->callApi(Zend_Http_Client::GET, 'product/updated-price/cpc', array(), 999);

		$sku_list = array();

		if (!empty($ubervis_updated_site_prods)) {
			foreach ($ubervis_updated_site_prods as $prod) {
				$prod_arr = (array) $prod;
				$sku_list[$prod_arr['sku']] = $prod_arr['sku'];
			}
		}

		if (!empty($ubervis_updated_cpc_prods)) {
			foreach ($ubervis_updated_cpc_prods as $prod) {
				$prod_arr = (array) $prod;
				$sku_list[$prod_arr['sku']] = $prod_arr['sku'];
			}
		}

		Mage::log('# of updated: ' . count($sku_list), null, 'ubervis.log');

		if (!empty($sku_list)) {
			$collection = Mage::getModel('catalog/product')->getCollection();
			$collection->addAttributeToSelect('ubervis_updated', 'left');
			$collection->addAttributeToSelect('*');
			$collection->addAttributeToFilter('status', array('eq' => 1));
			$collection->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array('manage_stock'));
			$collection->getSelect()->where('sku NOT LIKE "%HOME" AND sku NOT LIKE "%FBA"');
			$collection->getSelect()->where('manage_stock = 1');
			$collection->getSelect()->where('sku IN (?)', $sku_list);

			Mage::log('# of Magento to update: ' . count($collection), null, 'ubervis.log');
			$csv_content[] = array('Magento Updated:', count($collection));
			$csv_content[] = array('ID', 'SKU', 'Title', 'Old CPC', 'New CPC', 'Old Site', 'New Site');

			foreach ($collection as $prod) {
				Mage::log('Retrieving ' . $prod->getName(), null, 'ubervis.log');
				Mage::log('Sku: ' . $prod->getSku(), null, 'ubervis.log');

				$ubervis_prod = $api->callApi(Zend_Http_Client::GET, 'product/sku/' . $prod->getSku() . '/100/0');
				$data = array();

				if (is_array($ubervis_prod)) {
					$ubervis_prod = $ubervis_prod[0];
					$data = (array) $ubervis_prod;
				}

				if ($ubervis_prod != null) {
					$csv_row = array();
					$old_cpc = $prod->getCpcPrice();
					$old_site = $prod->getPrice();
					Mage::log('Before Price: ' . $prod->getPrice(), null, 'ubervis.log');
					Mage::log('Before CPC Price: ' . $prod->getCpcPrice(), null, 'ubervis.log');

					if (!empty($ubervis_prod->price)) {
						$prod->setPrice($ubervis_prod->price);
					}

					if (!empty($ubervis_prod->cpcPrice)) {
						$prod->setNewCpcPrice($ubervis_prod->cpcPrice);
					}

					Mage::log('Price: ' . $prod->getPrice(), null, 'ubervis.log');
					Mage::log('CPC Price: ' . $prod->getNewCpcPrice(), null, 'ubervis.log');

					$prod->setUbervisUpdated(date('Y-m-d H:i:s', strtotime('+1 hour')));
					$prod->save();

					$csv_row[0] = $ubervis_prod->id;
					$csv_row[1] = $prod->getSku();
					$csv_row[2] = $prod->getName();
					$csv_row[3] = $old_cpc;
					$csv_row[4] = $prod->getNewCpcPrice();
					$csv_row[5] = $old_site;
					$csv_row[6] = $prod->getPrice();

					$csv_content[] = $csv_row;
				}
			}

			$results = $this->generateCsv($csv_content);

			if (!empty($results)) {
				$mailTemplate = Mage_Core_Model_Email_Template::getMail();
				$mailTemplate->setFrom('customerservice@softwaremedia.com', 'Uberviz');
				$mailTemplate->setSubject('Uberviz To Magento Updates!');
				$mailTemplate->addTo('lisa@softwaremedia.com');
				$mailTemplate->setBodyHTML(file_get_contents($results['value']));

				$mailTemplate->createAttachment(file_get_contents($results['value']), Zend_Mime::TYPE_OCTETSTREAM, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'uber_to_magento_update.csv');
				$helper = Mage::helper('smtppro');
				$transport = $helper->getTransport(1);

				$mailTemplate->send($transport);
			}
		}
	}

	private function generateCsv($data, $delimiter = ',', $enclosure = '"') {
		$csv = new Varien_Io_File();
		$path = Mage::getBaseDir('var') . DS . 'export' . DS;
		$file = $path . 'uber_to_magento_update.csv';

		$csv->setAllowCreateFolders(true);
		$csv->open(array('path' => $path));
		$csv->streamOpen($file, 'w+');
		$csv->streamLock(true);

		foreach ($data as $row) {
			$csv->streamWriteCsv($row);
		}

		return array('type' => 'filename', 'value' => $file);
	}

}
