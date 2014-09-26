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

$content = 'Click the links below to view your keys. Warning: Issuing a key will make this item NON-REFUNDABLE';
//if you want one block for each store view, get the store collection
$stores = Mage::getModel('core/store')->getCollection()->addFieldToFilter('store_id', array('gt'=>0))->getAllIds();
//if you want one general block for all the store viwes, uncomment the line below
//$stores = array(0);
foreach ($stores as $store){
    $block = Mage::getModel('cms/block');
    $block->setTitle('Serial Codes Issue');
    $block->setIdentifier('serial_codes_issue');
    $block->setStores(array($store));
    $block->setIsActive(1);
    $block->setContent($content);
    $block->save();
}

$installer->endSetup();
$installer->installEntities();
