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
 * Rating admin controller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Adminhtml_Ratings_RatingController extends SoftwareMedia_Ratings_Controller_Adminhtml_Ratings
{
	protected function _isAllowed()
    {
        return true;
    }
    /**
     * init the rating
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Model_Rating
     */
    protected function _initRating()
    {
        $ratingId  = (int) $this->getRequest()->getParam('id');
        $rating    = Mage::getModel('softwaremedia_ratings/rating');
        if ($ratingId) {
            $rating->load($ratingId);
        }
        Mage::register('current_rating', $rating);
        return $rating;
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
        $this->_title(Mage::helper('softwaremedia_ratings')->__('Ratings'))
             ->_title(Mage::helper('softwaremedia_ratings')->__('Ratings'));
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
     * edit rating - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $ratingId    = $this->getRequest()->getParam('id');
        $rating      = $this->_initRating();
        if ($ratingId && !$rating->getId()) {
            $this->_getSession()->addError(
                Mage::helper('softwaremedia_ratings')->__('This rating no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getRatingData(true);
        if (!empty($data)) {
            $rating->setData($data);
        }
        Mage::register('rating_data', $rating);
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_ratings')->__('Ratings'))
             ->_title(Mage::helper('softwaremedia_ratings')->__('Ratings'));
        if ($rating->getId()) {
            $this->_title($rating->getUserId());
        } else {
            $this->_title(Mage::helper('softwaremedia_ratings')->__('Add rating'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new rating action
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
     * save rating - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('rating')) {
            try {
                $rating = $this->_initRating();
                $rating->addData($data);
                $rating->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_ratings')->__('Rating was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $rating->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setRatingData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_ratings')->__('There was a problem saving the rating.')
                );
                Mage::getSingleton('adminhtml/session')->setRatingData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_ratings')->__('Unable to find rating to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete rating - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $rating = Mage::getModel('softwaremedia_ratings/rating');
                $rating->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_ratings')->__('Rating was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_ratings')->__('There was an error deleting rating.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_ratings')->__('Could not find rating to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete rating - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $ratingIds = $this->getRequest()->getParam('rating');
        if (!is_array($ratingIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_ratings')->__('Please select ratings to delete.')
            );
        } else {
            try {
                foreach ($ratingIds as $ratingId) {
                    $rating = Mage::getModel('softwaremedia_ratings/rating');
                    $rating->setId($ratingId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_ratings')->__('Total of %d ratings were successfully deleted.', count($ratingIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_ratings')->__('There was an error deleting ratings.')
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
        $ratingIds = $this->getRequest()->getParam('rating');
        if (!is_array($ratingIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_ratings')->__('Please select ratings.')
            );
        } else {
            try {
                foreach ($ratingIds as $ratingId) {
                $rating = Mage::getSingleton('softwaremedia_ratings/rating')->load($ratingId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d ratings were successfully updated.', count($ratingIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_ratings')->__('There was an error updating ratings.')
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
        $fileName   = 'rating.csv';
        $content    = $this->getLayout()->createBlock('softwaremedia_ratings/adminhtml_rating_grid')
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
        $fileName   = 'rating.xls';
        $content    = $this->getLayout()->createBlock('softwaremedia_ratings/adminhtml_rating_grid')
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
        $fileName   = 'rating.xml';
        $content    = $this->getLayout()->createBlock('softwaremedia_ratings/adminhtml_rating_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('customer/softwaremedia_ratings/rating');
    }
}
