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
 * Admin search model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Model_Adminhtml_Search_Campaign extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Adminhtml_Search_Campaign
     * @author Ultimate Module Creator
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('softwaremedia_campaign/campaign_collection')
            ->addFieldToFilter('name', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $campaign) {
            $arr[] = array(
                'id'          => 'campaign/1/'.$campaign->getId(),
                'type'        => Mage::helper('softwaremedia_campaign')->__('Campaign'),
                'name'        => $campaign->getName(),
                'description' => $campaign->getName(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/campaign_campaign/edit',
                    array('id'=>$campaign->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
