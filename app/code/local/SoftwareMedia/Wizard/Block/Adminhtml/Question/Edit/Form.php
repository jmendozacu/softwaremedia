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
 * Question edit form
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Form extends SoftwareMedia_Wizard_Block_Adminhtml_Question_Abstract
{
    /**
     * Additional buttons on question page
     * @var array
     */
    protected $_additionalButtons = array();
    /**
     * constructor
     *
     * set template
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('softwaremedia_wizard/question/edit/form.phtml');
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        $question = $this->getQuestion();
        $questionId = (int)$question->getId();
        $this->setChild(
            'tabs',
            $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_question_edit_tabs', 'tabs')
        );
        $this->setChild(
            'save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('softwaremedia_wizard')->__('Save Question'),
                        'onclick' => "questionSubmit('" . $this->getSaveUrl() . "', true)",
                        'class'   => 'save'
                    )
                )
        );
        // Delete button
        if (!in_array($questionId, $this->getRootIds())) {
            $this->setChild(
                'delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(
                        array(
                            'label'   => Mage::helper('softwaremedia_wizard')->__('Delete Question'),
                            'onclick' => "questionDelete('" . $this->getUrl(
                                '*/*/delete',
                                array('_current' => true)
                            )
                            . "', true, {$questionId})",
                            'class'   => 'delete'
                        )
                    )
            );
        }

        // Reset button
        $resetPath = $question ? '*/*/edit' : '*/*/add';
        $this->setChild(
            'reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label' => Mage::helper('softwaremedia_wizard')->__('Reset'),
                        'onclick'   => "questionReset('".$this->getUrl(
                            $resetPath,
                            array('_current'=>true)
                        )
                        ."',true)"
                    )
                )
        );
        return parent::_prepareLayout();
    }

    /**
     * get html for delete button
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * get html for save button
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * get html for reset button
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve additional buttons html
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getAdditionalButtonsHtml()
    {
        $html = '';
        foreach ($this->_additionalButtons as $childName) {
            $html .= $this->getChildHtml($childName);
        }
        return $html;
    }

    /**
     * Add additional button
     *
     * @param string $alias
     * @param array $config
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Form
     * @author Ultimate Module Creator
     */
    public function addAdditionalButton($alias, $config)
    {
        if (isset($config['name'])) {
            $config['element_name'] = $config['name'];
        }
        $this->setChild(
            $alias . '_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->addData($config)
        );
        $this->_additionalButtons[$alias] = $alias . '_button';
        return $this;
    }

    /**
     * Remove additional button
     *
     * @access public
     * @param string $alias
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Form
     * @author Ultimate Module Creator
     */
    public function removeAdditionalButton($alias)
    {
        if (isset($this->_additionalButtons[$alias])) {
            $this->unsetChild($this->_additionalButtons[$alias]);
            unset($this->_additionalButtons[$alias]);
        }
        return $this;
    }

    /**
     * get html for tabs
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getTabsHtml()
    {
        return $this->getChildHtml('tabs');
    }

    /**
     * get the form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeader()
    {
        if ($this->getQuestionId()) {
            return $this->getQuestionQuestion();
        } else {
        	if ($this->getRequest()->getParam('parent') > 1) {
	        	return Mage::helper('softwaremedia_wizard')->__('New Child Question');
	        	
        	}
            return Mage::helper('softwaremedia_wizard')->__('New Root Question');
        }
    }

    /**
     * get the delete url
     *
     * @access public
     * @param array $args
     * @return string
     * @author Ultimate Module Creator
     */
    public function getDeleteUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/delete', $params);
    }

    /**
     * Return URL for refresh input element 'path' in form
     *
     * @access public
     * @param array $args
     * @return string
     * @author Ultimate Module Creator
     */
    public function getRefreshPathUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/refreshPath', $params);
    }

    /**
     * check if request is ajax
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function isAjax()
    {
        return Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax');
    }
}
