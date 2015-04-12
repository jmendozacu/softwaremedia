<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.7
 * @license:     grDwoQqpctpZdS57isl8WpY91kLDyrRZ7i5S4ZKTe1
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontCheckoutOnepageReview extends Mage_Checkout_Block_Onepage_Review
{
    public function getFieldHtml($aField)
    {
        $sSetName = 'customreview';
        
        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeHtml($aField, $sSetName, 'onepage',0,false,true);
    }

    public function getCustomFieldList($iTplPlaceId)
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('review');
        
        if (!$iStepId) return false;

        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getCheckoutAttributeList($iStepId, $iTplPlaceId, 'onepage');
    }

    protected function _beforeToHtml()
    {
        if (version_compare(Mage::getVersion(), '1.10.0.0', 'lt'))
        {
            $this->setTemplate('aitcommonfiles/design--frontend--base--default--template--checkout--onepage--review.phtml');
            Mage::dispatchEvent('aitoc_module_set_template_after', array('block' => $this));
        }
        return parent::_beforeToHtml();
    }
}