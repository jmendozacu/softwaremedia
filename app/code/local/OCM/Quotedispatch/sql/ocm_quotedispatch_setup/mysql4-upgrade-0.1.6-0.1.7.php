<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('ocm_quotedispatch')} ADD COLUMN `email_notes` TEXT NULL DEFAULT NULL  AFTER `notes` ;
ALTER TABLE {$this->getTable('ocm_quotedispatch_note')} CHANGE COLUMN `content` `content` TEXT NULL DEFAULT NULL  ;

");

$installer->endSetup(); 

