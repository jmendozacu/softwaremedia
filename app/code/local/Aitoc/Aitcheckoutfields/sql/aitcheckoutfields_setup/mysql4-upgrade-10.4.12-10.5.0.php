<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
$installer = $this;

$installer->startSetup();

$tablesToCheck = array(
    'aitoc_order_entity_custom',
    'aitoc_custom_attribute_description',
    'aitoc_custom_attribute_need_select',
    'aitoc_customer_entity_data',
    'aitoc_custom_attribute_cg',
    'aitoc_custom_attribute_cat_refs',
    'aitoc_recurring_profile_entity_custom'
);
foreach($tablesToCheck as $table) {
    try {
        $installer->run("
            RENAME TABLE `".$table."` TO `".$installer->getTable($table)."` ;
        ");
    } catch(Exception $e)
    {
        //well, maybe tables were not exisits after all
    }
}

$installer->endSetup();