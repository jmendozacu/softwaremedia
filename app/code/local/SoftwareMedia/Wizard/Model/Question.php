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
 * Question model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Model_Question extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'softwaremedia_wizard_question';
    const CACHE_TAG = 'softwaremedia_wizard_question';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'softwaremedia_wizard_question';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'question';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('softwaremedia_wizard/question');
    }

    /**
     * before save question
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save question relation
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Retrieve  collection
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Product_Collection
     * @author Ultimate Module Creator
     */
    public function getSelectedProductsCollection()
    {
        if (!$this->hasData('_product_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('softwaremedia_wizard/product_collection')
                        ->addFieldToFilter('question_id', $this->getId());
                $this->setData('_product_collection', $collection);
            }
        }
        return $this->getData('_product_collection');
    }

    /**
     * Retrieve parent 
     *
     * @access public
     * @return null|SoftwareMedia_Wizard_Model_Wizard
     * @author Ultimate Module Creator
     */
    public function getParentWizard()
    {
        if (!$this->hasData('_parent_wizard')) {
            if (!$this->getWizardId()) {
                return null;
            } else {
                $wizard = Mage::getModel('softwaremedia_wizard/wizard')
                    ->load($this->getWizardId());
                if ($wizard->getId()) {
                    $this->setData('_parent_wizard', $wizard);
                } else {
                    $this->setData('_parent_wizard', null);
                }
            }
        }
        return $this->getData('_parent_wizard');
    }

    /**
     * get the tree model
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     * @author Ultimate Module Creator
     */
    public function getTreeModel()
    {
        return Mage::getResourceModel('softwaremedia_wizard/question_tree');
    }

    /**
     * get tree model instance
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     * @author Ultimate Module Creator
     */
    public function getTreeModelInstance()
    {
        if (is_null($this->_treeModel)) {
            $this->_treeModel = Mage::getResourceSingleton('softwaremedia_wizard/question_tree');
        }
        return $this->_treeModel;
    }

    /**
     * Move question
     *
     * @access public
     * @param   int $parentId new parent question id
     * @param   int $afterQuestionId question id after which we have put current question
     * @return  SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    public function move($parentId, $afterQuestionId)
    {
        $parent = Mage::getModel('softwaremedia_wizard/question')->load($parentId);
        if (!$parent->getId()) {
            Mage::throwException(
                Mage::helper('softwaremedia_wizard')->__(
                    'Question move operation is not possible: the new parent question was not found.'
                )
            );
        }
        if (!$this->getId()) {
            Mage::throwException(
                Mage::helper('softwaremedia_wizard')->__(
                    'Question move operation is not possible: the current question was not found.'
                )
            );
        } elseif ($parent->getId() == $this->getId()) {
            Mage::throwException(
                Mage::helper('softwaremedia_wizard')->__(
                    'Question move operation is not possible: parent question is equal to child question.'
                )
            );
        }
        $this->setMovedQuestionId($this->getId());
        $eventParams = array(
            $this->_eventObject => $this,
            'parent'            => $parent,
            'question_id'     => $this->getId(),
            'prev_parent_id'    => $this->getParentId(),
            'parent_id'         => $parentId
        );
        $moveComplete = false;
        $this->_getResource()->beginTransaction();
        try {
            $this->getResource()->changeParent($this, $parent, $afterQuestionId);
            $this->_getResource()->commit();
            $this->setAffectedQuestionIds(array($this->getId(), $this->getParentId(), $parentId));
            $moveComplete = true;
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        if ($moveComplete) {
            Mage::app()->cleanCache(array(self::CACHE_TAG));
        }
        return $this;
    }

    /**
     * Get the parent question
     *
     * @access public
     * @return  SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    public function getParentQuestion()
    {
        if (!$this->hasData('parent_question')) {
            $this->setData(
                'parent_question',
                Mage::getModel('softwaremedia_wizard/question')->load($this->getParentId())
            );
        }
        return $this->_getData('parent_question');
    }

    /**
     * Get the parent id
     *
     * @access public
     * @return  int
     * @author Ultimate Module Creator
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }

    /**
     * Get all parent questions ids
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), array($this->getId()));
    }

    /**
     * Get all questions children
     *
     * @access public
     * @param bool $asArray
     * @return mixed (array|string)
     * @author Ultimate Module Creator
     */
    public function getAllChildren($asArray = false)
    {
        $children = $this->getResource()->getAllChildren($this);
        if ($asArray) {
            return $children;
        } else {
            return implode(',', $children);
        }
    }

    /**
     * Get all questions children
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getChildQuestions()
    {
        return implode(',', $this->getResource()->getChildren($this, false));
    }

    /**
     * check the id
     *
     * @access public
     * @param int $id
     * @return bool
     * @author Ultimate Module Creator
     */
    public function checkId($id)
    {
        return $this->_getResource()->checkId($id);
    }

    /**
     * Get array questions ids which are part of question path
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve level
     *
     * @access public
     * @return int
     * @author Ultimate Module Creator
     */
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData('level');
    }

    /**
     * Verify question ids
     *
     * @access public
     * @param array $ids
     * @return bool
     * @author Ultimate Module Creator
     */
    public function verifyIds(array $ids)
    {
        return $this->getResource()->verifyIds($ids);
    }

    /**
     * check if question has children
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function hasChildren()
    {
        return $this->_getResource()->getChildrenAmount($this) > 0;
    }

    /**
     * check if question can be deleted
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    protected function _beforeDelete()
    {
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            Mage::throwException(Mage::helper('softwaremedia_wizard')->__("Can't delete root question."));
        }
        return parent::_beforeDelete();
    }

    /**
     * get the questions
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @author Ultimate Module Creator
     */
    public function getQuestions($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        return $this->getResource()->getQuestions($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
    }

    /**
     * Return parent questions of current question
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getParentQuestions()
    {
        return $this->getResource()->getParentQuestions($this);
    }

    /**
     * Return children questions of current question
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getChildrenQuestions()
    {
        return $this->getResource()->getChildrenQuestions($this);
    }

    /**
     * check if parents are enabled
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function getStatusPath()
    {
        $parents = $this->getParentQuestions();
        $rootId = Mage::helper('softwaremedia_wizard/question')->getRootQuestionId();
        foreach ($parents as $parent) {
            if ($parent->getId() == $rootId) {
                continue;
            }
            if (!$parent->getStatus()) {
                return false;
            }
        }
        return $this->getStatus();
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
