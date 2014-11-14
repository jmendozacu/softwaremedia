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

abstract class Mmsmods_Serialcodes_Block_Sales_Items_Abstract extends Mage_Sales_Block_Items_Abstract
{

	
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
	
	public function getItemHtml(Varien_Object $item)
	{
		$html = parent::getItemHtml($item);
		$product = Mage::getModel('catalog/product')->load($item->getProductId());
		$show = 0;
		if ($item->getProductType() == 'configurable') {
			$itemId = $item->getItemId();
			$children = Mage::getModel('sales/order_item')->getCollection();
			$children->getSelect()->where("main_table.parent_item_id = $itemId");
			foreach($children as $child) {
				$show = $show || Mage::getModel('catalog/product')->load($child->getProductId())->getSerialCodeShowOrder();
			}
		}
		if ($show || $product->getSerialCodeShowOrder()) {
			$sc_model = Mage::getSingleton('serialcodes/serialcodes');
			$name = $this->htmlEscape($item->getName());
			$codetype = $item->getSerialCodeType();
			$codes = explode("\n",$item->getSerialCodes());
			$count = count($codes);
			$local = '<span style="font-weight:normal;">';
			if ($codes[0]) {
				
				$order = Mage::getSingleton('sales/order')->load($item->getOrderId());
				$key = '12312312522';
				$encrypted = $this->encrypt($order->getId(), $key);
				$encoded = str_replace('%2F','87542', $encrypted);
				$codeids = array_pad(explode(',',$item->getSerialCodeIds()),$count,'');
					for ($i=0; $i<$count; $i++) {
						$codes[$i] = "<a href=\"" .Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'serialcodes/index/index/order/' . $encoded . "\">Click here to view your code" . $cCount . "</a>";
						if ($sc_model->hidePendingCodes($order, $item, $product, $codeids[$i], $i)) {
							$codes[$i] = Mage::helper('serialcodes')->__('Issued when payment received.');
						}
						$local .= '</br>'.$codetype.': '.$codes[$i];
					}
			}
			$local .= '</span>';
			if (strpos($html,$name)) {
				$start = strpos($html,$name) + strlen($name);
			} else {
				$start = strpos($html,'</h3>') + 5;
			}
			$test = trim(strip_tags($local));
			if ($test && $test <> ':') {$html = substr_replace($html,$local,$start,0);}
		}
		return $html;
	}
}