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
 
class Mmsmods_Serialcodes_Block_Serialcodes extends Mage_Core_Block_Template
{
    public function __construct()
    {	
    	parent::__construct();
    	$key = '12312312522';

		//To Encrypt:
		$encrypted = $this->getRequest()->getParam('order');
    		
		//$encrypted = $this->encrypt('8417', $key);
		//$encrypted = base64_encode($encrypted);
		//echo urlencode($encrypted);

		//To Decrypt:
		$orderId = $this->decrypt(urldecode(base64_decode($encrypted)), $key);
		$order = Mage::getModel('sales/order')->load($orderId);
    	
    	if (!$order->getId()) {
	    	$orderId = $this->decrypt(base64_decode($encrypted), $key);
			$order = Mage::getModel('sales/order')->load($orderId);
    	}
    		
		$this->setOrder($order);
    	$this->setUnique(urlencode($this->getRequest()->getParam('order')));
        
    }
    
    public function getAdditional($productId) {
	    $data = array();
	    $product = Mage::getModel('catalog/product')->load($productId);
	    $attributes = $product->getAttributes();
	    foreach ($attributes as $attribute) {
	
	        if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
	            $value = $attribute->getFrontend()->getValue($product);
	
	            if (!$product->hasData($attribute->getAttributeCode())) {
	                $value = Mage::helper('catalog')->__('N/A');
	            } elseif ((string)$value == '') {
	                $value = Mage::helper('catalog')->__('No');
	            } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
	                $value = Mage::app()->getStore()->convertPrice($value, true);
	            }
	
	            if (is_string($value) && strlen($value)) {
	                $data[$attribute->getStoreLabel()] = $value;
	            }
	        }
	    }
	    return $data;
    }
    
    public function encrypt($pure_string, $encryption_key) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
	    return $encrypted_string;
	}
	
	/**
	 * Returns decrypted original string
	 */
	public function decrypt($encrypted_string, $encryption_key) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
	    return $decrypted_string;
	}

}