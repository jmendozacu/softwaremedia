<?php

/**
 * Abcnet_CommentBox
 * www.abcnet.ch | www.pixelplant.ro
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Abcnet
 * @package    Abcnet_CommentBox
 * @copyright  Copyright (c) 2011 Mogos Radu, radu.mogos@pixelplant.ro, radu.mogos@abcnet.ch
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Abcnet_CommentBox_Block_Adminhtml_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('commentboxListGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInProfile(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('commentbox/comment')->getCollection();
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns()
	{
		$this->addColumn('comment_id', array(
			'header'    => $this->__('ID'),
			'align'     => 'left',
			'width'     => '50px',
			'index'     => 'comment_id',
			'type'      => 'number',
		));

		$this->addColumn('title', array(
			'header'    => $this->__('Name'),
			'align'     => 'left',
			'width'     => '200px',
			'index'     => 'title',
		));
		
		$this->addColumn('message', array(
			'header'    => $this->__('Message'),
			'align'     => 'left',
			//'width'     => '300px',
			'index'     => 'message',
		));

		$this->addColumn('date', array(
			'header'    => $this->__('Created'),
			'align'     => 'left',
			'width'     => '100px',
			'type'      => 'datetime',
			'index'     => 'date',
		));

		/*$this->addColumn('type', array(
			'header'    => $this->__('Script type'),
			'align'     => 'left',
			'index'     => 'type',
			'type'      => 'options',
			'options'   => array(
				'import' => $this->__('Import'),
				'export'  => $this->__('Export'),
			),
		));*/
		//$this->addExportType('*/*/exportCsv', Mage::helper('urapidflow')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('urapidflow')->__('XML'));

		return parent::_prepareColumns();
	}
	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('comment_id');
		$this->getMassactionBlock()->setFormFieldName('comments');
		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => $this->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => $this->__('Are you sure you want to delete this item?')
		));
		return $this;
	}
	

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}
