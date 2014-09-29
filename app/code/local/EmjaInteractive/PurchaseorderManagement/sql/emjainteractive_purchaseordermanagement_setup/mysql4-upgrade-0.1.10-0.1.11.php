<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'check_no', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'paid_date', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'account_email', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'account_name', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'account_phone', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'pref_contact', 'TEXT NULL');

$installer->endSetup();