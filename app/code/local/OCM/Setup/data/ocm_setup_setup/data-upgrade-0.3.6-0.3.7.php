<?php
        // update Copyright

try {
    $installer = $this;
    $installer->startSetup();
	
    $config = new Mage_Core_Model_Config();
    $config->saveConfig('design/footer/copyright', '&copy; Copyright 2012 - SofwareMedia.com, your one stop discount software shop', 'default', 0); 
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


