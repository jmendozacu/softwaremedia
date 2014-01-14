<?php

try {
    $installer = $this;
    $installer->startSetup();

    $username = 'softwaremedia';
    $password = 'software1';
    $isDevMode = '0';

    Mage::getConfig()->saveConfig('rewards/platform/dev_mode', $isDevMode);
    Mage::getConfig()->cleanCache();

    Mage::helper('rewards/platform')->connectWithPlatformAccount($username, $password, $isDevMode);

    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}
