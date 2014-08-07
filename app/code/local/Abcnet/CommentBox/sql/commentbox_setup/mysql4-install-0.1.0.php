<?php

/**
 * Abcnet_CommentBox
 * www.abcnet.ch
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

$installer = $this;
$installer->startSetup();

$installer->run("
	CREATE TABLE `{$installer->getTable('commentbox/comment')}` (
	  `comment_id` int(11) NOT NULL auto_increment,
	  `title` text,
	  `message` text,
	  `date` datetime default NULL,
	  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
	  PRIMARY KEY  (`comment_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();
