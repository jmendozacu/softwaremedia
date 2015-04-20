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
 * Question admin edit tabs
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        $this->setId('question_info_tabs');
        $this->setDestElementId('question_tab_content');
        $this->setTitle(Mage::helper('softwaremedia_wizard')->__('Question'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    /**
     * Prepare Layout Content
     *
     * @access public
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Tabs
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'form_question',
            array(
                'label'   => Mage::helper('softwaremedia_wizard')->__('Question'),
                'title'   => Mage::helper('softwaremedia_wizard')->__('Question'),
                'content' => $this->getLayout()->createBlock(
                    'softwaremedia_wizard/adminhtml_question_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve question entity
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    public function getQuestion()
    {
        return Mage::registry('current_question');
    }
}
