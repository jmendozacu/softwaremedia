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
 * Question collection resource model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Model_Resource_Question_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joinedFields = array();

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('softwaremedia_wizard/question');
    }

    /**
     * Add Id filter
     *
     * @access public
     * @param array $questionIds
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function addIdFilter($questionIds)
    {
        if (is_array($questionIds)) {
            if (empty($questionIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $questionIds);
            }
        } elseif (is_numeric($questionIds)) {
            $condition = $questionIds;
        } elseif (is_string($questionIds)) {
            $ids = explode(',', $questionIds);
            if (empty($ids)) {
                $condition = $questionIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    /**
     * Add question path filter
     *
     * @access public
     * @param string $regexp
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function addPathFilter($regexp)
    {
        $this->addFieldToFilter('path', array('regexp' => $regexp));
        return $this;
    }

    /**
     * Add question path filter
     *
     * @access public
     * @param array|string $paths
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function addPathsFilter($paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        $write  = $this->getResource()->getWriteConnection();
        $cond   = array();
        foreach ($paths as $path) {
            $cond[] = $write->quoteInto('e.path LIKE ?', "$path%");
        }
        if ($cond) {
            $this->getSelect()->where(join(' OR ', $cond));
        }
        return $this;
    }

    /**
     * Add question level filter
     *
     * @access public
     * @param int|string $level
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     * @author Ultimate Module Creator
     */
    public function addLevelFilter($level)
    {
        $this->addFieldToFilter('level', array('lteq' => $level));
        return $this;
    }

    /**
     * Add root question filter
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     */
    public function addRootLevelFilter()
    {
        $this->addFieldToFilter('path', array('neq' => '1'));
        $this->addLevelFilter(1);
        return $this;
    }

    /**
     * Add order field
     *
     * @access public
     * @param string $field
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     */
    public function addOrderField($field)
    {
        $this->setOrder($field, self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Add active question filter
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Resource_Question_Collection
     */
    public function addStatusFilter($status = 1)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }

    /**
     * get questions as array
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     * @author Ultimate Module Creator
     */
    protected function _toOptionArray($valueField='entity_id', $labelField='question', $additional=array())
    {
        $res = array();
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($this as $item) {
            if ($item->getId() == Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
                continue;
            }
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $res[] = $data;
        }
        return $res;
    }

    /**
     * get options hash
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @return array
     * @author Ultimate Module Creator
     */
    protected function _toOptionHash($valueField='entity_id', $labelField='title')
    {
        $res = array();
        foreach ($this as $item) {
            if ($item->getId() == Mage::helper('softwaremedia_wizard/question')->getRootQuestionId()) {
                continue;
            }
            $res[$item->getData($valueField)] = $item->getData($labelField);
        }
        return $res;
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @access public
     * @return Varien_Db_Select
     * @author Ultimate Module Creator
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
}
