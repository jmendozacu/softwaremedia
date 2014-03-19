<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ordertags
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ordertags_Model_Managetags extends Mage_Rule_Model_Rule
{
    protected $_resource;

    const DEFAULT_TAG = 'ordertags/configuration/defaulttag';

    public function _construct()
    {
        parent::_construct();
        $this->_init('ordertags/managetags');
        $this->_resource = Mage::getResourceModel('ordertags/orderidtotagid');
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('ordertags/rule_condition_combine');
    }

    public function getRules($mode = 'instance', $id = null)
    {
        $instance = Mage::getModel('ordertags/managetags');
        if ($mode == 'instance') {
            return $instance->load($id);
        } else {
            return $instance->getCollection();
        }
    }

    public function validateObject(Varien_Object $objToValidate, $order)
    {
        $rules = $this->getRules('collection');

        // remove tags
        $tagsToDrop = Mage::getModel('ordertags/managetags')
            ->getCollection()
            ->addFieldToFilter('drop_tag', array('eq' => 1))
            ->getAllIds()
        ;
        $this->_resource->removeFromDB($objToValidate->getOrderId(), $tagsToDrop);

        foreach ($rules as $rule) {
            $ruleValidationResult = null;
            $validator = $this->getRules('instance', $rule->getTagId());
            if ($this->_validateProcess($validator, $objToValidate)) {
                $ruleValidationResult = $validator->validate($objToValidate);
            }

            $mssValidationResult = null;
            if ($rule->getMssRuleId()) {
                $mssValidationResult = Mage::helper('ordertags')->validateMss($rule, $order);
            }

            if (
                ($ruleValidationResult && $mssValidationResult)
                || (is_null($ruleValidationResult) && $mssValidationResult)
                || ($ruleValidationResult && is_null($mssValidationResult))
            ) {
                $this->_resource->removeFromDB($objToValidate->getOrderId(), $rule->getTagId());
                $this->_resource->loadIdsToTable($objToValidate->getOrderId(), $rule->getTagId());
            }
        }

        /* Add default tag to order, but only if
         * 1. It is set in System > Configuration
         * 2. No rule tags were applied during validation
         */
        $this->_processDefaultTag($objToValidate);
    }

    private function _validateProcess($validator, $objToValidate)
    {
        if ($validator->getData('conditions_serialized') == 's:4:"none";') {
            return false;
        }
        $unserializedRule = unserialize($validator->getData('conditions_serialized'));

        if (!array_key_exists("conditions", $unserializedRule)) {
            return false;
        }
        $invocedItemsTotal = $objToValidate->getData('order_items_total');

        if (
            is_null($invocedItemsTotal)
            && (strpos($validator->getData('conditions_serialized'), 'order_items_total') !== false)
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param Varien_Object $objToValidate
     *
     * @return bool
     */
    private function _processDefaultTag($objToValidate)
    {
        if (!$this->_resource->getArrayByOrderId($objToValidate->getOrderId())) {
            if (Mage::getStoreConfig(self::DEFAULT_TAG, $objToValidate->getStoreId()) != 'none') {
                $this->_resource->loadIdsToTable(
                    $objToValidate->getOrderId(), Mage::getStoreConfig(self::DEFAULT_TAG, $objToValidate->getStoreId())
                );
                return true;
            }
        }
        return false;
    }
}