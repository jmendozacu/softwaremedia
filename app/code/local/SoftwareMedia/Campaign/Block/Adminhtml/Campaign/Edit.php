<?php
/**
 * SoftwareMedia_Campaign extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Campaign
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Campaign admin edit form
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Block_Adminhtml_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        $this->_blockGroup = 'softwaremedia_campaign';
        $this->_controller = 'adminhtml_campaign';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('softwaremedia_campaign')->__('Save Campaign')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('softwaremedia_campaign')->__('Delete Campaign')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('softwaremedia_campaign')->__('Save And Continue Edit'),
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
        if (Mage::registry('current_campaign') && Mage::registry('current_campaign')->getId()) {
            return Mage::helper('softwaremedia_campaign')->__(
                "Edit Campaign '%s'",
                $this->escapeHtml(Mage::registry('current_campaign')->getName())
            );
        } else {
            return Mage::helper('softwaremedia_campaign')->__('Add Campaign');
        }
    }
}
