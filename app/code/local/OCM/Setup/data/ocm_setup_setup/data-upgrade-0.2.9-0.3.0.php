<?php
    // Update Cms/page - 404 page 
try {
    $installer = $this;
    $installer->startSetup();
    
    if(Mage::getModel('cms/page')->load('no-route')->getId()){
        Mage::getModel('cms/page')->load('no-route')
                ->setRootTemplate('one_column')
                ->setContentHeading('We\'re sorry, the page you’re looking for can not be found.')
                ->save();
    }
    
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}	