<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'check_no', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'paid_date', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'account_email', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'account_name', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'account_phone', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_grid'), 'pref_contact', 'TEXT NULL');

$installer->endSetup();