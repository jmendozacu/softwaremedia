<?php

class TBT_Rewardssocial_Block_Referral_Share_Rewards extends TBT_Rewardssocial_Block_Abstract
{
    public function _toHtml()
    {
        if (Mage::helper('rewardssocial/referral_config')->isShareButtonEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }
}