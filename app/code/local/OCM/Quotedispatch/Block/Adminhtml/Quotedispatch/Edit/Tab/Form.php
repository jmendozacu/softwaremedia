<?php
class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('quotedispatch_form', array('legend'=>Mage::helper('quotedispatch')->__('General')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('quotedispatch')->__('Quote Name'),
          'required'  => false,
          'name'      => 'title',
      ));

      $fieldset->addField('firstname', 'text', array(
          'label'     => Mage::helper('quotedispatch')->__('First Name'),
          'required'  => false,
          'name'      => 'firstname',
      ));

      $fieldset->addField('lastname', 'text', array(
          'label'     => Mage::helper('quotedispatch')->__('Last Name'),
          'required'  => false,
          'name'      => 'lastname',
      ));

      $fieldset->addField('company', 'text', array(
          'label'     => Mage::helper('quotedispatch')->__('Company'),
          'required'  => false,
          'name'      => 'company',
      ));

      $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('quotedispatch')->__('Email'),
          'class'     => 'required-entry validate-email',
          'required'  => true,
          'name'      => 'email',
      ));

      $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('quotedispatch')->__('Phone'),
          'class'     => '',
          'required'  => false,
          'name'      => 'phone',
      ));


      $fieldset->addField('expire_time', 'date', array(
          'label'     => Mage::helper('quotedispatch')->__('Expire Date'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'expire_time',
          'format' => 'yyyy-MM-dd',
          'required'  => true,
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('quotedispatch')->__('Status'),
          'name'      => 'status',
          'values'    => Mage::getModel('quotedispatch/status')->toOptionArray()
      ));
      
      $created_by_values = Mage::getModel('quotedispatch/adminuser')->toOptionArray();
      array_unshift($created_by_values, 'Please Select');
      
      $fieldset->addField('created_by', 'select', array(
          'label'     => Mage::helper('quotedispatch')->__('Sales Rep'),
          'name'      => 'created_by',
          'values'    => $created_by_values
      ));


     
      if ( Mage::getSingleton('adminhtml/session')->getQuotedispatchData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getQuotedispatchData());
          Mage::getSingleton('adminhtml/session')->setQuotedispatchData(null);
      } elseif ( Mage::registry('quotedispatch_data') ) {
          $form->setValues(Mage::registry('quotedispatch_data')->getData());
      }
      return parent::_prepareForm();
  }
}
