<?php

class TBT_Rewardssocial_Model_Rewardsref_Referral_Observer extends Varien_Object
{
    public function subscribe(Varien_Event_Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            if (!$event) {
                return $this;
            }

            $affiliate = $event->getAffiliate();
            if (!$affiliate) {
                return $this;
            }

            $referral = $event->getReferral();
            if (!$referral) {
                return $this;
            }

            $customer = Mage::getModel('rewardssocial/customer')->load($affiliate->getId());

            $minimumWait = $customer->getTimeUntilNextReferralShareAllowed();
            if($minimumWait > 0) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('rewardssocial')->__('Please wait %s second(s) before liking another page if you want to be rewarded.', $minimumWait));
                return $this;
            }

            if ($customer->isMaxDailyReferralShareReached()) {
                $maxShares = $this->_getMaxReferralSharesPerDay($customer);
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('rewardssocial')->__("You've reached the Facebook like rewards limit for today (%s)", $maxShares));
                return $this;
            }

            $referralShare = Mage::getModel('rewardssocial/referral_share')
                ->setCustomerId($customer->getId())
                ->save();

            $validatorModel = Mage::getModel('rewardssocial/referral_share_validator');
            $validatorModel->initReward($customer->getId(), $referralShare->getId());

            $applicableRules = $validatorModel->getApplicableRules();
            if (count($applicableRules) > 0) {
                // TODO: add to session
                echo "Your points balance will be updated momentarily.";
            }
        } catch (Exception $ex) {
            Mage::helper('rewards')->logException($ex);
        }

        return $this;
    }

    protected function _getMaxReferralSharesPerDay($customer)
    {
        return Mage::helper('rewardssocial/referral_config')->getMaxReferralSharesPerDay($customer->getStore());
    }
}
