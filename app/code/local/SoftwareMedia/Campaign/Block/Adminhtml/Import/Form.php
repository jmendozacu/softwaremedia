<?php

class SoftwareMedia_Campaign_Block_Adminhtml_Import_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/export'),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
			)
		);

		$form->setUseContainer(true);
		$this->setForm($form);


		$fieldset2 = $form->addFieldset('peachtree_import_form', array('legend' => Mage::helper('peachtree')->__('Import')));
		$fieldset2->addField('import', 'file', array(
			'label' => Mage::helper('peachtree')->__('CSV File'),
			'required' => false,
			'name' => 'import',
		));


		return parent::_prepareForm();
	}

}
