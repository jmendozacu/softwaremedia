<?php

// Set header-welcome to null

try {

    $installer = $this;
    $installer->startSetup();

    $config = new Mage_Core_Model_Config();

    $config->saveConfig('design/header/welcome', '', 'default', 0);
    $installer->endSetup();

} catch (Excpetion $e) {
	Mage::logException($e);
	Mage::log("ERROR IN SETUP " . $e->getMessage());
}