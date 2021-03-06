<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_ShippingOverride_Service extends Ess_M2ePro_Model_Component_Abstract
{
    const TYPE_EXCLUSIVE   = 0;
    const TYPE_ADDITIVE    = 1;
    const TYPE_RESTRICTIVE = 2;

    const COST_MODE_FREE             = 0;
    const COST_MODE_CUSTOM_VALUE     = 1;
    const COST_MODE_CUSTOM_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_ShippingOverride
     */
    private $shippingOverrideTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_ShippingOverride_Service_Source[]
     */
    private $shippingOverrideServiceSourceModels = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_ShippingOverride_Service');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->shippingOverrideTemplateModel = NULL;
        $temp && $this->shippingOverrideServiceSourceModels = array();
        return $temp;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ShippingOverride
     */
    public function getShippingOverrideTemplate()
    {
        if (is_null($this->shippingOverrideTemplateModel)) {
            $this->shippingOverrideTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Amazon_Template_ShippingOverride', $this->getTemplateShippingOverrideId(), NULL, array('template')
            );
        }

        return $this->shippingOverrideTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Template_ShippingOverride $instance
     */
    public function setShippingOverrideTemplate(Ess_M2ePro_Model_Amazon_Template_ShippingOverride $instance)
    {
         $this->shippingOverrideTemplateModel = $instance;
    }

    //------------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_ShippingOverride_Service_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $id = $magentoProduct->getProductId();

        if (!empty($this->shippingOverrideServiceSourceModels[$id])) {
            return $this->shippingOverrideServiceSourceModels[$id];
        }

        $this->shippingOverrideServiceSourceModels[$id] =
            Mage::getModel('M2ePro/Amazon_Template_ShippingOverride_Service_Source');

        $this->shippingOverrideServiceSourceModels[$id]->setMagentoProduct($magentoProduct);
        $this->shippingOverrideServiceSourceModels[$id]->setShippingOverrideServiceTemplate($this);

        return $this->shippingOverrideServiceSourceModels[$id];
    }

    // #######################################

    public function getTemplateShippingOverrideId()
    {
        return (int)$this->getData('template_shipping_override_id');
    }

    //-----------------------------------------

    public function getService()
    {
        return $this->getData('service');
    }

    public function getLocation()
    {
        return $this->getData('location');
    }

    public function getOption()
    {
        return $this->getData('option');
    }

    //-----------------------------------------

    public function getType()
    {
        return (int)$this->getData('type');
    }

    //-----------------------------------------

    public function isTypeExclusive()
    {
        return $this->getType() == self::TYPE_EXCLUSIVE;
    }

    public function isTypeAdditive()
    {
        return $this->getType() == self::TYPE_ADDITIVE;
    }

    public function isTypeRestrictive()
    {
        return $this->getType() == self::TYPE_RESTRICTIVE;
    }

    //-----------------------------------------

    public function getCostMode()
    {
        return (int)$this->getData('cost_mode');
    }

    //-----------------------------------------

    public function isCostModeFree()
    {
        return $this->getCostMode() == self::COST_MODE_FREE;
    }

    public function isCostModeCustomValue()
    {
        return $this->getCostMode() == self::COST_MODE_CUSTOM_VALUE;
    }

    public function isCostModeCustomAttribute()
    {
        return $this->getCostMode() == self::COST_MODE_CUSTOM_ATTRIBUTE;
    }

    // #######################################

    public function getCostValue()
    {
        return $this->getData('cost_value');
    }

    public function getCostAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostValue();
        }

        return $attributes;
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    public function getUsedAttributes()
    {
        return array_unique(
            $this->getCostAttributes()
        );
    }

    // #######################################
}