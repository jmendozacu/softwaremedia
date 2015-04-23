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
 * Question admin block abstract
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Question_Abstract extends Mage_Adminhtml_Block_Template
{
    /**
     * get current question
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Entity
     * @author Ultimate Module Creator
     */
    public function getQuestion()
    {
        return Mage::registry('question');
    }

    /**
     * get current question id
     *
     * @access public
     * @return int
     * @author Ultimate Module Creator
     */
    public function getQuestionId()
    {
        if ($this->getQuestion()) {
            return $this->getQuestion()->getId();
        }
        return null;
    }

    /**
     * get current question Question
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getQuestionQuestion()
    {
        return $this->getQuestion()->getQuestion();
    }

    /**
     * get current question path
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getQuestionPath()
    {
        if ($this->getQuestion()) {
            return $this->getQuestion()->getPath();
        }
        return Mage::helper('softwaremedia_wizard/question')->getRootQuestionId();
    }

    /**
     * check if there is a root question
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function hasRootQuestion()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    /**
     * get the root
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question|null $parentNodeQuestion
     * @param int $recursionLevel
     * @return Varien_Data_Tree_Node
     * @author Ultimate Module Creator
     */
    public function getRoot($parentNodeQuestion = null, $recursionLevel = 3)
    {
        if (!is_null($parentNodeQuestion) && $parentNodeQuestion->getId()) {
            return $this->getNode($parentNodeQuestion, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {
            $rootId = Mage::helper('softwaremedia_wizard/question')->getRootQuestionId();
            $tree = Mage::getResourceSingleton('softwaremedia_wizard/question_tree')
                ->load(null, $recursionLevel);
            if ($this->getQuestion()) {
                $tree->loadEnsuredNodes($this->getQuestion(), $tree->getNodeById($rootId));
            }
            $tree->addCollectionData($this->getQuestionCollection());
            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
                $root->setQuestion(Mage::helper('softwaremedia_wizard')->__('Root'));
            }
            Mage::register('root', $root);
        }
        return $root;
    }

    /**
     * Get and register questions root by specified questions IDs
     *
     * @accsess public
     * @param array $ids
     * @return Varien_Data_Tree_Node
     * @author Ultimate Module Creator
     */
    public function getRootByIds($ids)
    {
        $root = Mage::registry('root');
        if (null === $root) {
            $questionTreeResource = Mage::getResourceSingleton('softwaremedia_wizard/question_tree');
            $ids     = $questionTreeResource->getExistingQuestionIdsBySpecifiedIds($ids);
            $tree   = $questionTreeResource->loadByIds($ids);
            $rootId = Mage::helper('softwaremedia_wizard/question')->getRootQuestionId();
            $root   = $tree->getNodeById($rootId);
            if ($root && $rootId != Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
                $root->setName(Mage::helper('softwaremedia_wizard')->__('Root'));
            }
            $tree->addCollectionData($this->getQuestionCollection());
            Mage::register('root', $root);
        }
        return $root;
    }

    /**
     * get specific node
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $parentNodeQuestion
     * @param $int $recursionLevel
     * @return Varien_Data_Tree_Node
     * @author Ultimate Module Creator
     */
    public function getNode($parentNodeQuestion, $recursionLevel = 2)
    {
        $tree = Mage::getResourceModel('softwaremedia_wizard/question_tree');
        $nodeId     = $parentNodeQuestion->getId();
        $parentId   = $parentNodeQuestion->getParentId();
        $node = $tree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);
        if ($node && $nodeId != Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
            $node->setQuestion(Mage::helper('softwaremedia_wizard')->__('Root'));
        }
        $tree->addCollectionData($this->getQuestionCollection());
        return $node;
    }

    /**
     * get url for saving data
     *
     * @access public
     * @param array $args
     * @return string
     * @author Ultimate Module Creator
     */
    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    /**
     * get url for edit
     *
     * @access public
     * @param array $args
     * @return string
     * @author Ultimate Module Creator
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            "*/wizard_question/edit",
            array('_current' => true, '_query'=>false, 'id' => null, 'parent' => null)
        );
    }

    /**
     * Return root ids
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getRootIds()
    {
        return array(Mage::helper('softwaremedia_wizard/question')->getRootQuestionId());
    }
}
