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

class Abcnet_CommentBox_Model_Mysql4_Comment extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('commentbox/comment', 'comment_id');
	}
}