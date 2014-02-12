<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('ocm_quotedispatch_note')} ADD COLUMN `created_date` TIMESTAMP NULL DEFAULT `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`  AFTER `created_by` ;

");

$installer->endSetup(); 
