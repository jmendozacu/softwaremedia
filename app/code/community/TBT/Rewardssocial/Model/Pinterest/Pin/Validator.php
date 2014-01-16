<?php

class TBT_Rewardssocial_Model_Pinterest_Pin_Validator extends TBT_Rewards_Model_Special_Validator
{
    /**
     * Loops through each Special rule. If the rule applies and the customer didn't
     * already earn points for this tweet, then create (a) new points transfer(s) for the tweet.
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    public function initReward($customerId, $pinId)
    {
        try {
            $ruleCollection = $this->getApplicableRulesOnPinterestPin();
            $count = count($ruleCollection);

            $this->_doTransfer($ruleCollection, $customerId, $pinId);

        } catch (Exception $e) {
            Mage::helper('rewards')->logException($e);
            throw new Exception(
                Mage::helper('rewardssocial')->__("Could not reward you for your pin."),
                null, $e
            );
        }

        return $this;
    }

    public function hasPinned($customerId, $url)
    {
        return Mage::getModel('rewardssocial/pinterest_pin')->hasAlreadyPinnedUrl($customerId, $url);
    }

    /**
     * Goes through an already validated rule collection and transfers rule points to the customer specified
     * with the tweet model as the reference.
     * @param array(TBT_Rewards_Model_Special) $ruleCollection
     * @param TBT_Rewards_Model_Customer $customer
     * @param TBT_Rewardssocial_Model_Twitter_Tweet $tweetModel
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    protected function _doTransfer($ruleCollection, $customerId, $pinId)
    {
        foreach ($ruleCollection as $rule) {
            if (!$rule->getId()) {
                continue;
            }

            $transfer = Mage::getModel('rewardssocial/pinterest_pin_transfer');
            $is_transfer_successful = $transfer->create(
                    $customerId,
                    $pinId,
                    $rule
            );

            if (!$is_transfer_successful) {
                throw new Exception("Failed to reward for pin.");
            }
        }

        return $this;
    }

    /**
     * Returns all rules that apply wehn a customer tweets something on twitter
     * @return array(TBT_Rewards_Model_Special)
     */
    public function getApplicableRulesOnPinterestPin()
    {
        return $this->getApplicableRules(
                TBT_Rewardssocial_Model_Pinterest_Pin_Special_Config::ACTION_CODE
        );
    }


    /**
     * Returns an array outlining the number of points they will receive for liking the item
     *
     * @return array
     */
    public function getPredictedPinterestPinPoints($page=null)
    {

        Varien_Profiler::start("TBT_Rewardssocial:: Predict Twitter Tweet Points");
        $ruleCollection = $this->getApplicableRulesOnPinterestPin();

        $predictArray = array();
        foreach ($ruleCollection as $rule) {
            // TODO: shoud this be += ? I think so.
            // ksteffen: I think so, too. Changed code to do so.
            $currencyId = $rule->getPointsCurrencyId();
            if (!isset($predictArray[$currencyId])) {
                $predictArray[$currencyId] = 0;
            }
            $predictArray[$currencyId] += $rule->getPointsAmount();
        }

        Varien_Profiler::stop("TBT_Rewardssocial:: Predict Twitter Tweet Points");
        return $predictArray;
    }

}
?>
