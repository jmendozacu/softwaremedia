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
 * Step admin controller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Adminhtml_Campaign_StepController extends SoftwareMedia_Campaign_Controller_Adminhtml_Campaign
{
	
    /**
     * init the step
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Model_Step
     */
    protected function _initStep()
    {
        $stepId  = (int) $this->getRequest()->getParam('id');
        $step    = Mage::getModel('softwaremedia_campaign/step');
        if ($stepId) {
            $step->load($stepId);
        }
        Mage::register('current_step', $step);
        return $step;
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
             ->_title(Mage::helper('softwaremedia_campaign')->__('Steps'));
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
     * edit step - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $stepId    = $this->getRequest()->getParam('id');
        $step      = $this->_initStep();
        if ($stepId && !$step->getId()) {
            $this->_getSession()->addError(
                Mage::helper('softwaremedia_campaign')->__('This step no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getStepData(true);
        if (!empty($data)) {
            $step->setData($data);
        }
        Mage::register('step_data', $step);
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_campaign')->__('Customer Campaigns'))
             ->_title(Mage::helper('softwaremedia_campaign')->__('Steps'));
        if ($step->getId()) {
            $this->_title($step->getName());
        } else {
            $this->_title(Mage::helper('softwaremedia_campaign')->__('Add step'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new step action
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
     * save step - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('step')) {
            try {
                $step = $this->_initStep();
                $step->addData($data);
                $step->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_campaign')->__('Step was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $step->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setStepData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was a problem saving the step.')
                );
                Mage::getSingleton('adminhtml/session')->setStepData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_campaign')->__('Unable to find step to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete step - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $step = Mage::getModel('softwaremedia_campaign/step');
                $step->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_campaign')->__('Step was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error deleting step.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_campaign')->__('Could not find step to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete step - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $stepIds = $this->getRequest()->getParam('step');
        if (!is_array($stepIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_campaign')->__('Please select steps to delete.')
            );
        } else {
            try {
                foreach ($stepIds as $stepId) {
                    $step = Mage::getModel('softwaremedia_campaign/step');
                    $step->setId($stepId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_campaign')->__('Total of %d steps were successfully deleted.', count($stepIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error deleting steps.')
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
        $stepIds = $this->getRequest()->getParam('step');
        if (!is_array($stepIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_campaign')->__('Please select steps.')
            );
        } else {
            try {
                foreach ($stepIds as $stepId) {
                $step = Mage::getSingleton('softwaremedia_campaign/step')->load($stepId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d steps were successfully updated.', count($stepIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error updating steps.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass campaign change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massCampaignIdAction()
    {
        $stepIds = $this->getRequest()->getParam('step');
        if (!is_array($stepIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_campaign')->__('Please select steps.')
            );
        } else {
            try {
                foreach ($stepIds as $stepId) {
                $step = Mage::getSingleton('softwaremedia_campaign/step')->load($stepId)
                    ->setCampaignId($this->getRequest()->getParam('flag_campaign_id'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d steps were successfully updated.', count($stepIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_campaign')->__('There was an error updating steps.')
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
        $fileName   = 'step.csv';
        $content    = $this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_step_grid')
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
        $fileName   = 'step.xls';
        $content    = $this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_step_grid')
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
        $fileName   = 'step.xml';
        $content    = $this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_step_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('system/softwaremedia_campaign/step');
    }
}
