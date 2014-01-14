<?php
/**
 * Customer Reward Notification Preferences
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Customer_Preferences extends TBT_Rewards_Block_Customer_Abstract 
{
	
	protected function _prepareLayout() 
	{
		parent::_prepareLayout ();
	}
    
    /*
     * Check if Customer Points Summary email is allowed
     */
    public function showPreferences()
    {
        return Mage::getStoreConfigFlag('rewards/display/allow_points_summary_email');
    }
           
}