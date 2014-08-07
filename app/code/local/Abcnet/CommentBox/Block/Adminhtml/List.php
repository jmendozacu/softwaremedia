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

class Abcnet_CommentBox_Block_Adminhtml_List extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_list';
		$this->_blockGroup = 'commentbox';
		//$this->_addBackButton();
		$this->_headerText = Mage::helper('commentbox')->__('Manage messages available for the order comment box'); 
		parent::__construct();
	}
}
