<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('ocm_quotedispatch')} ADD COLUMN `notes` VARCHAR(255) NULL DEFAULT NULL  AFTER `status` ;

");

$installer->endSetup(); 
