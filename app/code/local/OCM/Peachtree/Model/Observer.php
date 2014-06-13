<?php

class OCM_Peachtree_Model_Observer {

	const COOKIE_NAME = 'softwaremedia_ovchn';
	const COOKIE_PERIOD = 172800;

	public function setRefererCookie($observer) {
		$request = Mage::app()->getRequest();
		$request_value = $request->getParam('ovchn');
		$cookie_value = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
		$affiliate_codes = explode(',', Mage::getStoreConfig('catalog/cpc_price/affiliate_codes'));

		if ($request_value && in_array($request_value, $affiliate_codes) && $request_value != $cookie_value) {
			Mage::getModel('core/cookie')->set(self::COOKIE_NAME, $request_value, self::COOKIE_PERIOD);
			Mage::register(self::COOKIE_NAME, true);
		}

		$cookie_value = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
	}

	public function saveRefererId($observer) {

		$cookie_value = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
		$order_id = $observer->getOrder()->getId();
		$amazon_order = Mage::helper('M2ePro/Component_Amazon')
			->getCollection('Order')
			->addFieldToFilter('order_id', $order_id)
			->getItems();
		$buy_order = Mage::helper('M2ePro/Component_Amazon')
			->getCollection('Order')
			->addFieldToFilter('order_id', $order_id)
			->getItems();

		if (!$cookie_value) {
			if (count($amazon_order) > 0) {
				$cookie_value = 'AMZ';
			} else if (count($buy_order) > 0) {
				$cookie_value = 'BUYM';
			} else {
				$cookie_value = 'Direct';
			}
		}

		$data = array(
			'order_id' => $order_id,
			'referer_id' => $cookie_value
		);


		Mage::getSingleton('core/session', array('name' => 'adminhtml'));
		$session = Mage::getSingleton('admin/session');
		if ($session->isLoggedIn()) {
			$admin = $session->getUser();
			if ($admin->getId()) {//check if the admin is logged in
				$data['referer_id'] = $admin->getUsername(); //add the class to the body.
			}
		}


		try {
			$m = Mage::getModel('peachtree/referer')->setData($data)->save();
		} catch (Exception $e) {
			Mage::log('peachtree_referer failed to save with Exception: ' . $e->getMessage());
		}
	}

}
