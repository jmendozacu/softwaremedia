<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    
UPDATE {$this->getTable('core_config_data')} SET `value`='AE,VI,MC,DI,JCB' WHERE `config_id`='1516';

");


$installer->endSetup();

