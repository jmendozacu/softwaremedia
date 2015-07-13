<?php

/**
 * Catalog product controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Catalog' . DS . 'ProductController.php';

class SoftwareMedia_Substitution_Adminhtml_Catalog_SubstitutionController extends Mage_Adminhtml_Catalog_ProductController {
	protected function _isAllowed()
    {
        return true;
    }
	/**
	 * Product substitution page
	 */
	public function substitutionAction() {
		$this->_initProduct();
		$this->loadLayout();
		$this->getLayout()->getBlock('catalog.product.edit.tab.substitution')
			->setProductsSubstitution($this->getRequest()->getPost('products_substitution', null));
		$this->renderLayout();
	}

	/**
	 * Get substitution products grid
	 */
	public function substitutionGridAction() {
		$this->_initProduct();
		$this->loadLayout();
		$this->getLayout()->getBlock('catalog.product.edit.tab.substitution')
			->setProductsSubstitution($this->getRequest()->getPost('products_substitution', null));
		$this->renderLayout();
	}

	/**
	 * Initialize product before saving
	 */
	protected function _initProductSave() {
		$product = $this->_initProduct();
		$productData = $this->getRequest()->getPost('product');
		if ($productData) {
			$this->_filterStockData($productData['stock_data']);
		}

		/**
		 * Websites
		 */
		if (!isset($productData['website_ids'])) {
			$productData['website_ids'] = array();
		}

		$wasLockedMedia = false;
		if ($product->isLockedAttribute('media')) {
			$product->unlockAttribute('media');
			$wasLockedMedia = true;
		}

		$product->addData($productData);

		if ($wasLockedMedia) {
			$product->lockAttribute('media');
		}

		if (Mage::app()->isSingleStoreMode()) {
			$product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
		}

		/**
		 * Create Permanent Redirect for old URL key
		 */
		if ($product->getId() && isset($productData['url_key_create_redirect'])) {
		// && $product->getOrigData('url_key') != $product->getData('url_key')
			$product->setData('save_rewrites_history', (bool) $productData['url_key_create_redirect']);
		}

		/**
		 * Check "Use Default Value" checkboxes values
		 */
		if ($useDefaults = $this->getRequest()->getPost('use_default')) {
			foreach ($useDefaults as $attributeCode) {
				$product->setData($attributeCode, false);
			}
		}

		/**
		 * Init product links data (related, upsell, crosssel)
		 */
		$links = $this->getRequest()->getPost('links');
		//die(var_dump($links));
		if (isset($links['related']) && !$product->getRelatedReadonly()) {
			$product->setRelatedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']));
		}
		if (isset($links['substitution']) && !$product->getSubtitutionReadonly()) {
			$product->setSubstitutionLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['substitution']));
		}
		if (isset($links['upsell']) && !$product->getUpsellReadonly()) {
			$product->setUpSellLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['upsell']));
		}
		if (isset($links['crosssell']) && !$product->getCrosssellReadonly()) {
			$product->setCrossSellLinkData(Mage::helper('adminhtml/js')
					->decodeGridSerializedInput($links['crosssell']));
		}
		if (isset($links['grouped']) && !$product->getGroupedReadonly()) {
			$product->setGroupedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['grouped']));
		}

		/**
		 * Initialize product categories
		 */
		$categoryIds = $this->getRequest()->getPost('category_ids');
		if (null !== $categoryIds) {
			if (empty($categoryIds)) {
				$categoryIds = array();
			}
			$product->setCategoryIds($categoryIds);
		}

		/**
		 * Initialize data for configurable product
		 */
		if (($data = $this->getRequest()->getPost('configurable_products_data')) && !$product->getConfigurableReadonly()
		) {
			$product->setConfigurableProductsData(Mage::helper('core')->jsonDecode($data));
		}
		if (($data = $this->getRequest()->getPost('configurable_attributes_data')) && !$product->getConfigurableReadonly()
		) {
			$product->setConfigurableAttributesData(Mage::helper('core')->jsonDecode($data));
		}

		$product->setCanSaveConfigurableAttributes(
			(bool) $this->getRequest()->getPost('affect_configurable_product_attributes') && !$product->getConfigurableReadonly()
		);

		/**
		 * Initialize product options
		 */
		if (isset($productData['options']) && !$product->getOptionsReadonly()) {
			$product->setProductOptions($productData['options']);
		}

		$product->setCanSaveCustomOptions(
			(bool) $this->getRequest()->getPost('affect_product_custom_options') && !$product->getOptionsReadonly()
		);

		Mage::dispatchEvent(
			'catalog_product_prepare_save', array('product' => $product, 'request' => $this->getRequest())
		);

		return $product;
	}

	/**
	 * Save product action
	 */
	public function saveAction() {
		$storeId = $this->getRequest()->getParam('store');
		$redirectBack = $this->getRequest()->getParam('back', false);
		$productId = $this->getRequest()->getParam('id');
		$isEdit = (int) ($this->getRequest()->getParam('id') != null);

		$data = $this->getRequest()->getPost();
		if ($data) {
			$this->_filterStockData($data['product']['stock_data']);

			$product = $this->_initProductSave();

			try {
				$product->save();
				$productId = $product->getId();

				if (isset($data['copy_to_stores'])) {
					$this->_copyAttributesBetweenStores($data['copy_to_stores'], $product);
				}

				$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
					->getParentIdsByChild($productId);

				$statusIds = array($productId => $product->getStatus());

				// Check to see if it is a child product
				if (!empty($parentIds)) {
					// Go through each parent id
					foreach ($parentIds as $parentId) {

						// Variable to determine if the parent should stay enabled
						$keep_enabled = false;

						// Get all child ids associated to the parent
						$childIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($parentId);

						if (!empty($childIds)) {

							// Go through each child id to see if they are enabled
							foreach ($childIds[0] as $childId) {

								// Check to see if we have already loaded this product
								if (empty($statusIds[$childId])) {

									// Load the child product
									$child = Mage::getModel('catalog/product')->load($childId);

									// Set the status in order to not load the product again
									$statusIds[$childId] = $child->getStatus();

									// Check the status
									if ($child->getStatus() == 1) {
										$keep_enabled = true;
										break;
									}
								} elseif ($statusIds[$childId] == 1) {
									$keep_enabled = true;
									break;
								}
							}
						}

						$parent = Mage::getModel('catalog/product')->load($parentId);
						if (!$keep_enabled) {
							// Disable the parent product
							$parent->setStatus(2);
						} else {
							$parent->setStatus(1);
						}
						$parent->save();
					}
				}

				$this->_getSession()->addSuccess($this->__('The product has been saved.'));
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage())
					->setProductData($data);
				$redirectBack = true;
			} catch (Exception $e) {
				Mage::logException($e);
				$this->_getSession()->addError($e->getMessage());
				$redirectBack = true;
			}
		}

		if ($redirectBack) {
			$this->_redirect('*/*/edit', array(
				'id' => $productId,
				'_current' => true
			));
		} elseif ($this->getRequest()->getParam('popup')) {
			$this->_redirect('*/*/created', array(
				'_current' => true,
				'id' => $productId,
				'edit' => $isEdit
			));
		} else {
			$this->_redirect('*/*/', array('store' => $storeId));
		}
	}

}
