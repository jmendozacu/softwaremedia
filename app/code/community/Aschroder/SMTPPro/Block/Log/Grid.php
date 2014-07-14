<?php

/**
 * @author Ashley Schroder (aschroder.com)
 * @copyright  Copyright (c) 2010 Ashley Schroder
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Aschroder_SMTPPro_Block_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('emailLogGrid');
		$this->setDefaultSort('email_id');
		$this->setDefaultDir('ASC');
	}

	protected function _prepareCollection() {
		$collection = Mage::getModel('emailhistory/email')->getCollection();
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$baseUrl = $this->getUrl();

		$this->addColumn('email_id', array(
			'header' => Mage::helper('adminhtml')->__('Id'),
			'width' => '30px',
			'index' => 'id',
		));
		$this->addColumn('sent', array(
			'header' => Mage::helper('adminhtml')->__('Sent'),
			'width' => '60px',
			'index' => 'created_at',
		));
		$this->addColumn('subject', array(
			'header' => Mage::helper('adminhtml')->__('Subject'),
			'width' => '160px',
			'index' => 'subject',
		));
		$this->addColumn('to', array(
			'header' => Mage::helper('adminhtml')->__('To'),
			'width' => '160px',
			'index' => 'email',
		));
		$this->addColumn('email_body', array(
			'header' => Mage::helper('adminhtml')->__('Message'),
			'width' => '160px',
			'index' => 'text',
			'type' => 'text',
			'truncate' => 1,
			'escape' => true,
		));
		$this->addColumn('action', array(
			'header' => Mage::helper('adminhtml')->__('View'),
			'width' => '50px',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => Mage::helper('adminhtml')->__('View'),
					'url' => array('base' => '*/*/view'),
					'field' => 'email_id'
				)
			),
			'filter' => false,
			'sortable' => false,
			'is_system' => true,
		));


		return parent::_prepareColumns();
	}

	/**
	 * Row click url
	 *
	 * @return string
	 */
	public function getRowUrl($row) {
		return $this->getUrl('*/*/view', array('email_id' => $row->getId()));
	}

}
