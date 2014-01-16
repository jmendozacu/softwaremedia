<?php

try {


    $installer = $this;
    $installer->startSetup();
    // setup Improved navigation
    $config = new Mage_Core_Model_Config();
    $config->saveConfig('amshopby/general/categories_type', '2', 'default', 0);    
    $config->saveConfig('amshopby/general/price_type', '3', 'default', 0);
    $config->saveConfig('amshopby/general/price_from_to', '0', 'default', 0);
    
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}	