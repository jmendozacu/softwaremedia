<?php
class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('quotedispatch_form', array('legend'=>Mage::helper('quotedispatch')->__('General')));
     
      $fieldset->addField('quotedispatch_id', 'label', array(
          'label'     => Mage::helper('quotedispatch')->__('Quote Id'),
          'required'  => false,
          'name'      => 'quotedispatch_id',
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
      
      $fieldset->addField('notes', 'textarea', array(
          'label'     => Mage::helper('quotedispatch')->__('Notes From Customer'),
          'class'     => '',
          'required'  => false,
          'readonly'  => true,
          'name'      => 'notes',
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
      
      $fieldset->addField('email_notes', 'textarea', array(
          'label'     => Mage::helper('quotedispatch')->__('Email Notes To Customer'),
          'class'     => '',
          'required'  => false,
          'name'      => 'email_notes',
      ));

     
      if ( Mage::getSingleton('adminhtml/session')->getQuotedispatchData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getQuotedispatchData());
          
          Mage::getSingleton('adminhtml/session')->setQuotedispatchData(null);
      } elseif ( Mage::registry('quotedispatch_data') ) {
          $form->setValues(Mage::registry('quotedispatch_data')->getData());
      }
      if (!Mage::registry('quotedispatch_data')->getExpireTime()) {
      $now = new DateTime('now', new DateTimeZone('America/Denver'));
      $now->add(new DateInterval('P1M'));
      $expire_time = $now->format('Y-m-d H:i:s');
      $form->setValues(array('expire_time'=>$expire_time));
      }
      //die(var_dump($form->setValues(Mage::getSingleton('adminhtml/session')->getQuotedispatchData())));
      
      return parent::_prepareForm();
  }
}
