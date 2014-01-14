<?php

class TBT_Rewardssocial_Block_Pinterest_Pin_Button extends TBT_Rewardssocial_Block_Widget_Abstract
{
    protected $_predictedPoints = null;
    protected $_customer = null;

    public function _prepareLayout()
    {
        $modalBlock = $this->getLayout()->createBlock('core/template')
            ->setTemplate('rewardssocial/pinterest/pin/modal.phtml');
        $this->setChild('modal', $modalBlock);

        if (!Mage::helper('rewardssocial/pinterest_config')->isPinningEnabled()) {
            $this->setIsHidden(true);
        }

        return parent::_prepareLayout();
    }

    public function getHasPinned()
    {
        $customer = $this->_getRS()->getSessionCustomer();
        if (!$customer->getId()) {
            return false;
        }

        $url = Mage::helper('core/url')->getCurrentUrl();
        return $this->_getValidator()->hasPinned($customer->getId(), $url);
    }

    public function getNotificationBlock()
    {
        return $this->getLayout()->createBlock('core/template')
            ->setTemplate('rewardssocial/pinterest/pin/points.phtml')
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
        return !empty($predictedPoints) && !$this->getHasPinned();
    }

    public function getPredictedPoints()
    {
        if ($this->_predictedPoints === null ) {
            $this->_predictedPoints = $this->_getValidator()->getPredictedPinterestPinPoints();
        }

        return $this->_predictedPoints;
    }

    public function countEnabled()
    {
        $countEnabled = Mage::getStoreConfig('rewards/twitter/showNumPageTweets');
        return $countEnabled;
    }

    public function getPinProcessingUri()
    {
        return $this->getUrl('rewardssocial/index/processPin');
    }

    public function getCustomerPinterestUsername()
    {
        return $this->getCustomer()->getPinterestUsername();
    }

    public function getRequestUri()
    {
        // We must use original request because $this->getRequest() contains the controller/action it mapped to.
        $request = $this->getRequest()->getOriginalRequest();

        $scheme = $request->getScheme();
        $domain = $request->getHttpHost();
        $requestPath = $request->getRequestUri();

        return $scheme . "://" . $domain . $requestPath;
    }

    public function getRequestUriEncoded()
    {
        return urlencode($this->getRequestUri());
    }

    public function getPinnableMediaUri()
    {
        // TODO: maybe this should be made non-product-page-specific at some point
        $product = Mage::registry('product');
        return $this->helper('catalog/image')->init($product, 'image');
    }

    public function getPinnableMediaUriEncoded()
    {
        return urlencode($this->getPinnableMediaUri());
    }

    public function getCustomer()
    {
        if ($this->_customer === null) {
            $customerId = $this->_getSession()->getCustomer()->getId();
            $this->_customer = Mage::getModel('rewardssocial/customer')->load($customerId);
            $this->_customer->setId($customerId);
        }

        return $this->_customer;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _getValidator()
    {
        return Mage::getSingleton('rewardssocial/pinterest_pin_validator');
    }
}
