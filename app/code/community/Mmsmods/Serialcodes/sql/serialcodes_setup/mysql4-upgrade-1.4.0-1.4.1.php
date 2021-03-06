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

$installer = $this;
$installer->startSetup();
	Mage::app()->setUpdateMode(false);
	Mage::app()->setCurrentStore('admin');
	$products = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect(array('serial_code_low_warning', 'serial_code_send_warning'))
		->addAttributeToFilter('serial_code_send_warning', array('like' => '%@%'))
		->load();
	foreach ($products as $product) {
			$product->setSerialCodeLowWarning(1)->save();
	}
$installer->endSetup();