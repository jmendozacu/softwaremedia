<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this->startSetup();
/* Remove all aggregated data */
/* @var $helper AW_Advancedreports_Helper_Data */
$helper = Mage::helper('advancedreports');
$helper->getAggregator()->cleanCache();
$installer->getConnection()->changeColumn($this->getTable('advancedreports/aggregation'), 'expired', 'expired', 'DATE DEFAULT NULL');
$installer->endSetup();
