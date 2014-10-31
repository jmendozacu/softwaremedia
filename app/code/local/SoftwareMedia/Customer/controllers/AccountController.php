<?php

require_once('Mage/Customer/controllers/AccountController.php');

class SoftwareMedia_Customer_AccountController extends Mage_Customer_AccountController {

	/**
	 * Success Registration
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Mage_Customer_AccountController
	 */
	protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer) {
		$session = $this->_getSession();
		if ($customer->isConfirmationRequired()) {
			/** @var $app Mage_Core_Model_App */
			$app = $this->_getApp();
			/** @var $store  Mage_Core_Model_Store */
			$store = $app->getStore();
			$customer->sendNewAccountEmail(
				'confirmation', $session->getBeforeAuthUrl(), $store->getId()
			);
			$customerHelper = $this->_getHelper('customer');
			$session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
			$url = $this->_getUrl('*/*/index', array('_secure' => true));
		} else {
			$session->setCustomerAsLoggedIn($customer);
			$session->renewSession();
			$url = $this->_welcomeCustomer($customer);
		}
		if ($url == 'new')
			$this->_redirect($url);
		else
			$this->_redirectSuccess($url);
		return $this;
	}

	/**
	 * Add welcome message and send new account email.
	 * Returns success URL
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param bool $isJustConfirmed
	 * @return string
	 */
	protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false) {
		$this->_getSession()->addSuccess(
			$this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
		);
		if ($this->_isVatValidationEnabled()) {
			// Show corresponding VAT message to customer
			$configAddressType = $this->_getHelper('customer/address')->getTaxCalculationAddressType();
			$userPrompt = '';
			switch ($configAddressType) {
				case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
					$userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', $this->_getUrl('customer/address/edit'));
					break;
				default:
					$userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', $this->_getUrl('customer/address/edit'));
			}
			$this->_getSession()->addSuccess($userPrompt);
		}

		$customer->sendNewAccountEmail(
			$isJustConfirmed ? 'confirmed' : 'registered', '', Mage::app()->getStore()->getId()
		);

		$successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
		if ($this->_getSession()->getBeforeAuthUrl()) {
			$successUrl = $this->_getSession()->getBeforeAuthUrl(true);
		}
		$postData = Mage::app()->getRequest()->getPost();

		if ($postData['success_url'] == 'brochure') {
			$this->processNewPoints($customer);
			$successUrl = $postData['success_url'];
		}
		return $successUrl;
	}

	/**
	 * Create customer account action
	 */
	public function createPostAction() {
		
		/** @var $session Mage_Customer_Model_Session */
		$session = $this->_getSession();
		if ($session->isLoggedIn()) {
			$this->_redirect('*/*/');
			return;
		}
		$session->setEscapeMessages(true); // prevent XSS injection in user input
		if (!$this->getRequest()->isPost()) {
			$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
			$this->_redirectError($errUrl);
			return;
		}

		$postData = Mage::app()->getRequest()->getPost();

		$customer = $this->_getCustomer();

		if (isset($postData['q4_2014_brochure'])) {
			$customer->setData('q4_2014_brochure', 1);
		}

		try {
			$errors = $this->_getCustomerErrors($customer);

			if (empty($errors)) {
				$customer->save();
				$this->_dispatchRegisterSuccess($customer);
				$this->_successProcessRegistration($customer);
				if (isset($postData['q4_2014_brochure'])) {
					$this->_redirect('brochure');
				}
				return;
			} else {
				$this->_addSessionError($errors);
			}
		} catch (Mage_Core_Exception $e) {
			$session->setCustomerFormData($this->getRequest()->getPost());
			if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
				$url = $this->_getUrl('customer/account/forgotpassword');
				$message = $this->__('Error: There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
				//$session->setEscapeMessages(false);
				
			} else {
				$message = $e->getMessage();
			}
			//$session->addError($message);
			if (isset($postData['q4_2014_brochure'])) {
					$errUrl = $this->_getUrl('brochure');
					$this->_redirect($errUrl);
				}
		} catch (Exception $e) {
			$session->setCustomerFormData($this->getRequest()->getPost())
				->addException($e, $this->__('Cannot save the customer.'));
		}

		$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
		if (isset($postData['q4_2014_brochure'])) {
			$errUrl = $this->_getUrl('brochure');
			$this->_redirect($errUrl);
		} else {
			$this->_redirect($errUrl);
		}
	}

	/**
	 * Add points and customer group for brochure sign up page
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Mage_Customer_AccountController
	 */
	protected function processNewPoints(Mage_Customer_Model_Customer $customer) {
		/*
		
		$transfer = Mage::getModel('rewards/transfer')->setReasonId(TBT_Rewards_Model_Transfer_Reason::REASON_ADMIN_ADJUSTMENT)->setComments('New Brochure Landing Page Points')->setCurrencyId(1)->setQuantity(1000);
		$transfer->setId(null)->setCustomerId($customer->getId());

		// get the default starting status - usually Pending
		if (!$transfer->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED)) {
			throw new Exception($this->__("Could not approve points."));
		}
		//$this->_getSession()->addSuccess('You receive a bonus 1000 points!');
		$transfer->save();
		*/
	}

	/**
	 * Define target URL and redirect customer after logging in
	 */
	protected function _loginPostRedirect() {
		$session = $this->_getSession();

		if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
			// Set default URL to redirect customer to
			$session->setBeforeAuthUrl($this->_getHelper('customer')->getAccountUrl());
			// Redirect customer to the last page visited after logging in
			if ($session->isLoggedIn()) {
				if (!Mage::getStoreConfigFlag(
						Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
					)) {
					$referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
					if ($referer) {
						// Rebuild referer URL to handle the case when SID was changed
						$referer = $this->_getModel('core/url')
							->getRebuiltUrl($this->_getHelper('core')->urlDecode($referer));
						if ($this->_isUrlInternal($referer)) {
							$session->setBeforeAuthUrl($referer);
						}
					}
				} else if ($session->getAfterAuthUrl()) {
					$session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
				}
			} else {
				$session->setBeforeAuthUrl($this->_getHelper('customer')->getLoginUrl());
			}
		} else if ($session->getBeforeAuthUrl() == $this->_getHelper('customer')->getLogoutUrl()) {
			$session->setBeforeAuthUrl($this->_getHelper('customer')->getDashboardUrl());
		} else if (strpos($_SESSION['core']['last_url'], Mage::getUrl('checkout/cart')) !== false) {
			if (!$session->getAfterAuthUrl()) {
				$session->setAfterAuthUrl($session->getBeforeAuthUrl());
			}
			if ($session->isLoggedIn()) {
				if (Mage::getUrl('checkout/cart/new') == false) {
					$session->setBeforeAuthUrl(Mage::getUrl('checkout/cart'));
				} else {
					$session->setBeforeAuthUrl(Mage::getUrl('checkout/cart/new'));
				}
			}
		} else {
			if (!$session->getAfterAuthUrl()) {
				$session->setAfterAuthUrl($session->getBeforeAuthUrl());
			}
			if ($session->isLoggedIn()) {
				$session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
			}
		}

		$this->_redirectUrl($session->getBeforeAuthUrl(true));
	}

}
