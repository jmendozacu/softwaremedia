<?php
/**
 * SoftwareMedia_Wizard extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Wizard
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Product model
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Model_Product extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'softwaremedia_wizard_product';
    const CACHE_TAG = 'softwaremedia_wizard_product';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'softwaremedia_wizard_product';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'product';

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
        $this->_init('softwaremedia_wizard/product');
    }

    /**
     * before save product
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Product
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

	public function loadProduct() {
		$product = Mage::getModel('catalog/product')>setStoreId(1)->loadByAttribute('sku',$this->getSku());
		return $product;
	}
    /**
     * save product relation
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Product
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
     * @return null|SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    public function getParentQuestion()
    {
        if (!$this->hasData('_parent_question')) {
            if (!$this->getQuestionId()) {
                return null;
            } else {
                $question = Mage::getModel('softwaremedia_wizard/question')
                    ->load($this->getQuestionId());
                if ($question->getId()) {
                    $this->setData('_parent_question', $question);
                } else {
                    $this->setData('_parent_question', null);
                }
            }
        }
        return $this->getData('_parent_question');
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
