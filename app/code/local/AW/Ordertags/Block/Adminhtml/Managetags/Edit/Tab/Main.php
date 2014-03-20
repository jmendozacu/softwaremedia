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

class AW_Ordertags_Block_Adminhtml_Managetags_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('main_group', array('legend' => Mage::helper('ordertags')->__('Fields')));

        $fieldset->addField(
            'filename',
            'image',
            array(
                 'label'    => Mage::helper('ordertags')->__('Icon'),
                 'required' => true,
                 'name'     => 'filename',
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                 'label'    => Mage::helper('ordertags')->__('Name'),
                 'name'     => 'name',
                 'required' => true,
            )
        );

        $fieldset->addField(
            'sort_order',
            'text',
            array(
                 'label'    => Mage::helper('ordertags')->__('Sort Order'),
                 'name'     => 'sort_order',
                 'required' => false,
            )
        );

        $yesNoValues = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
        $fieldset->addField(
            'drop_tag',
            'select',
            array(
                 'label'  => $this->__("Drop tag if rule doesn't match anymore"),
                 'name'   => 'drop_tag',
                 'values' => $yesNoValues
            )
        );
		 $fieldset->addField(
            'email',
            'text',
            array(
                 'label'    => Mage::helper('ordertags')->__('Alert E-Mail'),
                 'name'     => 'email',
                 'required' => false,
            )
            
        );
        $fieldset->addField(
            'comment',
            'textarea',
            array(
                 'label'    => $this->__('Tag Comment'),
                 'name'     => 'comment',
                 'required' => false,
            )
        );

        if (Mage::registry('tag_data')) {
            $form->setValues(Mage::registry('tag_data')->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}