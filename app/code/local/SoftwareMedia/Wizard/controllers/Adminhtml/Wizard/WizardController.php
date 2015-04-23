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
 * Wizard admin controller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Adminhtml_Wizard_WizardController extends SoftwareMedia_Wizard_Controller_Adminhtml_Wizard
{
    /**
     * init the wizard
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Wizard
     */
    protected function _initWizard()
    {
        $wizardId  = (int) $this->getRequest()->getParam('id');
        $wizard    = Mage::getModel('softwaremedia_wizard/wizard');
        if ($wizardId) {
            $wizard->load($wizardId);
        }
        Mage::register('current_wizard', $wizard);
        return $wizard;
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
        $this->_title(Mage::helper('softwaremedia_wizard')->__('Wizard'))
             ->_title(Mage::helper('softwaremedia_wizard')->__('Wizards'));
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
     * edit wizard - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $wizardId    = $this->getRequest()->getParam('id');
        $wizard      = $this->_initWizard();
        if ($wizardId && !$wizard->getId()) {
            $this->_getSession()->addError(
                Mage::helper('softwaremedia_wizard')->__('This wizard no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getWizardData(true);
        if (!empty($data)) {
            $wizard->setData($data);
        }
        Mage::register('wizard_data', $wizard);
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_wizard')->__('Wizard'))
             ->_title(Mage::helper('softwaremedia_wizard')->__('Wizards'));
        if ($wizard->getId()) {
            $this->_title($wizard->getTitle());
        } else {
            $this->_title(Mage::helper('softwaremedia_wizard')->__('Add wizard'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new wizard action
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
     * save wizard - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('wizard')) {
            try {
                $wizard = $this->_initWizard();
                $wizard->addData($data);
                $wizard->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('Wizard was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $wizard->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setWizardData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was a problem saving the wizard.')
                );
                Mage::getSingleton('adminhtml/session')->setWizardData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_wizard')->__('Unable to find wizard to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete wizard - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $wizard = Mage::getModel('softwaremedia_wizard/wizard');
                $wizard->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('Wizard was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error deleting wizard.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_wizard')->__('Could not find wizard to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete wizard - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $wizardIds = $this->getRequest()->getParam('wizard');
        if (!is_array($wizardIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_wizard')->__('Please select wizards to delete.')
            );
        } else {
            try {
                foreach ($wizardIds as $wizardId) {
                    $wizard = Mage::getModel('softwaremedia_wizard/wizard');
                    $wizard->setId($wizardId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('Total of %d wizards were successfully deleted.', count($wizardIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error deleting wizards.')
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
        $wizardIds = $this->getRequest()->getParam('wizard');
        if (!is_array($wizardIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_wizard')->__('Please select wizards.')
            );
        } else {
            try {
                foreach ($wizardIds as $wizardId) {
                $wizard = Mage::getSingleton('softwaremedia_wizard/wizard')->load($wizardId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d wizards were successfully updated.', count($wizardIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error updating wizards.')
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
        $fileName   = 'wizard.csv';
        $content    = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_wizard_grid')
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
        $fileName   = 'wizard.xls';
        $content    = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_wizard_grid')
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
        $fileName   = 'wizard.xml';
        $content    = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_wizard_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('catalog/softwaremedia_wizard/wizard');
    }
}
