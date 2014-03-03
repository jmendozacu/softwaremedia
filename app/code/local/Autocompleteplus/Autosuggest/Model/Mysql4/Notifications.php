<?php
class Autocompleteplus_Autosuggest_Model_Mysql4_Notifications extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('autocompleteplus_autosuggest/notifications', 'notification_id');
    }

    /**
     * @param array $notifications
     */
    public function addNotifications($notifications)
    {
        $write = $this->_getWriteAdapter();
        foreach ($notifications as $item) {
            $select = $write->select()
                ->from($this->getMainTable())
                ->where('type=?', $item['type'])
                ->where('timestamp=?', $item['timestamp']);
            $row = $write->fetchRow($select);
            if (!$row) {
                $write->insert($this->getMainTable(), $item);
            }
        }
    }
}