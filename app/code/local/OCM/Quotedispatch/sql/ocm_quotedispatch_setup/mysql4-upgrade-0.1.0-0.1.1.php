<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('ocm_quotedispatch')} ADD `created_by` VARCHAR(255)  NULL  DEFAULT NULL  AFTER `created_time`;
ALTER TABLE {$this->getTable('ocm_quotedispatch')} CHANGE `created_time` `available_time` DATETIME  NULL  DEFAULT NULL;

    ");

$installer->endSetup(); 