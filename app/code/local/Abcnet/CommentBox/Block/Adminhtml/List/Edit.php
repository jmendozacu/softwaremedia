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

class Abcnet_CommentBox_Block_Adminhtml_List_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
	}

	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'commentbox';
		$this->_controller = 'adminhtml_list';

		$comment = Mage::registry('comment_data');
		$id = $comment->getId();

		/*$this->_addButton('save', array(
			'label'     => Mage::helper('adminhtml')->__('Save'),
			'onclick'   => "editForm.submit(\$('edit_form').action+'back/edit/')",
			'class'     => 'save',
		), -100);*/
	}

	public function getHeaderText()
	{
		$comment = Mage::registry('comment_data');

		if ($comment->getId() > 0)
			return $this->__("Edit comment message '%s'", $comment->title);
		return $this->__("New comment message");
	}
}
