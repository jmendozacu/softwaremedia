<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('admin_user')} ADD COLUMN `admin_phone` VARCHAR(25) NULL DEFAULT NULL  AFTER `email` ;


");

$installer->endSetup(); 
