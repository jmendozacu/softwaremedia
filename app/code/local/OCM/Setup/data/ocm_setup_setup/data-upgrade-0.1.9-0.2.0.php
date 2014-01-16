<?php

// Set header-welcome to null

try {

    $installer = $this;
    $installer->startSetup();

    $installer->updateAttribute('catalog_product', 'brand', 'used_in_product_listing', '1');
    $installer->updateAttribute('catalog_product', 'brand', 'is_filterable', 2);
    
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}