<?php

$installer = $this;

$installer->startSetup();

$installer->run('ALTER TABLE ' . $this->getTable('admin_user') . ' ADD COLUMN `office_password` VARCHAR(100) NULL DEFAULT NULL  AFTER `rp_token_created_at`');

$installer->endSetup();
