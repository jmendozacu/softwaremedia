<?php

require_once('Mage/Checkout/controllers/CartController.php');

class OCM_Checkout_Checkout_CartController extends Mage_Checkout_CartController {

	public function couponPostAction() {
		/**
		 * No reason continue with empty shopping cart
		 */
		if (!$this->_getCart()->getQuote()->getItemsCount()) {
			$this->_goBack();
			return;
		}

		$couponCode = (string) $this->getRequest()->getParam('coupon_code');
		if ($this->getRequest()->getParam('remove') == 1) {
			$couponCode = '';
		}
		$oldCouponCode = $this->_getQuote()->getCouponCode();

		if (!strlen($couponCode) && !strlen($oldCouponCode)) {
			$this->_goBack();
			return;
		}

		try {
			$this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
			$this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
				->collectTotals()
				->save();

			if (strlen($couponCode)) {
				if ($couponCode == $this->_getQuote()->getCouponCode()) {
					$this->_getSession()->addSuccess(
						$this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
					);
				} else {
					$this->_getSession()->setReturnUrl($this->getRequest()->getParam('return_url'));
					header('location: ' . Mage::getUrl('giftcard/cart/add', array('code' => $couponCode)));
					exit;
				}
			} else {
				$this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
			}
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		} catch (Exception $e) {
			$this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
			Mage::logException($e);
		}

		$this->_goBack();
	}

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

		$this->checkCartMicrosoft();
		
        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
    
	/**
	 * Shopping cart display action
	 */
	public function newAction() {
		$cart = $this->_getCart();
		if ($cart->getQuote()->getItemsCount()) {
			$cart->init();
			$cart->save();

			if (!$this->_getQuote()->validateMinimumAmount()) {
				$minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
					->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

				$warning = Mage::getStoreConfig('sales/minimum_order/description') ? Mage::getStoreConfig('sales/minimum_order/description') : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

				$cart->getCheckoutSession()->addNotice($warning);
			}
		}

		// Compose array of messages to add
		$messages = array();
		foreach ($cart->getQuote()->getMessages() as $message) {
			if ($message) {
				// Escape HTML entities in quote message to prevent XSS
				$message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
				$messages[] = $message;
			}
		}
		$cart->getCheckoutSession()->addUniqueMessages($messages);

		/**
		 * if customer enteres shopping cart we should mark quote
		 * as modified bc he can has checkout page in another window.
		 */
		$this->_getSession()->setCartWasUpdated(true);

		Varien_Profiler::start(__METHOD__ . 'cart_display');
		$this
			->loadLayout()
			->_initLayoutMessages('checkout/session')
			->_initLayoutMessages('catalog/session')
			->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
		$this->renderLayout();
		Varien_Profiler::stop(__METHOD__ . 'cart_display');
	}

	/**
	 * Add product to shopping cart action
	 *
	 * @return Mage_Core_Controller_Varien_Action
	 * @throws Exception
	 */
	public function addAction() {
		$cookie = Mage::getSingleton('core/cookie');
		$val = rand(0, 1);

		if (empty($cookie->get('ab'))) {
			$cookie->set('ab', $val, time() + 86400, '/');
		}

		if ($cookie->get('ab') || (empty($cookie->get('ab')) && $val == 1)) {
			$this->getRequest()->setParam('return_url', Mage::getUrl('checkout/cart'));
		}

		$cart = $this->_getCart();
		$params = $this->getRequest()->getParams();
		try {
			if (isset($params['qty'])) {
				$filter = new Zend_Filter_LocalizedToNormalized(
					array('locale' => Mage::app()->getLocale()->getLocaleCode())
				);
				$params['qty'] = $filter->filter($params['qty']);
			}

			$product = $this->_initProduct();
			$related = $this->getRequest()->getParam('related_product');

			/**
			 * Check product availability
			 */
			if (!$product) {
				$this->_goBack();
				return;
			}

			//SPLIT UP BUNDLE PRODUCTS BEFORE ADDING TO CART
			if (isset($params['bundle_option'])) {
				$optionCollection = $product->getTypeInstance()->getOptionsCollection();
				$selectionCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds());
				$options = $optionCollection->appendSelections($selectionCollection);
				//var_dump($product->getTypeInstance(true)->getOptionsIds($product));
				foreach ($options as $option) {
					//var_dump($option);
					//echo $option->getOptionId();

					if (isset($params['bundle_option'][$option->getOptionId()])) {
						$_selections = $option->getSelections();
						foreach ($_selections as $selection) {
							if ($selection->getSelectionId() == $params['bundle_option'][$option->getOptionId()]) {
								$bundleProd = Mage::getModel('catalog/product')->load($selection->getProductId());
								$cart->addProduct($bundleProd, array('qty' => $params['bundle_option_qty'][$option->getOptionId()]));
								//exit;
							}
						}
					}
				}
			} else {
				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}
			}
			$cart->save();

			$this->_getSession()->setCartWasUpdated(true);

			/**
			 * @todo remove wishlist observer processAddToCart
			 */
			Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
			);
			
			
			
			if (!$this->_getSession()->getNoCartRedirect(true)) {
				if (!$cart->getQuote()->getHasError()) {
					$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
					$this->_getSession()->addSuccess($message);
				}
				$this->_goBack();
			}
		} catch (Mage_Core_Exception $e) {
			if ($this->_getSession()->getUseNotice(true)) {
				$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
			} else {
				$messages = array_unique(explode("\n", $e->getMessage()));
				foreach ($messages as $message) {
					$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
				}
			}

			$url = $this->_getSession()->getRedirectUrl(true);
			if ($url) {
				$this->getResponse()->setRedirect($url);
			} else {
				$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
			}
		} catch (Exception $e) {
			$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
			Mage::logException($e);
			$this->_goBack();
		}
	}
	
	public function checkCartMicrosoft() {
		$msLicense = 0;
		$cart = $this->_getCart();
		if ($cart->getQuote()->getItemsCount()) {
			foreach ($cart->getQuote()->getAllItems() as $item) {
				$product = $item->getProduct();
				$product = Mage::getModel('catalog/product')->load($product->getId());

				if ($product->getData('license_nonlicense_dropdown') == 1210 && strpos($product->getProductUrl(),'microsoft'))
					$msLicense += $item->getQty();
				
			}
		}

		if ($msLicense >0 && $msLicense < 5)
			Mage::getSingleton('core/session')->addError('Microsoft Licensing products require a minimum of 5 licenses purchased. Before checking out please verify your order contains 5 or more Microsoft Licensing products, unless you have a prior license agreement.');
	}

	public function estimatePostAction() {
		$country = (string) $this->getRequest()->getParam('country_id');
		$postcode = (string) $this->getRequest()->getParam('estimate_postcode');
		$city = (string) $this->getRequest()->getParam('estimate_city');
		$regionId = (string) $this->getRequest()->getParam('region_id');
		$region = (string) $this->getRequest()->getParam('region');
		$code = (string) $this->getRequest()->getParam('estimate_method');

		$this->_getQuote()->getShippingAddress()
			->setCountryId($country)
			->setCity($city)
			->setPostcode($postcode)
			->setRegionId($regionId)
			->setRegion($region)
			->setCollectShippingRates(true);

		$this->_getQuote()->save();
		$_SESSION['country'] = $country;
		if (isset($code) && !empty($code)) {
			header('location: ' . Mage::getUrl('checkout/cart/estimateUpdatePost', array('estimate_method' => $code)));
			exit;
		}
		$this->_goBack();
	}

}
