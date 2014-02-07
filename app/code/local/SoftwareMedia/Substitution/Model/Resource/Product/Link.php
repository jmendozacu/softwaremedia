<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Catalog product link resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SoftwareMedia_Substitution_Model_Resource_Product_Link extends Mage_Catalog_Model_Resource_Product_Link
{
    /**
     * Save Product Links process
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @param int $typeId
     * @return Mage_Catalog_Model_Resource_Product_Link
     */
    public function saveProductLinks($product, $data, $typeId)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $attributes = $this->getAttributesByType($typeId);
        $adapter    = $this->_getWriteAdapter();

        $bind   = array(
            ':product_id'    => (int)$product->getId(),
            ':link_type_id'  => (int)$typeId
        );
        $select = $adapter->select()
            ->from($this->getMainTable(), array('linked_product_id', 'link_id'))
            ->where('product_id = :product_id')
            ->where('link_type_id = :link_type_id');

        $links   = $adapter->fetchPairs($select, $bind);

        $deleteIds = array();
        foreach($links as $linkedProductId => $linkId) {
            if (!isset($data[$linkedProductId])) {
                $deleteIds[] = (int)$linkId;
            }
        }
        if (!empty($deleteIds)) {
            $adapter->delete($this->getMainTable(), array(
                'link_id IN (?)' => $deleteIds,
            ));
        }

        foreach ($data as $linkedProductId => $linkInfo) {
            $linkId = null;
            if (isset($links[$linkedProductId])) {
                $linkId = $links[$linkedProductId];
                unset($links[$linkedProductId]);
            } else {
                $bind = array(
                    'product_id'        => $product->getId(),
                    'linked_product_id' => $linkedProductId,
                    'link_type_id'      => $typeId
                );
                $adapter->insert($this->getMainTable(), $bind);
                $linkId = $adapter->lastInsertId($this->getMainTable());
            }

            foreach ($attributes as $attributeInfo) {
                $attributeTable = $this->getAttributeTypeTable($attributeInfo['type']);
                if ($attributeTable) {
                    if (isset($linkInfo[$attributeInfo['code']])) {
                    	if ($attributeInfo['id'] == 9 && $linkInfo[$attributeInfo['code']] == '')
                    		$linkInfo[$attributeInfo['code']] = 1;
                    		
                        $value = $this->_prepareAttributeValue($attributeInfo['type'],
                            $linkInfo[$attributeInfo['code']]);
                        $bind = array(
                            'product_link_attribute_id' => $attributeInfo['id'],
                            'link_id'                   => $linkId,
                            'value'                     => $value
                        );
                        $adapter->insertOnDuplicate($attributeTable, $bind, array('value'));
                    } else {
                    	if ($attributeInfo['id'] == 9)
                    		continue;
                        $adapter->delete($attributeTable, array(
                            'link_id = ?'                   => $linkId,
                            'product_link_attribute_id = ?' => $attributeInfo['id']
                        ));
                    }
                }
            }
        }

        return $this;
    }


}
