<?php
/*
* ModifyMage Solutions (http://ModifyMage.com)
* Serial Codes - Serial Numbers, Product Codes, PINs, and More
*
* NOTICE OF LICENSE
* This source code is owned by ModifyMage Solutions and distributed for use under the
* provisions, terms, and conditions of our Commercial Software License Agreement which
* is bundled with this package in the file LICENSE.txt. This license is also available
* through the world-wide-web at this URL: http://www.modifymage.com/software-license
* If you do not have a copy of this license and are unable to obtain it through the
* world-wide-web, please email us at license@modifymage.com so we may send you a copy.
*
* @category		Mmsmods
* @package		Mmsmods_Serialcodes
* @author		David Upson
* @copyright	Copyright 2013 by ModifyMage Solutions
* @license		http://www.modifymage.com/software-license
*/

class Mmsmods_Serialcodes_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
			$this->loadLayout();
			$this->renderLayout();
	}
	
	public function redeemAction() {
		$key = '12312312522';
		
		$itemId = $this->getRequest()->getParam('item');
		$unique = $this->getRequest()->getParam('unique');
		$unique = str_replace('87542', '/', $unique);
		
		
		if (!$itemId) {
			echo "Error: No Item";
			return false;
		}
			
		$item = Mage::getModel('sales/order_item')->load($itemId);
		$orderId = $this->decrypt($unique, $key);
		$order = Mage::getModel('sales/order')->load($orderId);
    		
		if ($order->getId() != $item->getOrderId()) {
			echo "Error Validating " . $this->decrypt(base64_decode(urldecode($unique)), $key) . "-" . $item->getOrderId();
			return;
		}
		
		$item->setSerialCodesViewed(1)->save();
		echo nl2br($item->getSerialCodes());
	}
	
    public function encrypt($pure_string, $encryption_key) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	    $dirty = array("+", "/", "=");
		$clean = array("PL174", "SL174", "EQ174");
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
	    $encrypted_string = base64_encode($encrypted_string);
	    return str_replace($dirty, $clean, $encrypted_string);
	}
	
	/**
	 * Returns decrypted original string
	 */
	public function decrypt($encrypted_string, $encryption_key) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	    $dirty = array("+", "/", "=");
		$clean = array("PL174", "SL174", "EQ174");
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $string = base64_decode(str_replace($clean, $dirty, $encrypted_string));
	    $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $string, MCRYPT_MODE_ECB, $iv);
	    return $decrypted_string;
	}
	
}