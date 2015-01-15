<?php
/**
 * SoftwareMedia_Campaign extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Campaign
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Campaign edit form tab
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Block_Adminhtml_Campaign_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Campaign_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('campaign_');
        $form->setFieldNameSuffix('campaign');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'campaign_form',
            array('legend' => Mage::helper('softwaremedia_campaign')->__('Campaign'))
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_campaign')->__('Campaign Name'),
                'name'  => 'name',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('softwaremedia_campaign')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('softwaremedia_campaign')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('softwaremedia_campaign')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_campaign')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getCampaignData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getCampaignData());
            Mage::getSingleton('adminhtml/session')->setCampaignData(null);
        } elseif (Mage::registry('current_campaign')) {
            $formValues = array_merge($formValues, Mage::registry('current_campaign')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
