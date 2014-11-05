<?php

// @codingStandardsIgnoreStart
/**
 * StoreFront Consulting Kount Magento Extension
 *
 * PHP version 5
 *
 * @category  SFC
 * @package   SFC_Kount
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 *
 */
// @codingStandardsIgnoreEnd

class SFC_Kount_Helper_RisRequest extends Mage_Core_Helper_Abstract {

	/**
	 * Ris Response codes
	 */
	const RIS_RESP_DECLINE = 'D';
	const RIS_RESP_REVIEW = 'R';
	const RIS_RESP_MANGREV = 'E';
	const RIS_RESP_APPRV = 'A';

	/**
	 * Pay types
	 */
	const RIS_PAYTYPE_CHASE = 'chasePaymentTech';
	const RIS_PAYTYPE_AUTH = 'authorizenet';
	const RIS_PAYTYPE_AUTHDP = 'authorizenet_directpost';
	const RIS_PAYTYPE_AUTHSFCCIM = 'authnettoken';
	const RIS_PAYTYPE_PPEXPRESS = 'paypal_express';
	const RIS_PAYTYPE_PPDIRECT = 'paypal_direct';
	const RIS_PAYTYPE_PPSTANDARD = 'paypal_standard';
	const RIS_PAYTYPE_CYBERSOURCE = 'cybersource_soap';
	const RIS_PAYTYPE_CYBERSOURCE_SFC = 'sfc_cybersource';
	const RIS_PAYTYPE_PAYFLOWPRO = 'verisign';

	/**
	 * Messages
	 */
	const RIS_MESSAGE_REJECTED = 'Order did not go through since payment was rejected. Please use a different payment method.';
	const RIS_MESSAGE_ORDERREVIEW = 'Order in review from Kount.';
	const RIS_MESSAGE_ORDERDECLINE = 'Order declined from Kount.';

	/**
	 * Validate payment method
	 * @param Mage_Paygate_Mode
	 * @return boolean
	 */
	public function validatePaymentMethod($oPayment) {
		// Validate
		if (empty($oPayment)) {
			return false;
		}
		$oMethod = $oPayment->getMethodInstance();
		if (empty($oMethod)) {
			return false;
		}

		// Log
		Mage::log("Checking Payment Method: {$oMethod->getCode()}", Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

		// Valid?
		switch ($oMethod->getCode()) {
			case self::RIS_PAYTYPE_PPEXPRESS:
				Mage::log('Paypal Enabled', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
				return true;
				break;
			case self::RIS_PAYTYPE_CHASE:
			case self::RIS_PAYTYPE_AUTH:
			case self::RIS_PAYTYPE_AUTHDP:
			case self::RIS_PAYTYPE_AUTHSFCCIM:
			case self::RIS_PAYTYPE_PPDIRECT:
			case self::RIS_PAYTYPE_PPSTANDARD:
			case self::RIS_PAYTYPE_CYBERSOURCE:
			case self::RIS_PAYTYPE_CYBERSOURCE_SFC:
			case self::RIS_PAYTYPE_PAYFLOWPRO:

				// -- Log
				Mage::log('Payment Method Supported', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

				// -- Enabled?
				$sConfig = Mage::getStoreConfig("kount/paymentmethods/{$oMethod->getCode()}");
				if (!empty($sConfig) && intval($sConfig) == 1) {

					// -- -- Log
					Mage::log('Payment Method Enabled', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

					return true;
				}

				// -- Log
				Mage::log('Payment Method Not Enabled', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

				return false;

				break;

			default:
				break;
		}

		// Log
		Mage::log('Payment Method Not Supported', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

		return false;
	}

	/**
	 * Send Ris inquiry
	 * @param Mage_Paygate_Mode
	 * @param Mage_Sales_Model_Order
	 * @return string or null
	 * @throws Kount_Ris_Exception Upon a bad repsonse
	 */
	public function sendRisInquiry($oPayment, Mage_Sales_Model_Order $oOrder) {
		// Log
		Mage::log('==== Sending RIS Inquiry ====', Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

		// Helper
		$oPathHelper = new SFC_Kount_Helper_Paths();

		// Log
		Mage::log('Kount extension version: ' . Mage::helper('kount')->getExtensionVersion(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Store Id: ' . Mage::app()->getStore()->getId(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Merchant Id: ' . Mage::getStoreConfig('kount/account/merchantnum'), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Website Id: ' . Mage::getStoreConfig('kount/account/website'), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Cert Path: ' . $oPathHelper->getCertFilePath(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Key Path: ' . $oPathHelper->getKeyFilePath(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

		try {
			// Pre-set ris request failed flag
			Mage::getSingleton('core/session')->setRisRequestFailed(false);

			// Order info from payment
			if (empty($oPayment)) {
				Mage::throwException('Payment information is empty.');
			}
			if (empty($oOrder)) {
				Mage::throwException('Order information is empty.');
			}
			$oBilling = $oOrder->getBillingAddress();
			$oShipping = $oOrder->getShippingAddress();
			$oMethod = $oPayment->getMethodInstance();
			if (empty($oBilling)) {
				Mage::throwException('Billing information is empty.');
			}
			if (empty($oShipping)) {
				$oQuote = Mage::getModel('sales/quote')->load($oOrder->getQuoteId());
				if ($oQuote->isVirtual() == false) {
					Mage::throwException('Shipping information is empty.');
				}
			}
			if (empty($oMethod)) {
				Mage::throwException('Payment Method information is empty.');
			}

			// Log prefix
			$sLogPrefix = "Kount Ris Inquiry for Order Id: '{$oOrder->getIncrementId()}' ";

			// Ris inquiry request
			$oInquiry = new Kount_Ris_Request_Inquiry();

			// Add version info
			$this->addVersionInfoToRequest($oInquiry);

			// Session Id
			$oInquiry->setSessionId(Mage::getSingleton('kount/session')->getKountSessionId());

			// Ip Address
			if (Mage::getSingleton('core/session')->getIsKountAdmin() == '1') {
				$oInquiry->setIpAddress('10.0.0.1');
			} else {
				$ipAddress = ($oOrder->getXForwardedFor() ? $oOrder->getXForwardedFor() : $oOrder->getRemoteIp());
				if (Mage::helper('kount')->checkIPAddress($ipAddress)) {
					$oInquiry->setIpAddress('10.0.0.1');
				} else {
					$oInquiry->setIpAddress($ipAddress);
				}
			}

			// Website Id
			$oInquiry->setWebsite(Mage::getStoreConfig('kount/account/website'));

			// Billing Info
			if (!empty($oBilling)) {
				$oInquiry->setBillingAddress(
					$oBilling->getStreet(1), ($oBilling->getStreet(2) ? $oBilling->getStreet(2) : ''), $oBilling->getCity(), $oBilling->getRegion(), $oBilling->getPostcode(), $oBilling->getCountry());
				$oInquiry->setBillingPhoneNumber($oBilling->getTelephone());
			}

			// Shipping info
			if (!empty($oShipping)) {
				$oInquiry->setShippingName($oShipping->getFirstname() . ' ' . $oShipping->getLastname());
				$oInquiry->setShippingAddress(
					$oShipping->getStreet(1), ($oShipping->getStreet(2) ? $oShipping->getStreet(2) : ''), $oShipping->getCity(), $oShipping->getRegion(), $oShipping->getPostcode(), $oShipping->getCountry());
				$oInquiry->setShippingPhoneNumber($oShipping->getTelephone());
				;
				$oInquiry->setShippingEmail($oOrder->getCustomerEmail());
			}

			// Payment info
			$sPaymentMethod = $oMethod->getCode();
			Mage::log("Payment Method: {$sPaymentMethod}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			// -- Authorize.Net, Authorize.Net CIM, PayPal Direct, Cybersource, Payflow Pro
			// All of these methods let us get the card number from the 'cc_number' field
			if ($sPaymentMethod == self::RIS_PAYTYPE_AUTH || $sPaymentMethod == self::RIS_PAYTYPE_PPDIRECT || $sPaymentMethod == self::RIS_PAYTYPE_CYBERSOURCE || $sPaymentMethod == self::RIS_PAYTYPE_PAYFLOWPRO
			) {
				$sCardNum = $oPayment->getData('cc_number');
				if (empty($sCardNum)) {
					Mage::throwException($sLogPrefix . 'Invalid Credit Card Number for payment.');
				}
				$oInquiry->setCardPayment($sCardNum);
			}
			// -- Authorize.Net CIM - SFC Module & SFC CyberSource Module
			// We need to handle saved credit cards differently, because we don't have full access to CC number
			else {
				if ($sPaymentMethod == self::RIS_PAYTYPE_CHASE || $sPaymentMethod == self::RIS_PAYTYPE_AUTHSFCCIM || $sPaymentMethod == self::RIS_PAYTYPE_CYBERSOURCE_SFC) {
					$sCardNum = $oPayment->getData('cc_number');
					if (empty($sCardNum)) {
						$sCardNum = $oPayment->getData('cc_last4');
					}
					if (empty($sCardNum)) {
						Mage::throwException($sLogPrefix . 'Invalid Credit Card Number for payment.');
					}
					// Check is we have a masked CC number or full
					if (substr($sCardNum, 0, 4) === 'XXXX' || strlen($sCardNum) == 4) {
						// Masked card number
						$oInquiry->setGiftCardPayment($sCardNum);
					} else {
						// Full Card Number
						$oInquiry->setCardPayment($sCardNum);
					}
				}
				// -- Authorize.Net Direct post
				// Direct Post doesn't let us see the card number
				else {
					if ($sPaymentMethod == self::RIS_PAYTYPE_AUTHDP) {
						$oInquiry->setNoPayment();
					}
					// -- Paypal express
					// Set PayPal payment type / info on inquiry
					else {
						if ($sPaymentMethod == self::RIS_PAYTYPE_PPEXPRESS) {
							$sPayId = $oPayment->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID);
							if (empty($sPayId)) {
								Mage::throwException($sLogPrefix . 'Invalid Payer Id for paypal payment.');
							}
							$oInquiry->setPayPalPayment($sPayId);
						}
						// -- Paypal standard
						// PayPal std doesn't let us get any of the payment details
						else {
							if ($sPaymentMethod == self::RIS_PAYTYPE_PPSTANDARD) {
								$oInquiry->setNoPayment();
							} else {
								Mage::throwException($sLogPrefix . 'Invalid payment method.');
							}
						}
					}
				}
			}

			// Order info
			$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
			Mage::log("Base Currency: {$baseCurrencyCode}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			if ($baseCurrencyCode == 'USD') {
				$baseGrandTotal = round($oOrder->getBaseGrandTotal() * 100);
				$oInquiry->setTotal($baseGrandTotal);
				Mage::log("USD Base Grand Total: {$baseGrandTotal}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			} else {
				$convertedGrandTotal = (round(Mage::helper('directory')->currencyConvert($oOrder->getBaseGrandTotal(), $baseCurrencyCode, 'USD') * 100));
				$oInquiry->setTotal($convertedGrandTotal);
				Mage::log("Grand Total Converted to USD: {$convertedGrandTotal}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			}
			$oInquiry->setCurrency('USD');
			$oInquiry->setOrderNumber($oOrder->getIncrementId());

			// Map other order fields to UDF fields
			// Map shipping method and carrier
			$shippingFields = explode('_', $oOrder->getShippingMethod());

			if (isset($shippingFields[0])) {
				$oInquiry->setUserDefinedField('CARRIER', $shippingFields[0]);
			}
			if (isset($shippingFields[1])) {
				$oInquiry->setUserDefinedField('METHOD', $shippingFields[1]);
			}
			// Map coupon code
			if (strlen($oOrder->getCouponCode())) {
				$oInquiry->setUserDefinedField('COUPON_CODE', $oOrder->getCouponCode());
			}

			// Customer info
			$sName = ($oOrder->getCustomerName() ? $oOrder->getCustomerName() :
					($oShipping->getFirstname() . ' ' . $oShipping->getLastname()));
			$oInquiry->setName($sName);
			$oInquiry->setEmail($oOrder->getCustomerEmail());
			$oInquiry->setUserAgent(Mage::helper('core/http')->getHttpUserAgent());
			$oInquiry->setUnique(Mage::getSingleton('customer/session')->getCustomer()->getEntityId());
			$oInquiry->setEpoch(time());

			$hasLicensing = 0;
			$isSuspicious = 0;
			// Cart
			$aCart = array();
			foreach ($oOrder->getAllVisibleItems() as $oItem) {
				$prod = Mage::getModel('catalog/product')->load($oItem->getProductId(), array('license_nonlicense_dropdown'));
				if ($prod->getResource()->getAttribute('license_nonlicense_dropdown')) {
					$attributeValue = $prod->getResource()->getAttribute('license_nonlicense_dropdown')->getFrontend()->getValue($prod);
					if ($attributeValue == 'License Product')
						$hasLicensing = 1;
				}
					
				$aCart[] = new Kount_Ris_Data_CartItem(
					$oItem->getSku(), $oItem->getName(), ($oItem->getDescription() ? $oItem->getDescription() : ''), round($oItem->getQtyOrdered()), ($baseCurrencyCode == 'USD' ? round($oItem->getBasePrice() * 100) :
						round(Mage::helper('directory')->currencyConvert($oItem->getBasePrice(), $baseCurrencyCode, 'USD') * 100)));
			}
			$oInquiry->setCart($aCart);

			// Additional info
			$oInquiry->setMack('N');
			$oInquiry->setAuth('A');
			Mage::log('Setting Licensing: ' . $hasLicensing,NULL,'li.log');

			$oInquiry->setUserDefinedField('LICENSING', $hasLicensing);
			$oInquiry->setUserDefinedField('SUSPICIOUS', $isSuspicious);
			
			$numOrders = 0;
			$numRefundedOrders = 0;
			
			if ($oOrder->getCustomerId()) {
				$customerOrders = Mage::getResourceModel('sales/order_collection')
                        ->addFieldToFilter('customer_id', $oOrder->getCustomerId())
                        ->addFieldToFilter('state', 'complete');   
                        
                $numOrders = count($customerOrders);
			}
			
			$oInquiry->setUserDefinedField('ORDERS', $numOrders);
			//$oInquiry->setUserDefinedField('REFUNDED', $numRefundedOrders);
			
			// Get response
			$oResponse = $oInquiry->getResponse();
			if (!$oResponse) {
				Mage::throwException($sLogPrefix . 'Invalid response from Ris inquiry.');
			}

			// Log errors
			$aErrors = $oResponse->getErrors();
			foreach ($aErrors as $sError) {
				Mage::log($sLogPrefix . $sError, Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			}

			// Log warnings
			$aWarnings = $oResponse->getWarnings();
			foreach ($aWarnings as $sWarning) {
				Mage::log($sLogPrefix . $sWarning, Zend_Log::WARN, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			}

			// Log results
			$sResAuto = null;
			$sResScore = null;
			if (!$oResponse->hasErrors()) {

				// -- Get response
				$sResAuto = $oResponse->getAuto();
				$sResScore = $oResponse->getScore();

				// -- Log
				Mage::log($sLogPrefix . "Ris Response: {$sResAuto}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
				Mage::log($sLogPrefix . "Ris Score: {$sResScore}", Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			}

			$sResRules = '';
			if ($oResponse->getNumberRulesTriggered() > 0) {
				foreach ($oResponse->getRulesTriggered() as $curRuleId => $curRuleDesc) {
					$sResRules .= 'Rule ID ' . $curRuleId . ': ' . $curRuleDesc . "\n";
				}
			} else {
				$sResRules = 'No Rules';
			}

			// Save session variables
			Mage::getSingleton('core/session')
				->setRisTransId($oResponse->getTransactionId())
				->setRisResponse($oResponse->getAuto())
				->setRisScore($oResponse->getScore())
				->setRisRules($sResRules)
				->setRisDescription($oResponse->getReason())
				->setRisAdditionalInfo(array(
					'TRAN' => $oResponse->getTransactionId(),
					'GEOX' => $oResponse->getGeox(),
					'DVCC' => $oResponse->getCountry(),
					'KAPT' => $oResponse->getKaptcha(),
					'CARDS' => $oResponse->getCards(),
					'EMAILS' => $oResponse->getEmails(),
					'DEVICES' => $oResponse->getDevices(),
				))
			;

			return $sResAuto;
		} catch (Kount_Ris_Exception $kre) {

			// -- Log
			Mage::log($kre->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

			// Save flag indicating RIS request failed
			Mage::getSingleton('core/session')->setRisRequestFailed(true);

			// Rethrow exception
			throw $kre;
		} catch (Exception $e) {

			// -- Log
			Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		}

		return null;
	}

	/**
	 * Send Ris update
	 * @param boolean Authorized or not
	 * @return string or null
	 */
	public function sendRisUpdate($bAuthorized) {
		// Log
		Mage::log('==== Sending RIS Update ====', Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

		// Helper
		$oPathHelper = new SFC_Kount_Helper_Paths();

		// Log
		Mage::log('Kount extension version: ' . Mage::helper('kount')->getExtensionVersion(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Store Id: ' . Mage::app()->getStore()->getId(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Merchant Id: ' . Mage::getStoreConfig('kount/account/merchantnum'), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Website Id: ' . Mage::getStoreConfig('kount/account/website'), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Cert Path: ' . $oPathHelper->getCertFilePath(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		Mage::log('Key Path: ' . $oPathHelper->getKeyFilePath(), Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

		try {

			// Check for flag indicating RIS request failed
			$risRequestFailed = Mage::getSingleton('core/session')->getRisRequestFailed();
			if ($risRequestFailed) {
				// Initial Ris request failed, skipping Ris update
				Mage::log('Initial Ris request failed, skipping Ris update', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

				return true;
			}

			// Order info from payment
			$sTransactionId = Mage::getSingleton('core/session')->getRisTransId();
			if (empty($sTransactionId)) {
				Mage::throwException('Transaction Id is empty, perform inquiry first.');
			}
			Mage::getSingleton('core/session')->unsRisTransId();

			// Log prefix
			$sLogPrefix = 'Kount Ris Update for Transaction Id: "' . $sTransactionId . '": ';

			// Ris update request
			$oUpdate = new Kount_Ris_Request_Update();

			// Session Id
			$oUpdate->setSessionId(Mage::getSingleton('kount/session')->getKountSessionId());

			// Transaction Id
			$oUpdate->setTransactionId($sTransactionId);

			// Transaction info
			$oUpdate->setMack(($bAuthorized ? 'Y' : 'N'));
			$oUpdate->setAuth(($bAuthorized ? 'A' : 'D'));

			// Get response
			$oResponse = $oUpdate->getResponse();
			if (!$oResponse) {
				Mage::throwException($sLogPrefix . 'Invalid response from Ris inquiry.');
			}

			// Log errors
			$aErrors = $oResponse->getErrors();
			foreach ($aErrors as $sError) {
				Mage::log($sLogPrefix . $sError, Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			}

			// Log warnings
			$aWarnings = $oResponse->getWarnings();
			foreach ($aWarnings as $sWarning) {
				Mage::log($sLogPrefix . $sWarning, Zend_Log::WARN, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
			}

			// Log results
			if ($oResponse->getErrorCode() !== null) {

				return false;
			}

			return true;
		} catch (Exception $e) {

			// -- Log
			Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
		}

		return null;
	}

	protected function addVersionInfoToRequest(Kount_Ris_Request $request) {
		// Get helper
		/** @var SFC_Kount_Helper_Data $helper */
		$helper = Mage::helper('kount');

		// Get Version info
		$aVersion = Mage::getVersionInfo();
		// Get Edition
		if (method_exists('Mage', 'getEdition')) {
			$magentoEdition = Mage::getEdition();
			// Build platform string from Magento version info
			switch ($magentoEdition) {
				case Mage::EDITION_COMMUNITY:
					$platformString = 'CE';
					break;
				case Mage::EDITION_ENTERPRISE:
					$platformString = 'EE';
					break;
				case Mage::EDITION_PROFESSIONAL:
					$platformString = 'PE';
					break;
				default:
					$platformString = '??';
					break;
			}
		} else {
			$platformString = '??';
		}

		// Add version to platform string
		$platformString .= $aVersion['major'] . '.' . $aVersion['minor'];

		// Add platform string to RIS request
		$request->setUserDefinedField('PLATFORM', $platformString);
		// Add extension version info to request
		$request->setUserDefinedField('EXT', $helper->getExtensionVersion());
	}

}
