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
 * Wizard front contrller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_WizardController extends Mage_Core_Controller_Front_Action
{
	

    /**
      * default action
      *
      * @access public
      * @return void
      * @author Ultimate Module Creator
      */
      
    /*
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if (Mage::helper('softwaremedia_wizard/wizard')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('softwaremedia_wizard')->__('Home'),
                        'link'  => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'wizards',
                    array(
                        'label' => Mage::helper('softwaremedia_wizard')->__('Wizards'),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', Mage::helper('softwaremedia_wizard/wizard')->getWizardsUrl());
        }
        $this->renderLayout();
    }
	*/
    /**
     * init Wizard
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Wizard
     * @author Ultimate Module Creator
     */
    protected function _initWizard()
    {
        $wizardId   = $this->getRequest()->getParam('id', 0);
        $wizard     = Mage::getModel('softwaremedia_wizard/wizard')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($wizardId);
        if (!$wizard->getId()) {
            return false;
        } elseif (!$wizard->getStatus()) {
            return false;
        }
        return $wizard;
    }

    /**
     * view wizard action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function viewAction()
    {
        $wizard = $this->_initWizard();
        if (!$wizard) {
            $this->_forward('no-route');
            return;
        }
        Mage::register('current_wizard', $wizard);
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('wizard-wizard wizard-wizard' . $wizard->getId());
        }
        //if (Mage::helper('softwaremedia_wizard/wizard')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label'    => Mage::helper('softwaremedia_wizard')->__('Home'),
                        'link'     => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'wizard',
                    array(
                        'label' => $wizard->getTitle(),
                        'link'  => '',
                    )
                );
            }
        //}
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
        	$headBlock->setTitle($wizard->getTitle());
            $headBlock->addLinkRel('canonical', $wizard->getWizardUrl());
        }
        $this->renderLayout();
    }
}
