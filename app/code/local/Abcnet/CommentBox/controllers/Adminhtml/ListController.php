<?php

/**
 * Abcnet_CommentBox
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

class Abcnet_CommentBox_Adminhtml_ListController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('system/commentbox');
		//$this->_addBreadcrumb(Mage::helper('commentbox')->__('ABCnet.ch extensions'), Mage::helper('commentbox')->__('ABCnet.ch extensions'));
		//$this->_addBreadcrumb(Mage::helper('commentbox')->__('New Profile'), Mage::helper('adminhtml')->__('New Profile'));
		$this->_addContent($this->getLayout()->createBlock('commentbox/adminhtml_list'));
		$this->renderLayout();
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('commentbox/comment')->load($id);
		if ($model->getId() || $id == 0)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data))
				$model->setData($data);

			Mage::register('comment_data', $model);
			$this->loadLayout();
			$this->_setActiveMenu('system/commentbox');
			$this->_addContent($this->getLayout()->createBlock('commentbox/adminhtml_list_edit'))
				->_addLeft($this->getLayout()->createBlock('commentbox/adminhtml_list_edit_tabs'));
			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('commentbox')->__('Specified Script was not found!'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			try
			{
				$model = Mage::getModel('commentbox/comment');
				// if it exists, load it and update the object
				if (($id = $this->getRequest()->getParam('id')))
				{
					$model->load($id);
				}
				// attach the post data to the object
				$model->addData($data);

				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL)
				{
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				}
				else
				{
					$model->setUpdateTime(now());
				}

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Comment message was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the comment message to save'));
		$this->_redirect('*/*/');
	}
	
	public function messageAction()
	{
		$message = Mage::getModel('commentbox/comment')->load($this->getRequest()->getParam('mid'));
		echo $message->getMessage();
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/commentbox');
	}

	public function deleteAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('commentbox/comment')->setId($id)->delete();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Comment message was successfully deleted'));
		$this->_redirect('*/*/');
	}
	
	public function massDeleteAction()
	{
		$comments = $this->getRequest()->getParam('comments');
		$total = sizeof($comments);
		if ($total == 0)
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('No comment messages ids were specified to be removed'));
		else
		{
			foreach ($comments as $comment_id)
			{
				Mage::getModel('commentbox/comment')->setId($comment_id)->delete();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('%s comment messages were successfully deleted', $total));
		}
		$this->_redirect('*/*/');
	}
}