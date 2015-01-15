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
 * Campaign model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Model_Campaign extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'softwaremedia_campaign_campaign';
    const CACHE_TAG = 'softwaremedia_campaign_campaign';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'softwaremedia_campaign_campaign';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'campaign';

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
        $this->_init('softwaremedia_campaign/campaign');
    }

    /**
     * before save campaign
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Model_Campaign
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
     * save campaign relation
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Campaign
     * @author Ultimate Module Creator
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Retrieve  collection
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Step_Collection
     * @author Ultimate Module Creator
     */
    public function getSelectedStepsCollection()
    {
        if (!$this->hasData('_step_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('softwaremedia_campaign/step_collection')
                        ->addFieldToFilter('campaign_id', $this->getId());
                $this->setData('_step_collection', $collection);
            }
        }
        return $this->getData('_step_collection');
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
