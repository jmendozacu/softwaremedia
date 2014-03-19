<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ordertags
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ordertags_Model_Mysql4_Orderidtotagid extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_read = null;
    protected $_write = null;

    /**
     * Name of Order id to tag id DB table
     *
     * @var string
     */
    protected $_orderidtotagidTable;

    /**
     * Initialize resource model
     *
     * Get tablename from config
     */
    protected function _construct()
    {
        $this->_init('ordertags/ordertotag', 'ot_id');
        $this->_orderidtotagidTable = Mage::getSingleton('core/resource')->getTableName("ordertags/ordertotag");
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    public function loadIdsToTable($orderId, $tagId)
    {
    	$tags = $this->getArrayByOrderId($orderId);
    	
    	if (in_array($tagId,$tags))
    		return $this;
    	
    		
        $this->_write->beginTransaction();
        try {
            $data['tag_id'] = $tagId;
            $data['order_id'] = $orderId;
            
            try {
                $this->_write->insert($this->_orderidtotagidTable, $data);
            } catch (Exception $e) {
                Mage::logException("coultn' write" . $e);
            }
            $this->_write->commit();
        } catch (Exception $e) {
            $this->_write->rollBack();
            Mage::logException("rollback" . $e);
        }

        return $this;
    }

    public function getArrayByOrderId($orderId)
    {
        $arrayOfTags = array();
        $select = $this->_read->select()->from($this->_orderidtotagidTable)->where("order_id=?", $orderId);
        $arrayOfTagsFromDb = $this->_read->fetchAll($select);
        foreach ($arrayOfTagsFromDb as $tagValue) {
            $arrayOfTags[] = $tagValue['tag_id'];
        }
        return $arrayOfTags;
    }

    public function addIntoDB($orderId, $elementsToAddIntoDB)
    {
        foreach ((array)$elementsToAddIntoDB as $tagId) {
            $this->loadIdsToTable($orderId, $tagId);
        }
        
    }

    public function removeFromDB($orderId, $elementsToRemoveFromDB)
    {
        $adapter = $this->_write;
        try {
            if ($elementsToRemoveFromDB == "*") {
                $adapter->delete(
                    $this->_orderidtotagidTable,
                    array(
                         'order_id = ?' => $orderId,
                    )
                );
            } else {
                foreach ((array)$elementsToRemoveFromDB as $tagId) {
                    $adapter->delete(
                        $this->_orderidtotagidTable,
                        array(
                             'order_id = ?' => $orderId,
                             'tag_id = ?'   => $tagId,
                        )
                    );
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}