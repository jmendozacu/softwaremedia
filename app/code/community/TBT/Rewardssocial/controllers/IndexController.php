<?php
/**
 * WDCA - Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL:
 *      http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, WDCA is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time WDCA spent
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension.
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2011 WDCA (http://www.wdca.ca)
 */

/**
 * RewardsSocial Index Controller
 *
 * @category   TBT
 * @package    TBT_RewardsSocial
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewardssocial_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() {

        if (Mage::getConfig()->getModuleConfig('TBT_Rewards')->is('active', 'false')) {
            throw new Exception(Mage::helper('rewardssocial')->__("Sweet Tooth must be installed on the server in order to use the Sweet Tooth Social system."));
        }
        die(Mage::helper('rewardssocial')->__("If you're seeing this page it confirms that Sweet Tooth is installed and the Sweet Tooth Social system is ready for use."));

        return $this;
    }

    /**
     * This function is hit after each tweet by an AJAX call
     *
     */
    public function processTweetsAction()
    {
        try {
            $url = $_SERVER['HTTP_REFERER'];

            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                throw new Exception($this->__("You must be logged in for us to reward you for tweeting."), 110);
            }

            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if (!$customer->getId()) {
                throw new Exception($this->__("There was a problem loading customer with ID: {$customer->getId()}."), 20);
            }

            $tweet = Mage::getModel('rewardssocial/twitter_tweet');

            if ($tweet->hasAlreadyTweetedUrl($customer, $url)) {
                throw new Exception($this->__("You've already tweeted about this page."), 120);
            }

            $minimumWait = $tweet->getTimeUntilNextTweetAllowed($customer);
            if($minimumWait > 0) {
                throw new Exception($this->__("Please wait %s second(s) before tweeting another page if you want to be rewarded.", $minimumWait), 130);
            }

            if ($tweet->isMaxDailyTweetsReached($customer)) {
                $maxTweets = $this->_getMaxTweetsPerDay($customer);
                throw new Exception($this->__("You've reached the tweet-rewards limit for today (%s tweets per day)", $maxTweets), 140);
            }

            $tweet->setCustomerId($customer->getId())
                ->setUrl($url)
                ->save();

            if (!$tweet->getId()) {
                // TODO: log these details, output something simpler
                throw new Exception($this->__("TWEET model was not saved for some reason."), 10);
            }

            $validatorModel = Mage::getModel('rewardssocial/twitter_tweet_validator');
            $validatorModel->initReward($customer->getId(), $url);

            $message = $this->__("Thanks for tweeting this page!");
            $predictedPoints = $validatorModel->getPredictedTwitterTweetPoints();
            if (count($predictedPoints) > 0) {
                $pointsString = (string) Mage::getModel('rewards/points')->set($predictedPoints);
                $message = $this->__("You've earned <b>%s</b> for tweeting!", $pointsString);
            }

            $this->_jsonSuccess(array(
                'success' => true,
                'message' => $message
            ));
        } catch (Exception $ex) {
            // log the exception
            Mage::helper('rewards')->logException("There was a problem rewarding customer {$customer->getEmail()} (ID: {$customer->getId()}) for tweeting about a page ({$url}): ".
                $ex->getMessage());

            $message = $this->__('There was a problem trying to reward you for tweeting about this page.<br/>Try again and contact us if you still encounter this issue.');
            if ($ex->getCode() > 100) {
                $message = $ex->getMessage();
            }

            $this->_jsonError(array(
                'success' => false,
                'message' => $message
            ));
        }

        return $this;
    }

    public function processFollowAction()
    {
        try {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                throw new Exception($this->__("You must be logged in for us to reward you for following us on Twitter!"));
            }

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $customer = Mage::getModel('rewardssocial/customer')->load($customerId);

            if ($customer->getIsFollowing()) {
                throw new Exception($this->__("You've already been rewarded for following us!"));
            }

            $customer->setId($customerId)
                ->setIsFollowing(true)
                ->save();

            if (!$customer->getId()) {
                // TODO: log these details, output something simpler
                throw new Exception($this->__("CUSTOMER model was not saved for some reason. Customer ID {$customerId}."));
            }

            $validatorModel = Mage::getModel('rewardssocial/twitter_follow_validator');
            $validatorModel->initReward($customerId);

            $message = $this->__("Thanks for following us!");
            $predictedPoints = $validatorModel->getPredictedTwitterFollowPoints();
            if (count($predictedPoints) > 0) {
                $pointsString = (string) Mage::getModel('rewards/points')->set($predictedPoints);
                $message = $this->__("You've earned <b>%s</b> for following us!", $pointsString);
            }

            $this->_jsonSuccess(array(
                'success' => true,
                'message' => $message
            ));
        } catch (Exception $ex) {
            $this->_jsonError(array(
                'success' => false,
                'message' => $ex->getMessage()
            ));
        }

        return $this;
    }

    public function referralShareAction()
    {
        try {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                throw new Exception($this->__("You must be logged in to share your referral link!"), 110);
            }

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $customer = Mage::getModel('rewardssocial/customer')->load($customerId);
            if (!$customer->getId()) {
                throw new Exception($this->__("There was a problem loading customer with ID: {$customerId}."), 20);
            }

            $minimumWait = $customer->getTimeUntilNextReferralShareAllowed();
            if($minimumWait > 0) {
                throw new Exception($this->__("Please wait %s second(s) before sharing your referral link again, if you want to be rewarded.", $minimumWait), 120);
            }

            if ($customer->isMaxDailyReferralShareReached()) {
                $maxTweets = $this->_getMaxReferralSharesPerDay($customer);
                throw new Exception($this->__("You've reached the rewards limit for today for sharing your referral link (%s shares per day)", $maxTweets), 130);
            }

            $referralShare = Mage::getModel('rewardssocial/referral_share')
                ->setCustomerId($customerId)
                ->save();

            if (!$referralShare->getId()) {
                // TODO: log these details, output something simpler
                throw new Exception($this->__("REFERRAL SHARE model was not saved for some reason."), 10);
            }

            $validatorModel = Mage::getModel('rewardssocial/referral_share_validator');
            $validatorModel->initReward($customerId, $referralShare->getId());

            $message = $this->__("Thanks for sharing your referral link!");
            $predictedPoints = $validatorModel->getPredictedPoints();
            if (count($predictedPoints) > 0) {
                $pointsString = (string) Mage::getModel('rewards/points')->set($predictedPoints);
                $message = $this->__("You've earned <b>%s</b> for sharing your referral link!", $pointsString);
            }

            $this->_jsonSuccess(array(
                'success' => true,
                'message' => $message
            ));
        } catch (Exception $ex) {
            // log the exception
            Mage::helper('rewards')->logException("There was a problem rewarding customer {$customer->getEmail()} (ID: {$customerId}) for sharing his referral link: ".
                $ex->getMessage());

            $message = $this->__('There was a problem trying to reward you for sharing your referral link.<br/>Try again and contact us if you still encounter this issue.');
            if ($ex->getCode() > 100) {
                $message = $ex->getMessage();
            }

            $this->_jsonError(array(
                'success' => false,
                'message' => $message
            ));
        }

        return $this;
    }

    public function processPinAction()
    {
        try {
            $thisUrl = $_SERVER['HTTP_REFERER'];
            $hostname = $_SERVER['HTTP_HOST'];
            $pinterestUsername = $this->getRequest()->getParam('username');

            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                throw new Exception($this->__("You must be logged in for us to reward you for pinning."), 110);
            }

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $customer = Mage::getModel('rewardssocial/customer')->load($customerId);

            if (!$customer->getId()) {
                throw new Exception($this->__("There was a problem loading customer with ID: {$customerId}."), 20);
            }

            if ($pinterestUsername && !$customer->getPinterestUsername()) {
                $customer->setPinterestUsername($pinterestUsername);
            }

            $pinterestUsername = $customer->getPinterestUsername();
            if (!$pinterestUsername) {
                throw new Exception($this->__("You haven't entered your Pinterest username, so we can't reward you for pinning."), 120);
            }

            $pin = Mage::getModel('rewardssocial/pinterest_pin');
            if ($pin->hasAlreadyPinnedUrl($customer, $thisUrl)) {
                throw new Exception($this->__("You've already been rewarded for pinning this page."), 130);
            }

            $minimumWait = $pin->getTimeUntilNextPinAllowed($customer);
            if($minimumWait > 0) {
                throw new Exception($this->__('Please wait %s second(s) before pinning another page if you want to be rewarded.', $minimumWait), 140);
            }

            if ($pin->isMaxDailyPinsReached($customer)) {
                $maxPins = $this->_getMaxPinsPerDay($customer);
                throw new Exception($this->__("You've reached the Pinterest rewards limit for today (%s pins per day)", $maxPins), 150);
            }

            $pinterest = Mage::getModel('tbtcommon/biglight')->getPinterestActivity($pinterestUsername);
            if (!isset($pinterest['body']) || !is_array($pinterest['body'])) {
                throw new Exception($this->__("The username you entered doesn't exist or we are unable to check it at this time."), 160);
            }

            $foundNewPin = false;
            foreach ($pinterest['body'] as $remotePin) {
                if ($remotePin['attrib'] != $hostname) {
                    continue;
                }

                $localPin = Mage::getModel('rewardssocial/pinterest_pin');
                if ($localPin->hasAlreadyPinnedUrl($customer, $remotePin['pinned_link'])) {
                    continue;
                }

                if ($remotePin['pinned_link'] != $thisUrl) {
                    continue;
                }

                $localPin->setCustomerId($customer->getId())
                    ->setPinterestUrl($remotePin['href'])
                    ->setPinnedUrl($remotePin['pinned_link'])
                    ->save();


                if (!$localPin->getId()) {
                    throw new Exception($this->__("PIN model was not saved for some reason."), 10);
                }

                $validatorModel = Mage::getModel('rewardssocial/pinterest_pin_validator');
                $validatorModel->initReward($customer->getId(), $localPin->getId());

                $foundNewPin = true;
            }

            if (!$foundNewPin) {
                throw new Exception($this->__("Please pin the product on Pinterest first and then reclaim your reward here."), 170);
            }

            // Found a pin, so let's save the customer's Pinterest username.
            $customer->setId($customerId)
                ->save();

            $message = $this->__("Thanks for pinning this page!");
            $predictedPoints = $validatorModel->getPredictedPinterestPinPoints();
            if (count($predictedPoints) > 0) {
                $pointsString = (string) Mage::getModel('rewards/points')->set($predictedPoints);
                $message = $this->__("You've earned <b>%s</b> for pinning!", $pointsString);
            }

            $this->_jsonSuccess(array(
                'success' => true,
                'message' => $message
            ));
        } catch (Exception $ex) {
            // log the exception
            Mage::helper('rewards')->logException("There was a problem rewarding customer {$customer->getEmail()} (ID: {$customerId}) for pinning a product ({$thisUrl}) on Pinterest: ".
                $ex->getMessage());

            $message = $this->__('There was a problem trying to reward your pinterest pin.<br/>Try again and contact us if you still encounter this issue.');
            if ($ex->getCode() > 100) {
                $message = $ex->getMessage();
            }

            $this->_jsonError(array(
                'success' => false,
                'message' => $message
            ));
        }

        return $this;
    }

    /**
     * This function is hit after each tweet by an AJAX call
     *
     */
    public function processPlusOneAction()
    {
        try {
            $url = $_SERVER['HTTP_REFERER'];

            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                throw new Exception($this->__("You must be logged in for us to reward you for +1'ing a page!"), 110);
            }

            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if (!$customer->getId()) {
                throw new Exception($this->__("There was a problem loading customer with ID: {$customer->getId()}."), 20);
            }

            $plusone = Mage::getModel('rewardssocial/google_plusOne');

            if ($plusone->hasAlreadyPlusOnedUrl($customer, $url)) {
                throw new Exception($this->__("You've already +1'd this page."), 120);
            }

            $minimumWait = $plusone->getTimeUntilNextPlusOneAllowed($customer);
            if($minimumWait > 0) {
                throw new Exception($this->__("Please wait %s second(s) before +1'ing another page if you want to be rewarded.", $minimumWait), 130);
            }

            if ($plusone->isMaxDailyPlusOnesReached($customer)) {
                $maxTweets = $this->_getMaxPlusOnesPerDay($customer);
                throw new Exception($this->__("You've reached the Google+ rewards limit for today (%s +1's per day)", $maxTweets), 140);
            }

            $plusone->setCustomerId($customer->getId())
                ->setUrl($url)
                ->save();

            if (!$plusone->getId()) {
                // TODO: log these details, output something simpler
                throw new Exception($this->__("PLUSONE model was not saved for some reason."), 10);
            }

            $validatorModel = Mage::getModel('rewardssocial/google_plusOne_validator');
            $validatorModel->initReward($customer->getId(), $url);

            $message = $this->__("Thanks for +1'ing this page!");
            $predictedPoints = $validatorModel->getPredictedGooglePlusOnePoints();
            if (count($predictedPoints) > 0) {
                $pointsString = (string) Mage::getModel('rewards/points')->set($predictedPoints);
                $message = $this->__("You've earned <b>%s</b> for +1'ing this page!", $pointsString);
            }

            $this->_jsonSuccess(array(
                'success' => true,
                'message' => $message
            ));
        } catch (Exception $ex) {
            // log the exception
            Mage::helper('rewards')->logException("There was a problem rewarding customer {$customer->getEmail()} (ID: {$customer->getId()}) for +1'ing this page ({$url}) on Google+: ".
                $ex->getMessage());

            $message = $this->__('There was a problem trying to reward youfor +1\'ing this page.<br/>Try again and contact us if you still encounter this issue.');
            if ($ex->getCode() > 100) {
                $message = $ex->getMessage();
            }

            $this->_jsonError(array(
                'success' => false,
                'message' => $message
            ));
        }

        return $this;
    }

    protected function _getMaxTweetsPerDay($customer)
    {
        return Mage::helper('rewardssocial/twitter_config')->getMaxTweetRewardsPerDay($customer->getStore());
    }

    protected function _getMaxPinsPerDay($customer)
    {
        return Mage::helper('rewardssocial/pinterest_config')->getMaxPinRewardsPerDay($customer->getStore());
    }

    protected function _getMaxReferralSharesPerDay($customer)
    {
        return Mage::helper('rewardssocial/twitter_config')->getMaxReferralSharesPerDay($customer->getStore());
    }

    protected function _getMaxPlusOnesPerDay($customer)
    {
        return Mage::helper('rewardssocial/google_config')->getMaxPlusOneRewardsPerDay($customer->getStore());
    }

    protected function _jsonSuccess($response)
    {
        $this->getResponse()->setBody(json_encode($response));
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        return $this;
    }

    protected function _jsonError($response)
    {
        return $this->_jsonSuccess($response);
    }
}
