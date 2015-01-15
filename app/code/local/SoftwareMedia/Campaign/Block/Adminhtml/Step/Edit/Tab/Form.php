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
 * Step edit form tab
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Block_Adminhtml_Step_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Step_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('step_');
        $form->setFieldNameSuffix('step');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'step_form',
            array('legend' => Mage::helper('softwaremedia_campaign')->__('Info'))
        );
        $values = Mage::getResourceModel('softwaremedia_campaign/campaign_collection')
            ->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="step_campaign_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeCampaignIdLink() {
                if ($(\'step_campaign_id\').value == \'\') {
                    $(\'step_campaign_id_link\').hide();
                } else {
                    $(\'step_campaign_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/campaign_campaign/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'step_campaign_id\').value);
                    $(\'step_campaign_id_link\').href = realUrl;
                    $(\'step_campaign_id_link\').innerHTML = text.replace(\'{#name}\', $(\'step_campaign_id\').options[$(\'step_campaign_id\').selectedIndex].innerHTML);
                }
            }
            $(\'step_campaign_id\').observe(\'change\', changeCampaignIdLink);
            changeCampaignIdLink();
            </script>';

        $fieldset->addField(
            'campaign_id',
            'select',
            array(
                'label'     => Mage::helper('softwaremedia_campaign')->__('Campaign'),
                'name'      => 'campaign_id',
                'required'  => false,
                'values'    => $values,
                'required'  => true,
                'after_element_html' => $html
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_campaign')->__('Name'),
                'name'  => 'name',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                'label' => Mage::helper('softwaremedia_campaign')->__('Description'),
                'name'  => 'description',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'sort',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_campaign')->__('Order'),
                'name'  => 'sort',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'reminder',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_campaign')->__('Reminder (days)'),
                'name'  => 'reminder',

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
        $formValues = Mage::registry('current_step')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getStepData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getStepData());
            Mage::getSingleton('adminhtml/session')->setStepData(null);
        } elseif (Mage::registry('current_step')) {
            $formValues = array_merge($formValues, Mage::registry('current_step')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
