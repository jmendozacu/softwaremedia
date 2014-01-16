<?php
class OCM_Testimonial_IndexController extends Mage_Core_Controller_Front_Action{
    public function postAction(){
        $post = $this->getRequest()->getPost();
        $company = $post['testimonial_company'] ? $post['testimonial_company'] : "";
        $datetime = time();
        $model = Mage::getModel('ocm_testimonial/testimonial');
        $model->setUserName($post['testimonial_name'])
              ->setCompany($company)
              ->setMessage($post['testimonial_comment'])
              ->setDatePost($datetime)
              ->save();
        header('location: '.Mage::getBaseUrl().'ratings.html');
        exit();
    }
}