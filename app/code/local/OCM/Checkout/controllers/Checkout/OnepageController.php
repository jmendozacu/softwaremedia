<?php

require_once('Mage/Checkout/controllers/OnepageController.php');

class OCM_Checkout_Checkout_OnepageController extends Mage_Checkout_OnepageController {

	public function savePaymentAction() {
		Mage::log('PAYMENT',NULL,'cc.log');
		//die();
		if ($this->_expireAjax()) {
			return;
		}
		try {
			if (!$this->getRequest()->isPost()) {
				$this->_ajaxRedirectResponse();
				return;
			}

			$data = $this->getRequest()->getPost('payment', array());

			Mage::log('SAVED: ' . $data['cc_saved'],NULL,'cc.log');
			
			if ($data['cc_saved']) {
				$profile = Mage::getModel('chasePaymentTech/profiles')->load($data['cc_saved']);
				$data['cc_type'] = $profile->getCardType();
				$data['cc_exp_month'] = $profile->getExpMonth();
				$data['cc_exp_year'] = $profile->getExpYear();
				$data['cc_last4'] = $profile->getCardNum();
				
				Mage::log('TYPE 1: ' . $data['cc_type'],NULL,'cc.log');
				Mage::log('CLASS: ' . get_class($this->getOnepage()),NULL,'cc.log');
				Mage::log('PROFILE: ' . $profile->getId(),NULL,'cc.log');
				Mage::log('MONTH: ' . $data['cc_exp_month'],NULL,'cc.log');
				Mage::log('YEAR: ' . $data['cc_exp_year'],NULL,'cc.log');
			
			} else {
				$data['cc_last4'] = substr($data['cc_number'], -4);
			}

			$this->getRequest()->setPost('payment', $data);
			$this->getOnepage()->getQuote()->getPayment()->addData($data);
			
			$result = $this->getOnepage()->savePayment($data);

			// get section and redirect data
			$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
			
			
			if (empty($result['error']) && !$redirectUrl) {
				$this->loadLayout('checkout_onepage_review');
				$result['goto_section'] = 'review';
				$result['update_section'] = array(
					'name' => 'review',
					'html' => $this->_getReviewHtml()
				);
			}
			if ($redirectUrl) {
				$result['redirect'] = $redirectUrl;
			}
		} catch (Mage_Payment_Exception $e) {
			if ($e->getFields()) {
				$result['fields'] = $e->getFields();
			}
			$result['error'] = $e->getMessage();
		} catch (Mage_Core_Exception $e) {
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			Mage::logException($e);
			$result['error'] = $this->__('Unable to set Payment Method.');
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	/**
	 * Save checkout billing address
	 */
	public function saveBillingAction() {

		if ($this->_expireAjax()) {
			return;
		}
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost('billing', array());
			$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

			if (isset($data['email'])) {
				$data['email'] = trim($data['email']);
			}



			if (isset($data['email']) && !Zend_Validate::is($data['email'], 'EmailAddress')) {
				$result = array(
					'error' => -1,
					'message' => Mage::helper('checkout')->__('Invalid email address "%s"', $data['email'])
				);
			} else {
				$result = $this->getOnepage()->saveBilling($data, $customerAddressId);


				if ($this->getRequest()->getPost('billing_address_id')) {
					$add = Mage::getModel('customer/address')->load($this->getRequest()->getPost('billing_address_id'));
					$data['country_id'] = $add->getCountryId();
				}
			}

			//Hijack Canadian Orders
			//Unhijack canadaian orders jk lol
			/*
			  if ($data['country_id'] == 'CA') {
			  Mage::getSingleton('core/session')->setCanadaInfo($data);
			  $result['redirect'] = '/qquoteadv/index/switch2CAQquote/';
			  } else
			 */

			if (!isset($result['error'])) {
				if ($this->getOnepage()->getQuote()->isVirtual()) {
					$result['goto_section'] = 'payment';
					$result['update_section'] = array(
						'name' => 'payment-method',
						'html' => $this->_getPaymentMethodsHtml()
					);
				} elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
					$result['goto_section'] = 'shipping_method';
					$result['update_section'] = array(
						'name' => 'shipping-method',
						'html' => $this->_getShippingMethodsHtml()
					);

					$result['allow_sections'] = array('shipping');
					$result['duplicateBillingInfo'] = 'true';
				} else {
					$result['goto_section'] = 'shipping';
				}
			}

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
    

}
