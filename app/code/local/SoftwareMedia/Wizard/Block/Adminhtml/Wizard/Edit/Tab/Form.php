<?php
/**
 * SoftwareMedia_Wizard extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Wizard
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Wizard edit form tab
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('wizard_');
        $form->setFieldNameSuffix('wizard');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'wizard_form',
            array('legend' => Mage::helper('softwaremedia_wizard')->__('Wizard'))
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Title'),
                'name'  => 'title',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'url_key',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Url Key'),
                'name'  => 'url_key',
                'note'  => Mage::helper('softwaremedia_wizard')->__('Relative to Website Base URL')
            )
        );
        $fieldset->addField(
            'static_block',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Static Block'),
                'name'  => 'static_block'
            )
        );
        $fieldset->addField(
            'static_block_side',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Static Block Side'),
                'name'  => 'static_block_side'
            )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('softwaremedia_wizard')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('softwaremedia_wizard')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('softwaremedia_wizard')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_wizard')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getWizardData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getWizardData());
            Mage::getSingleton('adminhtml/session')->setWizardData(null);
        } elseif (Mage::registry('current_wizard')) {
            $formValues = array_merge($formValues, Mage::registry('current_wizard')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
