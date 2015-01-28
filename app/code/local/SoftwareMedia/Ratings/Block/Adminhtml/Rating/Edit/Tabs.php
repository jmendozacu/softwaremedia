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
 * Rating admin edit tabs
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Block_Adminhtml_Rating_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rating_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('softwaremedia_ratings')->__('Rating'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Adminhtml_Rating_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_rating',
            array(
                'label'   => Mage::helper('softwaremedia_ratings')->__('Rating'),
                'title'   => Mage::helper('softwaremedia_ratings')->__('Rating'),
                'content' => $this->getLayout()->createBlock(
                    'softwaremedia_ratings/adminhtml_rating_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve rating entity
     *
     * @access public
     * @return SoftwareMedia_Ratings_Model_Rating
     * @author Ultimate Module Creator
     */
    public function getRating()
    {
        return Mage::registry('current_rating');
    }
}
