<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

Class Ess_M2ePro_Model_Magento_Product_Rule
{
    /**
     * @var Varien_Data_Form
     */
    protected $_form;

    /**
     * @var Mage_CatalogRule_Model_Rule_Condition_Combine
     */
    protected $_conditions;

    protected $_prefix;

    protected $_productIds = array();

    protected $_collectedAttributes = array();

    // ####################################

    /**
     * Create rule instance from serialized array
     *
     * @param string $serialized
     * @throws Exception
     *
     */
    public function loadFromSerialized($serialized)
    {
        $prefix = $this->getPrefix();
        if (is_null($prefix)) {
            throw new Exception('Prefix must be specified before.');
        }

        $this->_conditions = $this->getConditionInstance($prefix);

        if (empty($serialized)) {
            return;
        }

        $conditions = unserialize($serialized);
        $this->_conditions->loadArray($conditions, $prefix);
    }

    /**
     * Create rule instance form post array
     *
     * @param array $post
     * @throws Exception
     *
     */
    public function loadFromPost(array $post)
    {
        $prefix = $this->getPrefix();
        if (is_null($prefix)) {
            throw new Exception('Prefix must be specified before.');
        }

        $this->loadFromSerialized($this->getSerializedFromPost($post, $prefix));
    }

    // ####################################

    /**
     * Get serialized array from post array
     *
     * @param array $post
     * @return string
     * @throws Exception
     *
     */
    public function getSerializedFromPost(array $post)
    {
        $prefix = $this->getPrefix();
        if (is_null($prefix)) {
            throw new Exception('Prefix must be specified before.');
        }

        $conditionsArray = $this->_convertFlatToRecursive($post['rule'][$prefix], $prefix);

        return serialize($conditionsArray[$prefix][1]);
    }

    // ####################################

    public function getPrefix()
    {
        return $this->_prefix;
    }

    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
        return $this;
    }

    public function getCollectedAttributes()
    {
        return $this->_collectedAttributes;
    }

    public function setCollectedAttributes(array $attributes)
    {
        $this->_collectedAttributes = $attributes;
    }

    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }
        return $this->_form;
    }

    /**
     * Get condition instance
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Combine
     * @throws Exception
     *
     */
    public function getConditions()
    {
        $prefix = $this->getPrefix();
        if (is_null($prefix)) {
            throw new Exception('Prefix must be specified before.');
        }

        if (is_null($this->_conditions)) {
            $this->_conditions = $this->getConditionInstance($prefix);
        }

        return $this->_conditions;
    }

    // ####################################

    /**
     * Validate magento product with rule
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        return $this->getConditions()->validate($object);
    }

    /**
     * Add filters to magento product collection
     *
     * @param Varien_Data_Collection_Db
     */
    public function setAttributesFilterToCollection(Varien_Data_Collection_Db $collection)
    {
        $this->_productIds = array();
        $this->getConditions()->collectValidatedAttributes($collection);

        Mage::getSingleton('core/resource_iterator')->walk(
            $collection->getSelect(),
            array(array($this, 'callbackValidateProduct')),
            array(
                'attributes' => $this->getCollectedAttributes(),
                'product' => Mage::getModel('catalog/product'),
            )
        );

        $collection->addIdFilter($this->_productIds);
    }

    // ####################################

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

    protected function getConditionInstance($prefix)
    {
        $conditionInstance = Mage::getModel('catalogrule/rule_condition_combine')
            ->setRule($this)
            ->setPrefix($prefix)
            ->setValue(true)
            ->setId(1)
            ->setData($prefix, array());

        return $conditionInstance;
    }

    protected function _convertFlatToRecursive(array $data, $prefix)
    {
        $arr = array();
        foreach ($data as $id=>$value) {
            $path = explode('--', $id);
            $node =& $arr;
            for ($i=0, $l=sizeof($path); $i<$l; $i++) {
                if (!isset($node[$prefix][$path[$i]])) {
                    $node[$prefix][$path[$i]] = array();
                }
                $node =& $node[$prefix][$path[$i]];
            }
            foreach ($value as $k => $v) {
                $node[$k] = $v;
            }
        }

        return $arr;
    }

    // ####################################

    /**
     * Using model from controller
     *
     *      get serialized data for saving to database ($serializedData):
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix');
     *          $serializedData = $ruleModel->getSerializedFromPost($post);
     *
     *      set model to block for view rules from database ($serializedData):
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix');
     *          $ruleModel->loadFromSerialized($serializedData);
     *
     *          $ruleBlock = $this->getLayout()
     *                            ->createBlock('M2ePro/adminhtml_magento_product_rule')
     *                            ->setData('rule_model', $ruleModel);
     *
     * Using model for check magento product with rule
     *
     *      using serialized data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix');
     *          $ruleModel->loadFromSerialized($serializedData);
     *          $checkingResult = $ruleModel->validate($magentoProductInstance);
     *
     *      using post array data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix');
     *          $ruleModel->loadFromPost($post);
     *          $checkingResult = $ruleModel->validate($magentoProductInstance);
     *
     * Using model for filter magento product collection with rule
     *
     *      using serialized data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix');
     *          $ruleModel->loadFromSerialized($serializedData);
     *          $ruleModel->setAttributesFilterToCollection($magentoProductCollection);
     *
     *      using post array data:
     *          $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setPrefix('your_prefix');
     *          $ruleModel->loadFromPost($post);
     *          $ruleModel->setAttributesFilterToCollection($magentoProductCollection);
     *
     */
}