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
 * Rating admin edit form
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Block_Adminhtml_Rating_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        parent::__construct();
        $this->_blockGroup = 'softwaremedia_ratings';
        $this->_controller = 'adminhtml_rating';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('softwaremedia_ratings')->__('Save Rating')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('softwaremedia_ratings')->__('Delete Rating')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('softwaremedia_ratings')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_rating') && Mage::registry('current_rating')->getId()) {
            return "Edit Rating";
                    } else {
            return Mage::helper('softwaremedia_ratings')->__('Add Rating');
        }
    }
}
