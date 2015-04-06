<?php
/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
/* @var bool $dropKeyRequire Is Require to drop old foreign key */
$dropKeyRequire = false;
try {
    $tableName = $this->getTable('aw_arep_sku_relevance');
    $sql = new Zend_Db_Expr("SHOW CREATE TABLE `{$tableName}`");
    foreach ($this->getConnection()->fetchPairs($sql) as $result) {
        if (strpos($result, "FK_AREP_VARCHAR_PRODUCT_SKU") !== false) {
            $dropKeyRequire = true;
        }
    }
} catch (Exception $e) {
}
if ($dropKeyRequire) {
    $installer->run(
        "ALTER TABLE {$this->getTable('aw_arep_sku_relevance')} DROP FOREIGN KEY `FK_AREP_VARCHAR_PRODUCT_SKU`;"
    );
}
$installer->endSetup();