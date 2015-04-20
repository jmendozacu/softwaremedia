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
 * Question resource model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Model_Resource_Question extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Question tree object
     * @var Varien_Data_Tree_Db
     */
    protected $_tree;

    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        $this->_init('softwaremedia_wizard/question', 'entity_id');
    }

    /**
     * Retrieve question tree object
     *
     * @access protected
     * @return Varien_Data_Tree_Db
     * @author Ultimate Module Creator
     */
    protected function _getTree()
    {
        if (!$this->_tree) {
            $this->_tree = Mage::getResourceModel('softwaremedia_wizard/question_tree')->load();
        }
        return $this->_tree;
    }

    /**
     * Process question data before delete
     * update children count for parent question
     * delete child questions
     *
     * @access protected
     * @param Varien_Object $object
     * @return SoftwareMedia_Wizard_Model_Resource_Question
     * @author Ultimate Module Creator
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeDelete($object);
        /**
         * Update children count for all parent questions
         */
        $parentIds = $object->getParentIds();
        if ($parentIds) {
            $childDecrease = $object->getChildrenCount() + 1; // +1 is itself
            $data = array('children_count' => new Zend_Db_Expr('children_count - ' . $childDecrease));
            $where = array('entity_id IN(?)' => $parentIds);
            $this->_getWriteAdapter()->update($this->getMainTable(), $data, $where);
        }
        $this->deleteChildren($object);
        return $this;
    }

    /**
     * Delete children questions of specific question
     *
     * @access public
     * @param Varien_Object $object
     * @return SoftwareMedia_Wizard_Model_Resource_Question
     * @author Ultimate Module Creator
     */
    public function deleteChildren(Varien_Object $object)
    {
        $adapter = $this->_getWriteAdapter();
        $pathField = $adapter->quoteIdentifier('path');
        $select = $adapter->select()
            ->from($this->getMainTable(), array('entity_id'))
            ->where($pathField . ' LIKE :c_path');
        $childrenIds = $adapter->fetchCol($select, array('c_path' => $object->getPath() . '/%'));
        if (!empty($childrenIds)) {
            $adapter->delete(
                $this->getMainTable(),
                array('entity_id IN (?)' => $childrenIds)
            );
        }
        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }

    /**
     * Process question data after save question object
     *
     * @access protected
     * @param Varien_Object $object
     * @return SoftwareMedia_Wizard_Model_Resource_Question
     * @author Ultimate Module Creator
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }


        return parent::_afterSave($object);
    }

    /**
     * Update path field
     *
     * @access protected
     * @param SoftwareMedia_Wizard_Model_Question $object
     * @return SoftwareMedia_Wizard_Model_Resource_Question
     * @author Ultimate Module Creator
     */
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('path' => $object->getPath()),
                array('entity_id = ?' => $object->getId())
            );
        }
        return $this;
    }

    /**
     * Get maximum position of child questions by specific tree path
     *
     * @access protected
     * @param string $path
     * @return int
     * @author Ultimate Module Creator
     */
    protected function _getMaxPosition($path)
    {
        $adapter = $this->getReadConnection();
        $positionField = $adapter->quoteIdentifier('position');
        $level   = count(explode('/', $path));
        $bind = array(
            'c_level' => $level,
            'c_path'  => $path . '/%'
        );
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'MAX(' . $positionField . ')')
            ->where($adapter->quoteIdentifier('path') . ' LIKE :c_path')
            ->where($adapter->quoteIdentifier('level') . ' = :c_level');

        $position = $adapter->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        return $position;
    }

    /**
     * Get children questions count
     *
     * @access public
     * @param int $questionId
     * @return int
     * @author Ultimate Module Creator
     */
    public function getChildrenCount($questionId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'children_count')
            ->where('entity_id = :entity_id');
        $bind = array('entity_id' => $questionId);
        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Check if question id exist
     *
     * @access public
     * @param int $entityId
     * @return bool
     * @author Ultimate Module Creator
     */
    public function checkId($entityId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('entity_id = :entity_id');
        $bind =  array('entity_id' => $entityId);
        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Check array of questions identifiers
     *
     * @access public
     * @param array $ids
     * @return array
     * @author Ultimate Module Creator
     */
    public function verifyIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('entity_id IN(?)', $ids);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Get count of active/not active children questions
     *
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @param bool $isActiveFlag
     * @return int
     * @author Ultimate Module Creator
     */
    public function getChildrenAmount($question, $isActiveFlag = true)
    {
        $bind = array(
            'active_flag'  => $isActiveFlag,
            'c_path'   => $question->getPath() . '/%'
        );
        $select = $this->_getReadAdapter()->select()
            ->from(array('m' => $this->getMainTable()), array('COUNT(m.entity_id)'))
            ->where('m.path LIKE :c_path')
            ->where('status' . ' = :active_flag');
        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Return parent questions of question
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @return array
     * @author Ultimate Module Creator
     */
    public function getParentQuestions($question)
    {
        $pathIds = array_reverse(explode('/', $question->getPath()));
        $questions = Mage::getResourceModel('softwaremedia_wizard/question_collection')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->load()
            ->getItems();
        return $questions;
    }

    /**
     * Return child questions
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function getChildrenQuestions($question)
    {
        $collection = $question->getCollection();
        $collection
            ->addIdFilter($question->getChildQuestions())
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->load();
        return $collection;
    }
    /**
     * Return children ids of question
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @param boolean $recursive
     * @return array
     * @author Ultimate Module Creator
     */
    public function getChildren($question, $recursive = true)
    {
        $bind = array(
            'c_path'   => $question->getPath() . '/%'
        );
        $select = $this->_getReadAdapter()->select()
            ->from(array('m' => $this->getMainTable()), 'entity_id')
            ->where('status = ?', 1)
            ->where($this->_getReadAdapter()->quoteIdentifier('path') . ' LIKE :c_path');
        if (!$recursive) {
            $select->where($this->_getReadAdapter()->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $question->getLevel() + 1;
        }
        return $this->_getReadAdapter()->fetchCol($select, $bind);
    }

    /**
     * Process question data before saving
     * prepare path and increment children count for parent questions
     *
     * @access protected
     * @param Varien_Object $object
     * @return SoftwareMedia_Wizard_Model_Resource_Question
     * @author Ultimate Module Creator
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }
        if ($object->getLevel() === null) {
            $object->setLevel(1);
        }
        if (!$object->getId() && !$object->getInitialSetupFlag()) {
            $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            $path  = explode('/', $object->getPath());
            $level = count($path);
            $object->setLevel($level);
            if ($level) {
                $object->setParentId($path[$level - 1]);
            }
            $object->setPath($object->getPath() . '/');
            $toUpdateChild = explode('/', $object->getPath());
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('children_count'  => new Zend_Db_Expr('children_count+1')),
                array('entity_id IN(?)' => $toUpdateChild)
            );
        }
        return $this;
    }


    /**
     * Retrieve questions
     *
     * @access public
     * @param integer $parent
     * @param integer $recursionLevel
     * @param boolean|string $sorted
     * @param boolean $asCollection
     * @param boolean $toLoad
     * @return Varien_Data_Tree_Node_Collection|SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function getQuestions(
        $parent,
        $recursionLevel = 0,
        $sorted = false,
        $asCollection = false,
        $toLoad = true
    )
    {
        $tree = Mage::getResourceModel('softwaremedia_wizard/question_tree');
        $nodes = $tree->loadNode($parent)
            ->loadChildren($recursionLevel)
            ->getChildren();
        $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);
        if ($asCollection) {
            return $tree->getCollection();
        }
        return $nodes;
    }

    /**
     * Return all children ids of question (with question id)
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @return array
     * @author Ultimate Module Creator
     */
    public function getAllChildren($question)
    {
        $children = $this->getChildren($question);
        $myId = array($question->getId());
        $children = array_merge($myId, $children);
        return $children;
    }

    /**
     * Check question is forbidden to delete.
     *
     * @access public
     * @param integer $questionId
     * @return boolean
     * @author Ultimate Module Creator
     */
    public function isForbiddenToDelete($questionId)
    {
        return ($questionId == Mage::helper('softwaremedia_wizard/question')->getRootQuestionId());
    }

    /**
     * Get question path value by its id
     *
     * @access public
     * @param int $questionId
     * @return string
     * @author Ultimate Module Creator
     */
    public function getQuestionPathById($questionId)
    {
        $select = $this->getReadConnection()->select()
            ->from($this->getMainTable(), array('path'))
            ->where('entity_id = :entity_id');
        $bind = array('entity_id' => (int)$questionId);
        return $this->getReadConnection()->fetchOne($select, $bind);
    }

    /**
     * Move question to another parent node
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @param SoftwareMedia_Wizard_Model_Question $newParent
     * @param null|int $afterQuestionId
     * @return SoftwareMedia_Wizard_Model_Resource_Question
     * @author Ultimate Module Creator
     */
    public function changeParent(
        SoftwareMedia_Wizard_Model_Question $question,
        SoftwareMedia_Wizard_Model_Question $newParent,
        $afterQuestionId = null
    )
    {
        $childrenCount  = $this->getChildrenCount($question->getId()) + 1;
        $table          = $this->getMainTable();
        $adapter        = $this->_getWriteAdapter();
        $levelFiled     = $adapter->quoteIdentifier('level');
        $pathField      = $adapter->quoteIdentifier('path');

        /**
         * Decrease children count for all old question parent questions
         */
        $adapter->update(
            $table,
            array('children_count' => new Zend_Db_Expr('children_count - ' . $childrenCount)),
            array('entity_id IN(?)' => $question->getParentIds())
        );
        /**
         * Increase children count for new question parents
         */
        $adapter->update(
            $table,
            array('children_count' => new Zend_Db_Expr('children_count + ' . $childrenCount)),
            array('entity_id IN(?)' => $newParent->getPathIds())
        );

        $position = $this->_processPositions($question, $newParent, $afterQuestionId);

        $newPath  = sprintf('%s/%s', $newParent->getPath(), $question->getId());
        $newLevel = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $question->getLevel();

        /**
         * Update children nodes path
         */
        $adapter->update(
            $table,
            array(
                'path' => new Zend_Db_Expr(
                    'REPLACE(' . $pathField . ','.
                    $adapter->quote($question->getPath() . '/'). ', '.$adapter->quote($newPath . '/').')'
                ),
                'level' => new Zend_Db_Expr($levelFiled . ' + ' . $levelDisposition)
            ),
            array($pathField . ' LIKE ?' => $question->getPath() . '/%')
        );
        /**
         * Update moved question data
         */
        $data = array(
            'path'  => $newPath,
            'level' => $newLevel,
            'position'  =>$position,
            'parent_id' =>$newParent->getId()
        );
        $adapter->update($table, $data, array('entity_id = ?' => $question->getId()));
        // Update question object to new data
        $question->addData($data);
        return $this;
    }

    /**
     * Process positions of old parent question children and new parent question children.
     * Get position for moved question
     *
     * @access protected
     * @param SoftwareMedia_Wizard_Model_Question $question
     * @param SoftwareMedia_Wizard_Model_Question $newParent
     * @param null|int $afterQuestionId
     * @return int
     * @author Ultimate Module Creator
     */
    protected function _processPositions($question, $newParent, $afterQuestionId)
    {
        $table  = $this->getMainTable();
        $adapter= $this->_getWriteAdapter();
        $positionField  = $adapter->quoteIdentifier('position');

        $bind = array(
            'position' => new Zend_Db_Expr($positionField . ' - 1')
        );
        $where = array(
            'parent_id = ?' => $question->getParentId(),
            $positionField . ' > ?' => $question->getPosition()
        );
        $adapter->update($table, $bind, $where);

        /**
         * Prepare position value
         */
        if ($afterQuestionId) {
            $select = $adapter->select()
                ->from($table, 'position')
                ->where('entity_id = :entity_id');
            $position = $adapter->fetchOne($select, array('entity_id' => $afterQuestionId));
            $bind = array(
                'position' => new Zend_Db_Expr($positionField . ' + 1')
            );
            $where = array(
                'parent_id = ?' => $newParent->getId(),
                $positionField . ' > ?' => $position
            );
            $adapter->update($table, $bind, $where);
        } elseif ($afterQuestionId !== null) {
            $position = 0;
            $bind = array(
                'position' => new Zend_Db_Expr($positionField . ' + 1')
            );
            $where = array(
                'parent_id = ?' => $newParent->getId(),
                $positionField . ' > ?' => $position
            );
            $adapter->update($table, $bind, $where);
        } else {
            $select = $adapter->select()
                ->from($table, array('position' => new Zend_Db_Expr('MIN(' . $positionField. ')')))
                ->where('parent_id = :parent_id');
            $position = $adapter->fetchOne($select, array('parent_id' => $newParent->getId()));
        }
        $position += 1;
        return $position;
    }
}
