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
 * Question tree resource model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Model_Resource_Question_Tree extends Varien_Data_Tree_Dbp
{
    const ID_FIELD        = 'entity_id';
    const PATH_FIELD      = 'path';
    const ORDER_FIELD     = 'order';
    const LEVEL_FIELD     = 'level';

    /**
     * Questions resource collection
     *
     * @var SoftwareMedia_Wizard_Model_Resource_Question_Collection
     */
    protected $_collection;
    protected $_storeId;

    /**
     * Inactive questions ids
     * @var array
     */

    protected $_inactiveQuestionIds  = null;

    /**
     * Initialize tree
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        parent::__construct(
            $resource->getConnection('softwaremedia_wizard_write'),
            $resource->getTableName('softwaremedia_wizard/question'),
            array(
                Varien_Data_Tree_Dbp::ID_FIELD    => 'entity_id',
                Varien_Data_Tree_Dbp::PATH_FIELD  => 'path',
                Varien_Data_Tree_Dbp::ORDER_FIELD => 'position',
                Varien_Data_Tree_Dbp::LEVEL_FIELD => 'level',
            )
        );
    }

    /**
     * Get questions collection
     *
     * @access public
     * @param boolean $sorted
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function getCollection($sorted = false)
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }
    /**
     * set the collection
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Resource_Question_Collection $collection
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     */
    public function setCollection($collection)
    {
        if (!is_null($this->_collection)) {
            destruct($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }
    /**
     * get the default collection
     *
     * @access protected
     * @param boolean $sorted
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     */
    protected function _getDefaultCollection($sorted = false)
    {
        $collection = Mage::getModel('softwaremedia_wizard/question')->getCollection();
        if ($sorted) {
            if (is_string($sorted)) {
                $collection->setOrder($sorted);
            } else {
                $collection->setOrder('question');
            }
        }
        return $collection;
    }

    /**
     * Executing parents move method and cleaning cache after it
     *
     * @access public
     * @param unknown_type $question
     * @param unknown_type $newParent
     * @param unknown_type $prevNode
     * @author Ultimate Module Creator
     */
    public function move($question, $newParent, $prevNode = null)
    {
        Mage::getResourceSingleton('softwaremedia_wizard/question')
            ->move($question->getId(), $newParent->getId());
        parent::move($question, $newParent, $prevNode);
        $this->_afterMove($question, $newParent, $prevNode);
    }

    /**
     * Move tree after
     *
     * @access protected
     * @param unknown_type $question
     * @param Varien_Data_Tree_Node $newParent
     * @param Varien_Data_Tree_Node $prevNode
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     */
    protected function _afterMove($question, $newParent, $prevNode)
    {
        Mage::app()->cleanCache(array(SoftwareMedia_Wizard_Model_Question::CACHE_TAG));
        return $this;
    }

    /**
     * Load whole question tree, that will include specified questions ids.
     *
     * @access public
     * @param array $ids
     * @param bool $addCollectionData
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     * @author Ultimate Module Creator
     */
    public function loadByIds($ids, $addCollectionData = true)
    {
        $levelField = $this->_conn->quoteIdentifier('level');
        $pathField  = $this->_conn->quoteIdentifier('path');
        // load first two levels, if no ids specified
        if (empty($ids)) {
            $select = $this->_conn->select()
                ->from($this->_table, 'entity_id')
                ->where($levelField . ' <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }
        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn->select()
            ->from($this->_table, array('path', 'level'))
            ->where('entity_id IN (?)', $ids);
        $where = array($levelField . '=0' => true);

        foreach ($this->_conn->fetchAll($select) as $item) {
            $pathIds  = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                $pathIds[count($pathIds) - 1] = '%';
                $path = implode('/', $pathIds);
                $where["$levelField=$level AND $pathField LIKE '$path'"] = true;
                array_pop($pathIds);
                $level--;
            }
        }
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        } else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ' . Varien_Db_Select::SQL_ASC);
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        $childrenItems = array();
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);
        return $this;
    }

    /**
     * Load array of question parents
     *
     * @access public
     * @param string $path
     * @param bool $addCollectionData
     * @param bool $withRootNode
     * @return array
     * @author Ultimate Module Creator
     */
    public function loadBreadcrumbsArray($path, $addCollectionData = true, $withRootNode = false)
    {
        $pathIds = explode('/', $path);
        if (!$withRootNode) {
            array_shift($pathIds);
        }
        $result = array();
        if (!empty($pathIds)) {
            if ($addCollectionData) {
                $select = $this->_createCollectionDataSelect(false);
            } else {
                $select = clone $this->_select;
            }
            $select
                ->where('main_table.entity_id IN(?)', $pathIds)
                ->order($this->_conn->getLengthSql('main_table.path') . ' ' . Varien_Db_Select::SQL_ASC);
            $result = $this->_conn->fetchAll($select);
        }
        return $result;
    }

    /**
     * Obtain select for questions
     * By default everything from entity table is selected
     * + name
     *
     * @access public
     * @param bool $sorted
     * @param array $optionalAttributes
     * @return Zend_Db_Select
     * @author Ultimate Module Creator
     */
    protected function _createCollectionDataSelect($sorted = true)
    {
        $select = $this->_getDefaultCollection($sorted ? $this->_orderField : false)->getSelect();
        $questionsTable = Mage::getSingleton('core/resource')
            ->getTableName('softwaremedia_wizard/question');
        $subConcat = $this->_conn->getConcatSql(array('main_table.path', $this->_conn->quote('/%')));
        $subSelect = $this->_conn->select()
            ->from(array('see' => $questionsTable), null)
            ->where('see.entity_id = main_table.entity_id')
            ->orWhere('see.path LIKE ?', $subConcat);
        return $select;
    }

    /**
     * Get real existing question ids by specified ids
     *
     * @access public
     * @param array $ids
     * @return array
     * @author Ultimate Module Creator
     */
    public function getExistingQuestionIdsBySpecifiedIds($ids)
    {
        if (empty($ids)) {
            return array();
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $select = $this->_conn->select()
            ->from($this->_table, array('entity_id'))
            ->where('entity_id IN (?)', $ids);
        return $this->_conn->fetchCol($select);
    }

    /**
     * add collection data
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Resource_Question_Collection $collection
     * @param boolean $sorted
     * @param array $exclude
     * @param boolean $toLoad
     * @param boolean $onlyActive
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     * @author Ultimate Module Creator
     */
    public function addCollectionData(
        $collection = null,
        $sorted = false,
        $exclude = array(),
        $toLoad = true,
        $onlyActive = false
    )
    {
        if (is_null($collection)) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }
        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }
        $nodeIds = array();
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {
            $disabledIds = $this->_getDisabledIds($collection);
            if ($disabledIds) {
                $collection->addFieldToFilter('entity_id', array('nin' => $disabledIds));
            }
            $collection->addFieldToFilter('status', 1);
        }
        if ($toLoad) {
            $collection->load();
            foreach ($collection as $question) {
                if ($this->getNodeById($question->getId())) {
                    $this->getNodeById($question->getId())->addData($question->getData());
                }
            }
            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }
        return $this;
    }

    /**
     * Add inactive questions ids
     *
     * @access public
     * @param unknown_type $ids
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     * @author Ultimate Module Creator
     */
    public function addInactiveQuestionIds($ids)
    {
        if (!is_array($this->_inactiveQuestionIds)) {
            $this->_initInactiveQuestionIds();
        }
        $this->_inactiveQuestionIds = array_merge($ids, $this->_inactiveQuestionIds);
        return $this;
    }

    /**
     * Retrieve inactive questions ids
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Tree
     * @author Ultimate Module Creator
     */
    protected function _initInactiveQuestionIds()
    {
        $this->_inactiveQuestionIds = array();
        return $this;
    }
    /**
     * Retrieve inactive questions ids
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getInactiveQuestionIds()
    {
        if (!is_array($this->_inactiveQuestionIds)) {
            $this->_initInactiveQuestionIds();
        }
        return $this->_inactiveQuestionIds;
    }

    /**
     * Return disable question ids
     *
     * @access protected
     * @param SoftwareMedia_Wizard_Model_Resource_Question_Collection $collection
     * @return array
     * @author Ultimate Module Creator
     */
    protected function _getDisabledIds($collection)
    {
        $this->_inactiveItems = $this->getInactiveQuestionIds();
        $this->_inactiveItems = array_merge(
            $this->_getInactiveItemIds($collection),
            $this->_inactiveItems
        );
        $allIds = $collection->getAllIds();
        $disabledIds = array();

        foreach ($allIds as $id) {
            $parents = $this->getNodeById($id)->getPath();
            foreach ($parents as $parent) {
                if (!$this->_getItemIsActive($parent->getId())) {
                    $disabledIds[] = $id;
                    continue;
                }
            }
        }
        return $disabledIds;
    }

    /**
     * Retrieve inactive question item ids
     *
     * @access protecte
     * @param SoftwareMedia_Wizard_Model_Resource_Question_Collection $collection
     * @return array
     * @author Ultimate Module Creator
     */
    protected function _getInactiveItemIds($collection)
    {
        $filter = $collection->getAllIdsSql();
        $table = Mage::getSingleton('core/resource')->getTable('softwaremedia_wizard/question');
        $bind = array(
            'cond' => 0,
        );
        $select = $this->_conn->select()
            ->from(array('d'=>$table), array('d.entity_id'))
            ->where('d.entity_id IN (?)', new Zend_Db_Expr($filter))
            ->where('status = :cond');
        return $this->_conn->fetchCol($select, $bind);
    }

    /**
     * Check is question items active
     *
     * @access protecte
     * @param int $id
     * @return boolean
     * @author Ultimate Module Creator
     */
    protected function _getItemIsActive($id)
    {
        if (!in_array($id, $this->_inactiveItems)) {
            return true;
        }
        return false;
    }
}