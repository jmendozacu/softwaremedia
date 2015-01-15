<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_CustomerNotes
 * @version    2.0.0
 * @copyright  Copyright (c) 2012 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
$this->startSetup();

$this->run("

ALTER TABLE `magecon_customer_notes` 
ADD COLUMN `campaign_id` INT(10) NULL DEFAULT NULL AFTER `created_time`,
ADD COLUMN `step_id` INT(10) NULL DEFAULT NULL AFTER `campaign_id`;


");

$this->endSetup();
?>