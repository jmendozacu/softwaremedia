<?php

$installer = $this;
$installer->startSetup();

$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
// Force the store to be admin
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::register('isSecureArea', 1);

// Set IsActive to NO for all default category
$root_category = Mage::getModel('catalog/category')->load($rootCategoryId);
$categoryIds = $root_category->getChildren();
foreach (explode(',', $categoryIds) as $sub_catId){
    Mage::getModel('catalog/category')->load($sub_catId)->setIsActive(0)->save();
}

//add new categories
$cat_Software = Mage::getModel('catalog/category');
$cat_Software->setPath('1/'.$rootCategoryId) 
        ->setName('Software')
        ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
        ->setIsActive(1)
        ->setIncludeInMenu(1)
        ->setIsAnchor(1)
        ->save();

$cat_Licensing = Mage::getModel('catalog/category');
$cat_Licensing->setPath('1/'.$rootCategoryId) 
        ->setName('Licensing')
        ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
        ->setIsActive(1)
        ->setIncludeInMenu(1)
        ->setIsAnchor(1)
        ->save();

$cat_Downloads = Mage::getModel('catalog/category');
$cat_Downloads->setPath('1/'.$rootCategoryId) 
        ->setName('Downloads')
        ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
        ->setIsActive(1)
        ->setIncludeInMenu(1)
        ->setIsAnchor(1)
        ->save();

$cat_Topdeals = Mage::getModel('catalog/category');
$cat_Topdeals->setPath('1/'.$rootCategoryId) 
        ->setName('Top Deals')
        ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
        ->setIsActive(1)
        ->setIncludeInMenu(1)
        ->setIsAnchor(1)
        ->save();
// end.add categories

//add sub categories
        // sub categories for Software
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Accounting $ Financial')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Business & Productivity')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('CAD & Home Design')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Clearance')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Design & Productivity')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Development Tools')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Hardware Electronics')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Operating Systems')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
Mage::getModel('catalog/category')->setPath($cat_Software->getPath()) 
	->setName('Reference & Learning')
	->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	->setIsActive(1)
	->setIncludeInMenu(1)
        ->setIsAnchor(1)
    	->save();
// end.add sub categories
$installer->endSetup();