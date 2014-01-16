<?php
/**
 * Twitter Helper
 */
class TBT_Rewardssocial_Helper_Twitter_Tweet extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the username of the store's Twitter account, as set in the Admin Panel.
     *
     * @return string
     */
    public function getStoreTwitterUsername()
    {
        return Mage::getStoreConfig('rewards/twitter/storeTwitterUsername');
    }
}
