<?php
class Autocompleteplus_Autosuggest_Model_Mysql4_Notifications_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('autocompleteplus_autosuggest/notifications');
    }

    /**
     * @param string $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        $this->getSelect()
            ->where('type=?', $type);
        return $this;
    }

    public function addActiveFilter()
    {
        $this->getSelect()
            ->where('is_active=?', 1);
        return $this;
    }
}