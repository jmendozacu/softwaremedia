<?php

class OCM_Peachtree_Model_Referer extends Mage_Core_Model_Abstract {

	public function _construct() {
		parent::_construct();
		$this->_init('peachtree/referer');
	}

	static public function checkForUser($code) {
		if (!$code)
			return false;

		$referers = new Varien_Object(array(
			'PGR' => 'PriceGrabber',
			'FRO' => 'Froogle',
			'CJ' => 'Commission Junction',
			'NXT' => 'Nextag',
			'GGL' => 'Google Adwords',
			'CMJ' => 'Commission Junction',
			'Email' => 'Email Blast',
			//‘Wholesale’ if the wholesale checkbox is checked on the order info page (we’ll need an option for this on the order info page in Magento)
			'Direct' => 'Direct',
			'BNG' => 'Bing Shopping',
			'BUYM' => 'Buy.com',
			'BEST' => 'Best Buy',
			'AdCenter' => 'MSN Adcenter',
			//‘Unknown’ for anything else
			)
			)
		;

		if ($referers->getData($code))
			return false;
		else
			return true;
	}

	static public function getNameByCode($code) {

		if (!$code)
			return 'Unknown';

		$referers = new Varien_Object(array(
			'PGR' => 'PriceGrabber',
			'FRO' => 'Froogle',
			'CJ' => 'Commission Junction',
			'NXT' => 'Nextag',
			'GGL' => 'Google Adwords',
			'Email' => 'Email Blast',
			//‘Wholesale’ if the wholesale checkbox is checked on the order info page (we’ll need an option for this on the order info page in Magento)
			'Direct' => 'Direct',
			'BNG' => 'Bing Shopping',
			'BUYM' => 'Buy.com',
			'BEST' => 'Best Buy',
			'AdCenter' => 'MSN Adcenter',
			//‘Unknown’ for anything else
			)
			)
		;

		if ($referers->getData($code))
			$name = $referers->getData($code);
		else
			$name = $code;

		return $name;
	}

	static public function getReferers() {
		return array(
			'PGR' => 'PriceGrabber',
			'FRO' => 'Froogle',
			'CJ' => 'Commission Junction',
			'NXT' => 'Nextag',
			'GGL' => 'Google Adwords',
			'Email' => 'Email Blast',
			//‘Wholesale’ if the wholesale checkbox is checked on the order info page (we’ll need an option for this on the order info page in Magento)
			'Direct' => 'Direct',
			'BNG' => 'Bing Shopping',
			'BUYM' => 'Buy.com',
			'BEST' => 'Best Buy',
			'AdCenter' => 'MSN Adcenter',
			'AMZ' => 'Amazon',
			//‘Unknown’ for anything else
		);
	}

	public function loadByAttribute($attribute, $value, $additionalAttributes = '*') {
		$collection = $this->getResourceCollection()
			->addFieldToSelect($additionalAttributes)
			->addFieldToFilter($attribute, $value);

		foreach ($collection as $object) {
			return $object;
		}
		return false;
	}

}
