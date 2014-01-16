<?php

class TBT_Rewardssocial_Helper_Referral_Config extends Mage_Core_Helper_Abstract
{
    public function getMaxReferralSharesPerDay($store = null)
    {
        return (int) Mage::getStoreConfig('rewards/referral/maxShareRewardsPerDay', $store);
    }

    public function getMinSecondsBetweenShares($store = null)
    {
        return (int) Mage::getStoreConfig('rewards/referral/minSecondsBetweenShares', $store);
    }
}
