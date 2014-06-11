<?php

require_once('Mage/Checkout/controllers/OnepageController.php');

class OCM_Checkout_Checkout_OnepageController extends Mage_Checkout_OnepageController {

	public function savePaymentAction() {
		if ($this->_expireAjax()) {
			return;
		}
		try {
			if (!$this->getRequest()->isPost()) {
				$this->_ajaxRedirectResponse();
				return;
			}

			$data = $this->getRequest()->getPost('payment', array());

			if ($data['cc_saved']) {
				$profile = Mage::getModel('chasePaymentTech/profiles')->load($data['cc_saved']);
				$data['cc_type'] = $profile->getCardType();
				$data['cc_exp_month'] = $profile->getExpMonth();
				$data['cc_exp_year'] = $profile->getExpYear();
				$data['cc_last4'] = $profile->getCardNum();
			} else {
				$data['cc_last4'] = substr($data['cc_number'], -4);
			}

			$this->getRequest()->setPost('payment', $data);

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
	
		/**
     * Create order action
     */
    public function saveOrderAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', array());
            if ($data['cc_saved']) {
		        $profile = Mage::getModel('chasePaymentTech/profiles')->load($data['cc_saved']);  
		        $data['cc_type'] = $profile->getCardType();
		        $data['cc_exp_month'] = $profile->getExpMonth();
		        $data['cc_exp_year'] = $profile->getExpYear();
		        $data['cc_last4'] = $profile->getCardNum();
	        } else {
		        $data['cc_last4'] = substr($data['cc_number'],-4);
	        }
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
                $pay = $this->getOnepage()->getQuote()->getPayment();
                Mage::log("pay: " . $pay['cc_saved'],null,'temp.log');
                Mage::log("data: " . $data['cc_saved'],null,'temp.log');
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }
            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}
