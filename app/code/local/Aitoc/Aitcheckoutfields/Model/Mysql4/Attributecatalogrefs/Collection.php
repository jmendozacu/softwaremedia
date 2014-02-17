<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Model_Mysql4_Attributecategoryrefs_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
    protected $_sCRAttrTable = 'aitoc_custom_attribute_cat_refs';    
    
    public function _construct()
    {
        $this->_sCRAttrTable    = Mage::getSingleton('core/resource')->getTableName('aitoc_custom_attribute_cat_refs');
        $this->_init('aitcheckoutfields/attributecategoryrefs');
    }    
    
    public function getRefs($iAttributeId, $sType)
    {

        $oDb = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $oDb->select()
            ->from(array('c' => $this->_sCRAttrTable), array('value'))
            ->where('c.attribute_id=?', (int)$iAttributeId)
            ->where('c.type=?',$sType);

        return $oDb->fetchCol($select);
    }
    
    
    public function saveRefs($iAttributeId, $sType,$aValues)
    {
       $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
       $this->deleteRefs($iAttributeId, $sType);
         
        
        foreach ($aValues as $iValue)
        {
            if($iValue)
            {
                $aDBInfo = array
                (
                    'attribute_id'  => $iAttributeId,
                    'type' => $sType,
                    'value'     => $iValue,
                );

                $oDb->insert($this->_sCRAttrTable, $aDBInfo);
            }
        }
        return $this;
    }
    
    public function deleteRefs($iAttributeId, $sType)
    {
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $oDb->delete($this->_sCRAttrTable, array('attribute_id = ?' => $iAttributeId, 'type = ?'=> $sType));
        return $this;
    }        
    
}