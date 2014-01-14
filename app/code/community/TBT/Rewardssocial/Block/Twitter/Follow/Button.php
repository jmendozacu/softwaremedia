<?php

class TBT_Rewardssocial_Block_Twitter_Follow_Button extends TBT_Rewardssocial_Block_Widget_Abstract
{
    protected $_predictedPoints = null;

    public function _prepareLayout()
    {
        $session = Mage::getSingleton('customer/session');
        $this->customerId = $session->getCustomerId();
        $this->storeTwitterUsername = Mage::getStoreConfig('rewards/twitter/storeTwitterUsername');
        return parent::_prepareLayout();
    }

    public function getHasFollowed()
    {
        $customer = $this->_getRS()->getSessionCustomer();
        if (!$customer->getId()) {
            return false;
        }

        $url = Mage::helper('core/url')->getCurrentUrl();
        return $this->_getValidator()->hasFollowed($customer->getId());
    }

    public function getNotificationBlock()
    {
        return $this->getLayout()->createBlock('core/template')
            ->setTemplate('rewardssocial/twitter/follow/points.phtml')
            ->setPredictedPointsString($this->getPredictedPointsString());
    }

    public function getPredictedPointsString()
    {
        $predictedPoints = $this->getPredictedPoints();
        return (string) Mage::getModel('rewards/points')->set($predictedPoints);
    }

    public function getHasPredictedPoints()
    {
        $predictedPoints = $this->getPredictedPoints();
        // TODO: should check other things too, like limits
        return !empty($predictedPoints) && !$this->getHasFollowed();
    }

    public function getPredictedPoints()
    {
        if ($this->_predictedPoints === null ) {
            $this->_predictedPoints = $this->_getValidator()->getPredictedTwitterFollowPoints();
        }

        return $this->_predictedPoints;
    }

    public function countEnabled()
    {
        $countEnabled = Mage::getStoreConfig('rewards/twitter/showNumPageTweets');
        return $countEnabled;
    }

    public function getFollowProcessingUrl()
    {
        return $this->getUrl('rewardssocial/index/processFollow');
    }

    /**
     * If the is_hidden attribute is set, dont output anything.
     *
     * (overrides parent method)
     */
    protected function _toHtml()
    {
        // this code would prevent the Tweet button from appearing if
        // no Twitter username is specified in the admin panel
		/*if (!Mage::helper('rewardssocial/twitter_tweet')->isTweetingEnabled()) {
		    return "";
		}*/
        return parent::_toHtml();
    }

    protected function _getValidator()
    {
        return Mage::getSingleton('rewardssocial/twitter_follow_validator');
    }
}
