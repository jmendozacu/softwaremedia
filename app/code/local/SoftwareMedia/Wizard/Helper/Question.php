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
 * Question helper
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Helper_Question extends Mage_Core_Helper_Abstract
{

    /**
     * get the url to the questions list page
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getQuestionsUrl()
    {
        if ($listKey = Mage::getStoreConfig('softwaremedia_wizard/question/url_rewrite_list')) {
            return Mage::getUrl('', array('_direct'=>$listKey));
        }
        return Mage::getUrl('softwaremedia_wizard/question/index');
    }

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('softwaremedia_wizard/question/breadcrumbs');
    }
    const QUESTION_ROOT_ID = 1;
    /**
     * get the root id
     *
     * @access public
     * @return int
     * @author Ultimate Module Creator
     */
    public function getRootQuestionId()
    {
        return self::QUESTION_ROOT_ID;
    }
}
