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
class SoftwareMedia_Campaign_Model_Adminhtml_Search_Step extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Adminhtml_Search_Step
     * @author Ultimate Module Creator
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('softwaremedia_campaign/step_collection')
            ->addFieldToFilter('name', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $step) {
            $arr[] = array(
                'id'          => 'step/1/'.$step->getId(),
                'type'        => Mage::helper('softwaremedia_campaign')->__('Step'),
                'name'        => $step->getName(),
                'description' => $step->getName(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/campaign_step/edit',
                    array('id'=>$step->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
