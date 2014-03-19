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


class AW_Ordertags_Block_Adminhtml_Managetags_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('tag_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_managetags/newConditionHtml/form/rule_conditions_fieldset'))
        ;

        $fieldset = $form
            ->addFieldset(
                'conditions_fieldset',
                array(
                     'legend' => $this->__('Conditions')
                )
            )
            ->setRenderer($renderer)
        ;

        $fieldset
            ->addField(
                'conditions',
                'text',
                array(
                     'name'     => 'conditions',
                     'label'    => $this->__('Conditions'),
                     'title'    => $this->__('Conditions'),
                     'required' => true,
                )
            )
            ->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'))
        ;

        /* AW MSS integration */
        if (Mage::helper('ordertags')->isMssEnabled()) {

            $fieldset = $form->addFieldset(
                'main_group', array('legend' => Mage::helper('ordertags')->__('Market Segmentation Suite integration'))
            );

            $mssRules = Mage::helper('ordertags')->getMssRulesToOptionArray();
            $fieldset->addField(
                'mss_rule_id',
                'select',
                array(
                     'label'  => $this->__("MSS Rule"),
                     'name'   => 'mss_rule_id',
                     'values' => $mssRules,
                )
            );
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}