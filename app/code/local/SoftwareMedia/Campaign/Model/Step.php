<?php
/**
 * SoftwareMedia_Campaign extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Campaign
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Step model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Model_Step extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'softwaremedia_campaign_step';
    const CACHE_TAG = 'softwaremedia_campaign_step';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'softwaremedia_campaign_step';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'step';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('softwaremedia_campaign/step');
    }

    /**
     * before save step
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Model_Step
     * @author Ultimate Module Creator
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save step relation
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Step
     * @author Ultimate Module Creator
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Retrieve parent 
     *
     * @access public
     * @return null|SoftwareMedia_Campaign_Model_Campaign
     * @author Ultimate Module Creator
     */
    public function getParentCampaign()
    {
        if (!$this->hasData('_parent_campaign')) {
            if (!$this->getCampaignId()) {
                return null;
            } else {
                $campaign = Mage::getModel('softwaremedia_campaign/campaign')
                    ->load($this->getCampaignId());
                if ($campaign->getId()) {
                    $this->setData('_parent_campaign', $campaign);
                } else {
                    $this->setData('_parent_campaign', null);
                }
            }
        }
        return $this->getData('_parent_campaign');
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
