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
 * Campaign admin controller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Adminhtml_Campaign_CampaignController extends SoftwareMedia_Campaign_Controller_Adminhtml_Campaign
{
    /**
     * init the campaign
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Model_Campaign
     */
    protected function _initCampaign()
    {
        $campaignId  = (int) $this->getRequest()->getParam('id');
        $campaign    = Mage::getModel('softwaremedia_campaign/campaign');
        if ($campaignId) {
            $campaign->load($campaignId);
        }
        Mage::register('current_campaign', $campaign);
        return $campaign;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_campaign')->__('Customer Campaigns'))
             ->_title(Mage::helper('softwaremedia_campaign')->__('Campaigns'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit campaign - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $campaignId    = $this->getRequest()->getParam('id');
        $campaign      = $this->_initCampaign();
        if ($campaignId && !$campaign->getId()) {
            $this->_getSession()->addError(
                Mage::helper('softwaremedia_campaign')->__('This campaign no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getCampaignData(true);
        if (!empty($data)) {
            $campaign->setData($data);
        }
        Mage::register('campaign_data', $campaign);
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_campaign')->__('Customer Campaigns'))
             ->_title(Mage::helper('softwaremedia_campaign')->__('Campaigns'));
        if ($campaign->getId()) {
            $this->_title($campaign->getName());
        } else {
            $this->_title(Mage::helper('softwaremedia_campaign')->__('Add campaign'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new campaign action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save campaign - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('campaign')) {
            try {
                $campaign = $this->_initCampaign();
                $campaign->addData($data);
                $campaign->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_campaign')->__('Campaign was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $campaign->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was a problem saving the campaign.')
                );
                Mage::getSingleton('adminhtml/session')->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_campaign')->__('Unable to find campaign to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete campaign - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $campaign = Mage::getModel('softwaremedia_campaign/campaign');
                $campaign->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_campaign')->__('Campaign was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error deleting campaign.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_campaign')->__('Could not find campaign to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete campaign - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $campaignIds = $this->getRequest()->getParam('campaign');
        if (!is_array($campaignIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_campaign')->__('Please select campaigns to delete.')
            );
        } else {
            try {
                foreach ($campaignIds as $campaignId) {
                    $campaign = Mage::getModel('softwaremedia_campaign/campaign');
                    $campaign->setId($campaignId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_campaign')->__('Total of %d campaigns were successfully deleted.', count($campaignIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error deleting campaigns.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massStatusAction()
    {
        $campaignIds = $this->getRequest()->getParam('campaign');
        if (!is_array($campaignIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_campaign')->__('Please select campaigns.')
            );
        } else {
            try {
                foreach ($campaignIds as $campaignId) {
                $campaign = Mage::getSingleton('softwaremedia_campaign/campaign')->load($campaignId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d campaigns were successfully updated.', count($campaignIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error updating campaigns.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportCsvAction()
    {
        $fileName   = 'campaign.csv';
        $content    = $this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_campaign_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportExcelAction()
    {
        $fileName   = 'campaign.xls';
        $content    = $this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_campaign_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportXmlAction()
    {
        $fileName   = 'campaign.xml';
        $content    = $this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_campaign_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Ultimate Module Creator
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/softwaremedia_campaign/campaign');
    }
}
