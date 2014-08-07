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

class Abcnet_CommentBox_Block_Adminhtml_Sales_Order_View_History extends Mage_Adminhtml_Block_Sales_Order_View_History
{
	protected function _prepareLayout()
	{
		$this->setTemplate('commentbox/history.phtml');
		return parent::_prepareLayout();
	}

	public function getCommentMessages()
	{
		return Mage::getModel('commentbox/comment')->getCollection();
	}

}
