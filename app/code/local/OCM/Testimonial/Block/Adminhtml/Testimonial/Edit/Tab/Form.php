<?php
class OCM_Testimonial_Block_Adminhtml_Testimonial_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('test_form',
            array('legend'=>'Message information'));
        $fieldset->addField('user_name', 'text',
            array(
                'label' => 'User Name',
                'class' => 'required-entry',
                'required' => true,
                'name' => 'user_name',
            ));
        $fieldset->addField('date_post', 'date', array(
            'label'     => Mage::helper('ocm_testimonial')->__('Date Post'),
            'tabindex' => 1,
            'name'    => 'date_post',
            'required'  => true,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $fieldset->addField('public', 'select', array(
            'label'     => Mage::helper('ocm_testimonial')->__('Select'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'public',
            'onclick' => "",
            'onchange' => "",
            'value'  => '1',
            'values' => array('-1'=>'Please Select..','0' => 'Disable','1'=>'Enable'),
            'disabled' => false,
            'readonly' => false,
            'tabindex' => 1
        ));
        $fieldset->addField('message', 'textarea', array(
            'label'     => Mage::helper('ocm_testimonial')->__('Message'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'message',
            'onclick' => "",
            'value'  => '<b><b/>',
            'disabled' => false,
            'tabindex' => 1
        ));
        if ( Mage::registry('message_data') )
        {
            $form->setValues(Mage::registry('message_data')->getData());
        }
        return parent::_prepareForm();
    }
}