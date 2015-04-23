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
 * Question list block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Question_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $questions = Mage::getResourceModel('softwaremedia_wizard/question_collection')
                         ->addFieldToFilter('status', 1);
        ;
        $questions->getSelect()->order('main_table.entity_id');
        $this->setQuestions($questions);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Question_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getQuestions()->addFieldToFilter('level', 1);
        if ($this->_getDisplayMode() == 0) {
            $pager = $this->getLayout()->createBlock(
                'page/html_pager',
                'softwaremedia_wizard.questions.html.pager'
            )
            ->setCollection($this->getQuestions());
            $this->setChild('pager', $pager);
            $this->getQuestions()->load();
        }
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get the display mode
     *
     * @access protected
     * @return int
     * @author Ultimate Module Creator
     */
    protected function _getDisplayMode()
    {
        return Mage::getStoreConfigFlag('softwaremedia_wizard/question/tree');
    }

    /**
     * draw question
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question
     * @param int $level
     * @return int
     * @author Ultimate Module Creator
     */
    public function drawQuestion($question, $level = 0)
    {
        $html = '';
        $recursion = $this->getRecursion();
        if ($recursion !== '0' && $level >= $recursion) {
            return '';
        }
        $storeIds = Mage::getResourceSingleton(
            'softwaremedia_wizard/question'
        )
        ->lookupStoreIds($question->getId());
        $validStoreIds = array(0, Mage::app()->getStore()->getId());
        if (!array_intersect($storeIds, $validStoreIds)) {
            return '';
        }
        if (!$question->getStatus()) {
            return '';
        }
        $children = $question->getChildrenQuestions();
        $activeChildren = array();
        if ($recursion == 0 || $level < $recursion-1) {
            foreach ($children as $child) {
                $childStoreIds = Mage::getResourceSingleton(
                    'softwaremedia_wizard/question'
                )
                ->lookupStoreIds($child->getId());
                $validStoreIds = array(0, Mage::app()->getStore()->getId());
                if (!array_intersect($childStoreIds, $validStoreIds)) {
                    continue;
                }
                if ($child->getStatus()) {
                    $activeChildren[] = $child;
                }
            }
        }
        $html .= '<li>';
        $html .= '<a href="#">'.$question->getQuestion().'</a>';
        if (count($activeChildren) > 0) {
            $html .= '<ul>';
            foreach ($children as $child) {
                $html .= $this->drawQuestion($child, $level+1);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        return $html;
    }

    /**
     * get recursion
     *
     * @access public
     * @return int
     * @author Ultimate Module Creator
     */
    public function getRecursion()
    {
        if (!$this->hasData('recursion')) {
            $this->setData('recursion', Mage::getStoreConfig('softwaremedia_wizard/question/recursion'));
        }
        return $this->getData('recursion');
    }
}
