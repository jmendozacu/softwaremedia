<?php
class Autocompleteplus_Autosuggest_Block_Notifications extends Mage_Core_Block_Template
{
    /**
     * @return Autocompleteplus_Autosuggest_Model_Mysql4_Notifications_Collection
     */
    public function getNotifications()
    {
        /** @var Autocompleteplus_Autosuggest_Model_Mysql4_Notifications_Collection $collection */
        $collection = Mage::getModel('autocompleteplus_autosuggest/notifications')->getCollection();
        return $collection->addTypeFilter('alert')->addActiveFilter();
    }
}