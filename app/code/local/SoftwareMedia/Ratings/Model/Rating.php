<?php
/**
 * SoftwareMedia_Ratings extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Ratings
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Rating model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Model_Rating extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'softwaremedia_ratings_rating';
    const CACHE_TAG = 'softwaremedia_ratings_rating';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'softwaremedia_ratings_rating';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'rating';

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
        $this->_init('softwaremedia_ratings/rating');
    }

    /**
     * before save rating
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Model_Rating
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
     * save rating relation
     *
     * @access public
     * @return SoftwareMedia_Ratings_Model_Rating
     * @author Ultimate Module Creator
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
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
