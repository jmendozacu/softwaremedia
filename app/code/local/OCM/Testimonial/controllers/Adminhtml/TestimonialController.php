<?php
class OCM_Testimonial_Adminhtml_TestimonialController extends Mage_Adminhtml_Controller_Action{

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function newAction(){
        $this->_forward('edit');
    }
    public function editAction(){
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ocm_testimonial/testimonial');
        if($id){
            $model->load((int)$id);
            if($model->getId()){
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if($data){
                    $model->setData($data)->setId($id);
                }
            }else{
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ocm_testimonial')->__('Message does not exist'));
                $this->_redirect('*/*/');
            }
        }
        Mage::register('message_data',$model);
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()
            ->createBlock('ocm_testimonial/adminhtml_testimonial_edit'))
            ->_addLeft($this->getLayout()
                ->createBlock('ocm_testimonial/adminhtml_testimonial_edit_tabs')
        );
        $this->renderLayout();
    }
    public function saveAction(){
        if($data = $this->getRequest()->getPost()){
            $model = Mage::getModel('ocm_testimonial/testimonial');
            $id = $this->getRequest()->getParam('id');
            if($id){
                $model->load($id);
            }
            $model->setUserName($data['user_name'])
                ->setDate($data['date'])
                ->setPublic($data['public'])
                ->setMessage($data['message'])
            ;
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('ocm_testimonial')->__('Error saving message'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ocm_testimonial')->__('Message was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ocm_testimonial')->__('No data found to save'));
        $this->_redirect('*/*/');
    }
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('ocm_testimonial/testimonial');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ocm_testimonial')->__('Message has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the message to delete.'));
        $this->_redirect('*/*/');
    }
}