<?php

class TBT_Rewardssocial_Model_Twitter_Follow_Validator extends TBT_Rewards_Model_Special_Validator
{
    /**
     * Loops through each Special rule. If the rule applies and the customer didn't
     * already earn points for this tweet, then create (a) new points transfer(s) for the tweet.
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    public function initReward($customerId, $twitterUserId = null)
    {
        try {
            $customer = Mage::getModel('rewardssocial/customer')->load($customerId);

            $ruleCollection = $this->getApplicableRulesOnTwitterFollow();
            $count = count($ruleCollection);

            $this->_transferFollowPoints($ruleCollection, $customerId, $customer);

        } catch (Exception $e) {
            Mage::helper('rewards')->logException($e);
            throw new Exception(
                Mage::helper('rewardssocial')->__("Could not reward you for your Twitter follow."),
                null, $e
            );
        }

        return $this;
    }

    /**
     * Goes through an already validated rule collection and transfers rule points to the customer specified
     * with the tweet model as the reference.
     * @param array(TBT_Rewards_Model_Special) $ruleCollection
     * @param TBT_Rewards_Model_Customer $customer
     * @param TBT_Rewardssocial_Model_Twitter_Tweet $tweetModel
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    protected function _transferFollowPoints($ruleCollection, $customerId, $customer)
    {
        foreach ($ruleCollection as $rule) {
            if (!$rule->getId()) {
                continue;
            }

            try {
                $transfer = Mage::getModel('rewardssocial/twitter_follow_transfer');
                $is_transfer_successful = $transfer->create($customerId, $rule);

                if ($is_transfer_successful) {
                    // TODO: should not add this to the session since it happens on ajax
                    $points_for_liking = Mage::getModel('rewards/points')->set($rule);
                    $message = Mage::helper('rewardssocial')->__(
                            'You received <b>%s</b> for following us on Twitter.',
                            $points_for_liking
                    );
                    Mage::getSingleton('core/session')->addSuccess($message);

                }
            } catch (Exception $ex) {
                Mage::helper('rewards')->logException($ex);
                Mage::getSingleton('core/session')->addError($ex->getMessage());
            }
        }

        return $this;
    }

    /**
     * Returns all rules that apply wehn a customer tweets something on twitter
     * @return array(TBT_Rewards_Model_Special)
     */
    public function getApplicableRulesOnTwitterFollow()
    {
        return $this->getApplicableRules(
                TBT_Rewardssocial_Model_Twitter_Follow_Special_Config::ACTION_CODE
        );
    }

    /**
     * Returns an array outlining the number of points they will receive for liking the item
     *
     * @return array
     */
    public function getPredictedTwitterFollowPoints()
    {
        Varien_Profiler::start("TBT_Rewardssocial:: Predict Twitter Follow Points");
        $ruleCollection = $this->getApplicableRulesOnTwitterFollow();

        $predictArray = array();
        foreach ($ruleCollection as $rule) {
            $currencyId = $rule->getPointsCurrencyId();
            if (!isset($predictArray[$currencyId])) {
                $predictArray[$currencyId] = 0;
            }
            $predictArray[$currencyId] += $rule->getPointsAmount();
        }

        Varien_Profiler::stop("TBT_Rewardssocial:: Predict Twitter Follow Points");
        return $predictArray;
    }
}
