<?php


class OCM_ChasePaymentTech_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function translateError($errorCode) {
		$errors = array(
			"M1" => "Merchant Override Decline"
		);
	}
	
	public function hasProfile($customer,$cardNum = null) {
		$profiles = Mage::getModel('chasePaymentTech/profiles')->getCollection();
		$profiles->addFieldToFilter('customer_id', $customer);
		
		if ($cardNum)
			$profiles->addFieldToFilter('card_num', $cardNum);
				
		if (count($profiles) >0)
			return $profiles;
			
		return count($profiles);
	}
}