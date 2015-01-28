<?php
/**
 * SoftwareMedia_Ratings extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Ratings
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Ratings module install script
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
 
$installer = $this;
 
$installer->startSetup();
$installer->run("
	ALTER TABLE `mage`.`softwaremedia_ratings_rating` 
ADD COLUMN `source` VARCHAR(45) NULL DEFAULT NULL AFTER `created_at`;
");

  
$installer->endSetup();
