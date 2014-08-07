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

class Abcnet_CommentBox_Block_Adminhtml_List_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));
	
		$form->setUseContainer(true);
		$this->setForm($form);
		$hlp = Mage::helper('commentbox');
		
		// add the fields
		$model = Mage::registry('comment_data');
		
		$fldInfo = $form->addFieldset('comment_info', array('legend'=> $hlp->__('Comment message')));
		if ($model->getId())
		{
			$fldInfo->addField('comment_id', 'hidden', array(
				'name'      => 'comment_id',
			));
		}
		// title box
		$fldInfo->addField('title', 'text', array( 
			'label'     => $hlp->__('Title'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'title',
		));
		// message box
		$fldInfo->addField('message', 'editor', array( 
			'label'     => $hlp->__('Message'),
			'class'     => 'required-entry',
			'required'  => true,
			'wysiwyg'   => true,
			'style'     => 'width: 600px; height: 12em',
			'name'      => 'message',
		));
		
		//set form values
		$data = Mage::getSingleton('adminhtml/session')->getFormData();
		if ($data) {
			$form->setValues($data);
			Mage::getSingleton('adminhtml/session')->setFormData(null);
		}
		elseif ($model) {
			$form->setValues($model->getData());
		}

		return parent::_prepareForm();
	}
}