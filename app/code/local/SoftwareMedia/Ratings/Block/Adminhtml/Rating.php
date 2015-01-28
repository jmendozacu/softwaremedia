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
 * Rating admin block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Block_Adminhtml_Rating extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_rating';
        $this->_blockGroup         = 'softwaremedia_ratings';
        parent::__construct();
        $this->_headerText         = Mage::helper('softwaremedia_ratings')->__('Rating');
        $this->_removeButton('add');
        //$this->_updateButton('add', 'label', Mage::helper('softwaremedia_ratings')->__('Add Rating'));

    }
}
