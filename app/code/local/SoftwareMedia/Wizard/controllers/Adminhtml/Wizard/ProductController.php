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
 * Product admin controller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Adminhtml_Wizard_ProductController extends SoftwareMedia_Wizard_Controller_Adminhtml_Wizard
{
    /**
     * init the product
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Product
     */
    protected function _initProduct()
    {
        $productId  = (int) $this->getRequest()->getParam('id');
        $product    = Mage::getModel('softwaremedia_wizard/product');
        if ($productId) {
            $product->load($productId);
        }
        Mage::register('current_product', $product);
        return $product;
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
             ->_title(Mage::helper('softwaremedia_wizard')->__('Products'));
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
     * edit product - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $productId    = $this->getRequest()->getParam('id');
        $product      = $this->_initProduct();
        if ($productId && !$product->getId()) {
            $this->_getSession()->addError(
                Mage::helper('softwaremedia_wizard')->__('This product no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getProductData(true);
        if (!empty($data)) {
            $product->setData($data);
        }
        Mage::register('product_data', $product);
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_wizard')->__('Wizard'))
             ->_title(Mage::helper('softwaremedia_wizard')->__('Products'));
        if ($product->getId()) {
            $this->_title($product->getName());
        } else {
            $this->_title(Mage::helper('softwaremedia_wizard')->__('Add product'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new product action
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
     * save product - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('product')) {
            try {
                $product = $this->_initProduct();
                $product->addData($data);
                $product->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('Product was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $product->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setProductData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was a problem saving the product.')
                );
                Mage::getSingleton('adminhtml/session')->setProductData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_wizard')->__('Unable to find product to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete product - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $product = Mage::getModel('softwaremedia_wizard/product');
                $product->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('Product was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error deleting product.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('softwaremedia_wizard')->__('Could not find product to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete product - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_wizard')->__('Please select products to delete.')
            );
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getModel('softwaremedia_wizard/product');
                    $product->setId($productId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('Total of %d products were successfully deleted.', count($productIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error deleting products.')
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
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_wizard')->__('Please select products.')
            );
        } else {
            try {
                foreach ($productIds as $productId) {
                $product = Mage::getSingleton('softwaremedia_wizard/product')->load($productId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d products were successfully updated.', count($productIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error updating products.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass question change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massQuestionIdAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('softwaremedia_wizard')->__('Please select products.')
            );
        } else {
            try {
                foreach ($productIds as $productId) {
                $product = Mage::getSingleton('softwaremedia_wizard/product')->load($productId)
                    ->setQuestionId($this->getRequest()->getParam('flag_question_id'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d products were successfully updated.', count($productIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('There was an error updating products.')
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
        $fileName   = 'product.csv';
        $content    = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_product_grid')
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
        $fileName   = 'product.xls';
        $content    = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_product_grid')
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
        $fileName   = 'product.xml';
        $content    = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_product_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('catalog/softwaremedia_wizard/product');
    }
}
