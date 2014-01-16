<?php

class OCM_Peachtree_Block_Adminhtml_Peachtree_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
              'id' => 'edit_form',
              'action' => $this->getUrl('*/*/export'),
              'method' => 'post',
           )
      );

      $form->setUseContainer(true);
      $this->setForm($form);

      $fieldset = $form->addFieldset('peachtree_form', array('legend'=>Mage::helper('peachtree')->__('Date Range')));
     
      $fieldset->addField('from', 'date', array(
          'label'     => Mage::helper('peachtree')->__('From Date'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'from',
          'format' => 'yyyy-MM-dd',
          'required'  => true,
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      ));

      $fieldset->addField('to', 'date', array(
          'label'     => Mage::helper('peachtree')->__('To Date'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'to',
          'format' => 'yyyy-MM-dd',
          'required'  => true,
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
      ));

     
      if ( Mage::getSingleton('adminhtml/session')->getPeachtreeData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPeachtreeData());
          Mage::getSingleton('adminhtml/session')->setPeachtreeData(null);
      } elseif ( Mage::registry('peachtree_data') ) {
          $form->setValues(Mage::registry('peachtree_data')->getData());
      }



      return parent::_prepareForm();
  }
}