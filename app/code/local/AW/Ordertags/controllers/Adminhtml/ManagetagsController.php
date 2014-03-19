<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ordertags
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ordertags_Adminhtml_ManagetagsController extends Mage_Adminhtml_Controller_Action
{
    protected function _displayTitle($data = null, $root = 'Order Tags')
    {
        if (!Mage::helper('ordertags')->magentoLess14()) {
            if ($data) {
                if (!is_array($data)) {
                    $data = array($data);
                }
                foreach ($data as $title) {
                    $this->_title($this->__($title));
                }
                $this->_title($this->__($root));
            } else {
                $this->_title($this->__('Order Tags'))->_title($root);
            }
        }
        return $this;
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales')
        ;
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/ordertags');
    }

    public function indexAction()
    {
        if (!preg_match('/^1.3/', Mage::getVersion())) {
            $this->_title($this->__('Sales'))->_title($this->__('Manage Tags'));
        }
        $this->_initAction()->_displayTitle('Manage Tags')->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('tag_id');
        $model = Mage::getModel('ordertags/managetags')->load($id);

        if ($data = Mage::getSingleton('adminhtml/session')->getFormData()) {
            $model->addData($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }

        if ($model->getId() || $id == 0) {

            if (!$id) {
                $this->_displayTitle('New Tag');
            } else {
                $this->_displayTitle('Edit Tag');
            }

            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

            Mage::register('tag_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('sales');

            $block = $this
                ->getLayout()
                ->createBlock('ordertags/adminhtml_managetags_edit')
                ->setData('action', $this->getUrl('*/ordertags_managetags/save'))
            ;

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true)
                ->setCanLoadRulesJs(true)
            ;

            $this->_addContent($block)
                ->_addLeft($this->getLayout()->createBlock('ordertags/adminhtml_managetags_edit_tabs'))
                ->renderLayout()
            ;
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ordertags')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function windowAction()
    {
        $data = $this->getRequest()->getPost();
        $orderId = $data['aw_ot_order_id'];

        if (isset($data['tag_select'])) {
            $arrayForDB = $data['tag_select'];
            $arrayFromDB = Mage::getResourceModel('ordertags/orderidtotagid')->getArrayByOrderId($orderId);
            $elementsToAddIntoDB = array_diff($arrayForDB, $arrayFromDB);
            $elementsToRemoveFromDB = array_diff($arrayFromDB, $arrayForDB);
            Mage::getResourceModel('ordertags/orderidtotagid')->addIntoDB($orderId, $elementsToAddIntoDB);
        } else {
            $elementsToRemoveFromDB = "*";
        }

        Mage::getResourceModel('ordertags/orderidtotagid')->removeFromDB($orderId, $elementsToRemoveFromDB);
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('filename');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    if (!preg_match('/^[a-z0-9-_. ]+$/i', $_FILES['filename']['name'])) {
                        throw new Exception(
                            Mage::helper('ordertags')->__(
                                "Please specify correct file name. "
                                . "Use only letters 'A–Z a-z' and numbers '0-9' for  file name"
                            )
                        );
                    }

                    $_FILES['filename']['name'] = $uploader->getCorrectFileName($_FILES['filename']['name']);

                    preg_match("/\.([^\.]+)$/", $_FILES['filename']['name'], $matches);

                    $allext = " 'jpg', 'jpeg', 'gif', 'png' ";
                    if (!$uploader->chechAllowedExtension($matches[1])) {
                        throw new Exception(
                            Mage::helper('ordertags')->__(
                                'Wrong type of file, only %s  types allowed', $allext
                            )
                        );
                    }

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . AW_Ordertags_Helper_Config::TAG_FOLDER . DS;
                    $result = $uploader->save($path, $_FILES['filename']['name']);
                    Mage::helper('ordertags')->resizeToThumbnail($result);
                } catch (Exception $e) {
                    Mage::log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('tag_id' => $this->getRequest()->getParam('id')));
                    return $this;
                }

                //this way the image tag with src is saved in DB
                $data['filename'] = AW_Ordertags_Helper_Config::TAG_FOLDER . DS . $_FILES['filename']['name'];
            } else {
                $data['filename'] = $data['filename']['value'];
            }

            $model = Mage::getModel('ordertags/managetags');
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);

            try {
                $model->loadPost($data)->setId($this->getRequest()->getParam('id'));
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ordertags')->__('Item was successfully saved')
                );

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return $this;
                }
                $this->_redirect('*/*/');
                return $this;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('tag_id' => $this->getRequest()->getParam('id')));
                return $this;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ordertags')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
        return $this;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('tag_id') > 0) {
            try {
                $idFromRequest = $this->getRequest()->getParam('tag_id');
                $configUpdate = Mage::getModel('ordertags/configupdate');
                $model = Mage::getModel('ordertags/managetags');

                $configUpdate->updateForTag($idFromRequest);
                $model->setId($idFromRequest)->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('tag_id' => $this->getRequest()->getParam('tag_id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('ordertags/managetags'))
            ->setPrefix('conditions')
        ;

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function massAddTagAction()
    {
        try {
            $orderIds = $this->getRequest()->getParam('order_ids');
            $tagId = $this->getRequest()->getParam('tag_id');

            if ($orderIds && $tagId) {
                $tagToOrderResource = Mage::getResourceModel('ordertags/orderidtotagid');

                foreach ($orderIds as $orderId) {
                    $tagToOrderResource->addIntoDB($orderId, $tagId);
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been updated.', count($orderIds))
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e, $this->__('An error occurred while updating the order(s) tags.')
            );
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    public function massRemoveTagAction()
    {
        try {
            $orderIds = $this->getRequest()->getParam('order_ids');
            $tagId = $this->getRequest()->getParam('tag_id');

            if ($orderIds && $tagId) {
                $tagToOrderResource = Mage::getResourceModel('ordertags/orderidtotagid');

                foreach ($orderIds as $orderId) {
                    $tagToOrderResource->removeFromDB($orderId, $tagId);
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been updated.', count($orderIds))
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e, $this->__('An error occurred while updating the order(s) tags.')
            );
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    public function massResetTagsAction()
    {
        try {
            $orderIds = $this->getRequest()->getParam('order_ids');
            if ($orderIds) {
                $tagToOrderResource = Mage::getResourceModel('ordertags/orderidtotagid');

                foreach ($orderIds as $orderId) {
                    $tagToOrderResource->removeFromDB($orderId, '*');
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been updated.', count($orderIds))
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e, $this->__('An error occurred while updating the order(s) tags.')
            );
        }

        $this->_redirect('adminhtml/sales_order/index');
    }
}