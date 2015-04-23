<?php
/**
 * SoftwareMedia_Wizard extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Wizard
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Product admin edit form
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        $this->_blockGroup = 'softwaremedia_wizard';
        $this->_controller = 'adminhtml_product';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('softwaremedia_wizard')->__('Save Product')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('softwaremedia_wizard')->__('Delete Product')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('softwaremedia_wizard')->__('Save And Continue Edit'),
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
        if (Mage::registry('current_product') && Mage::registry('current_product')->getId()) {
            return Mage::helper('softwaremedia_wizard')->__(
                "Edit Product '%s'",
                $this->escapeHtml(Mage::registry('current_product')->getName())
            );
        } else {
            return Mage::helper('softwaremedia_wizard')->__('Assign Product');
        }
    }
}
