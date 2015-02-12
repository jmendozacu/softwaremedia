<?php

class SoftwareMedia_Campaign_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {
		parent::__construct();

		$this->_objectId = 'id';
		$this->_mode = 'import';
		$this->_blockGroup = 'softwaremedia_campaign';
		$this->_controller = 'adminhtml';

		//$this->_updateButton('save', 'label', Mage::helper('peachtree')->__('Import CSV'));

		$this->_addButton('import', array('label' => Mage::helper('catalog')->__('Import CSV'),
			'onclick' => '$(\'edit_form\').writeAttribute(\'action\',\'' . $this->getUrl('*/*/import') . '\'); $(\'edit_form\').submit()',
			)
		);
		
		$this->_removeButton('delete');
		$this->_removeButton('save');
		$this->_removeButton('back');
		$this->_removeButton('reset');
	}

	public function getHeaderText() {
		return Mage::helper('softwaremedia_campaign')->__('Import Campaign Contacts');
	}

}
