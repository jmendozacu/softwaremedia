<?php
final class Ophirah_Qquoteadv_Adminhtml_QquoteadvController
    extends Mage_Adminhtml_Controller_Action
{

    CONST XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE = 'qquoteadv/emails/proposal';
    CONST EXPORT_FOLDER_PATH = '/var/qquoteadv_export/';
    protected $_saveFlag  = false;
    protected $_postData  = array();
    
    /*
     * CUSTOMER GRID
     * 
     * 
     */    
    
    protected function _initCustomer($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    
    /**
     * Customer quotes grid
     *
     */
    
    public function quotesAction()
    {          
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    
    /*
     * CUSTOMER GRID
     * 
     * 
     */     
    
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/qquoteadv')
                ->_addBreadcrumb($this->__('Items Manager'), $this->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('qquoteadv/qqadvcustomer')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('qquote_data', $model);
            $skinUrl =  Mage::getDesign()->getSkinUrl();

            $this->loadLayout();
            $this->_setActiveMenu('qquoteadv/items');
            
            // Set currenCurrency from quote
            Mage::app()->getStore()->setCurrentCurrencyCode($model->getCurrency());

            $head = $this->getLayout()->getBlock('head');
            $head->setCanLoadExtJs(true);
            $createHash = array($model->getCreateHash(), $model->getIncrementId());
            $access     = $this->getAccessLevel();
            if (is_null($access) || $this->isTrialVersion($createHash)) {
                Mage::register('createHash', $createHash);
                $msgUpgrade = $this->getMsgToUpgrade();
                $this->_addContent($this->getLayout()->createBlock('core/text', 'example-block')
                                ->setText($msgUpgrade));
            }
   
            $this->_addContent($this->getLayout()->createBlock('qquoteadv/adminhtml_qquoteadv_edit'))
                    ->_addLeft($this->getLayout()->createBlock('qquoteadv/adminhtml_qquoteadv_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {

        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('qquoteadv/adminhtml_qquoteadv_edit'));

        $this->renderLayout();
        //$this->_forward('edit');
    }

    protected function _sendProposalEmail($customerId, $realQuoteadvId) {

        try {
            $customer = Mage::getModel('customer/customer')->load($customerId);

			 $admin = Mage::getSingleton('admin/session')->getUser();
			 $quoteId = (int) $this->getRequest()->getParam('id');
			$_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
			
			$newemail = $this->getRequest()->getParam('newemail');
			$newname = $this->getRequest()->getParam('newname');
			
			if (!$newemail) 
				$newemail = $customer->getEmail();
			if (!$newname) 
				$newname = $customer->getName();
				
			$model = Mage::getModel('crmaddon/crmaddonmessages')->setQuoteId($quoteId);
			$model->setTemplateId(2);
			$model->setCreatedAt(now());
			$model->setCustomerNotified(1);
			$model->setEmailAddress($newemail);
			$model->setMessage('Proposal e-mail sent to ' . $newname . ' (' . $newemail . ') by <strong>' . $admin->getFirstname() . " " .  $admin->getLastname() . '</strong> - Total: ' . $_quoteadv->getGrandTotal());
			$model->save();
				
            $res = $this->sendEmail(array('email' => $newemail, 'name' => $newname));
			
			
            if (empty($res)) {
                $message = $this->__("Qquote proposal email was't sent to the client for quote #%s", $realQuoteadvId);
                Mage::getSingleton('adminhtml/session')->addError($message);
            } elseif(is_string($res) && $res == Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL) {
                Mage::getSingleton('adminhtml/session')->addNotice($this->__('Sending proposal Email is disabled'));
            } elseif (is_string($res) && $res == 'cs') {
	         	Mage::getSingleton('adminhtml/session')->addNotice('Could not send from user e-mail, sending from customerservice@softwaremedia.com instead');  
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Email was sent to client'));
                Mage::helper('qquoteadv')->sentAnonymousData('proposal', 'b');
            }
        } catch (Exception $e) {
            $message = $this->__($e->getMessage(), $realQuoteadvId);
            Mage::log($e->getMessage());
            Mage::getSingleton('adminhtml/session')->addError($message);
            $this->_redirect('*/*/');
            return;
        }
    }
   
    /**
     * Save Quote Action
     * 
     * @return \Ophirah_Qquoteadv_Adminhtml_QquoteadvController
     */
    public function saveAction() {        
        if(!Mage::helper('qquoteadv')->validLicense('create-edit-admin', $this->getRequest()->getPost('createHash') )){
                Mage::getSingleton('adminhtml/session')->addError(__("Please upgrade to Cart2Quote Standard or higher to use this feature"));
                $this->_redirectReferer();
                return;
        }

        // Retrieve Post data
        $data = $this->getRequest()->getPost();
        $pdfPrint = false;
        // If save is called from creating PDF
        if(isset($this->_flags['qquoteadv']['print'])){
            if($this->_flags['qquoteadv']['print'] === true){
                $data = $this->_postData;          
                $pdfPrint = true;
            }
        }        

        if(isset($data)) {
            try {
                if (is_array($data['product']) && count($data['product']) > 0) {
                    
                    if ($quoteId = (int) $this->getRequest()->getParam('id')) {
                        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                        
                        // Setting Extra Options
                        if(isset($data['extra_options'])){
                            if(!is_array($data['extra_options'])){$data['extra_options'] = array($data['extra_options']);}
                            
                            foreach($data['extra_options'] as $key => $option){
                                if(is_array($option)){$option = implode(',', $option);}                               
                                $_quoteadv->setData($key, $option);
                            }
                        }
                        
                        /* // DEPRACTED
                        $baseCurrency = Mage::app()->getBaseCurrencyCode();
                        $currencyCode = $_quoteadv->getData('currency');
                         */
                        // Rate gets calculated from base=>quoterate
                        $rate = $_quoteadv->getBase2QuoteRate();
                      
                        $errors = array();
                        $prodId_prev = 0;
                        foreach ($data['product'] as $id => $arr) {                           
                            $price = $arr['price'];
                            $qty = $arr['qty'];
                            $model = Mage::getModel('qquoteadv/requestitem')->load($id);
                            $productId = $model->getProductId();

                            $quoteProduct = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteItemChildren($productId, $model->getQuoteadvProductId());
                            
                            // Creating ChildProducts array
                            // in case product has a product type Bundle
                            // All childproducts need to be checked
                            $checkQty           = $qty;
                            $checkProductArray  = array();
                            // Parent product gets added first
                            $checkProductArray[] = $quoteProduct;
                            if($quoteProduct->getChildren()){
                                $checkProductArray = array_merge($checkProductArray, $quoteProduct->getChildren());
                            }

                            // Cycle through childproducts
                            foreach($checkProductArray as $checkProduct){
                                if($checkProduct->getQuoteItemQty()){
                                    $checkQty = $checkProduct->getQuoteItemQty();
                                }
                                $check = Mage::helper('qquoteadv')->isQuoteable($checkProduct , $checkQty);
                            }

      
                            if($check->getHasErrors()){
                                $errors = $check->getErrors();
                                //#return back in case any error found
                                if($pdfPrint === true){                                    
                                    return $errors;
                                }else{
                                    if(count($errors)) {
                                        $lastMessage = NULL;
                                        foreach($errors as $message) {
                                            if($message != $lastMessage){
                                                Mage::getSingleton('adminhtml/session')->addError($message);
                                            }
                                            $lastMessage = $message;
                                        }
                                    }
                                    $this->_redirect('*/*/edit', array('id' => $quoteId));
                                }
                            }

                            try {
                                $model->setOwnerCurPrice($price);
                                $basePrice = $price / $rate;                             
                                $model->setOwnerBasePrice($basePrice);
                                
                                $model->save();
                            } catch (Exception $e) {
                                $errors[] = $this->__("Item #%s was't updated", $id);
                            }
                        }                        

                        if (is_array($data['requestedproduct']) && count($data['requestedproduct']) > 0) {

                            $errors = array();
                            $counter = 0;
                            foreach ($data['requestedproduct'] as $id => $arr) {

                                //if($client_request = $arr['client_request']){
                                $client_request = $arr['client_request'];
                                $comment = trim(strip_tags($client_request));

                                $item = Mage::getModel('qquoteadv/qqadvproduct')->load($id);                              
                                
                                try {
                                  $item->setClientRequest($comment);
                                  // Update tier qty
                                  if($data['product'][$data['q2o'][$counter]]['qty']){
                                    $newQty = $data['product'][$data['q2o'][$counter]]['qty'];
                                    $attribute = unserialize($item->getAttribute());
                                    $attribute['qty'] = $newQty;

                                    $item->setAttribute(serialize($attribute));
                                    $item->setQty($newQty);
                                    
                                  }
                                  $item->save();                                
                                } catch (Exception $e) {
                                    $errors[] = $this->__("Item #%s was't updated", $model->getProductId());
                                }
                                $counter++;
                            }
                        }
                        
                        //FILE UPLOAD
                        if($fileTitle = $this->getRequest()->getParam('file_title') || $this->getRequest()->getParam('path_info')){
                            
                            if($this->getRequest()->getParam('file_title')){
                                $fileTitle = $this->getRequest()->getParam('file_title');
                            }elseif(isset($_FILES['file_path']['name'])){
                                $fileTitle = $_FILES['file_path']['name'];                               
                            }else{
                                $fileTitle = 'File_'.$_quoteadv->getData('increment_id');    
                            }
                                                        	
                            $_quoteadv->setFileTitle($fileTitle);
                         
                          if($pathInfo = $this->getRequest()->getParam('path_info')){ 
	                          
                          	  if($pathInfo=='file' && $filePath = $this->fileUpload($_quoteadv->getId()) ){                           
	                           	$_quoteadv->setPath($filePath);
	                          }
	                          
	                          elseif($pathInfo=='url' && $value = $this->getRequest()->getParam('url_path')){
	                           
                                        if (!Mage::helper('qquoteadv')->isValidHttp($value)) {
                                                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('The %s you entered is invalid. Please make sure that it follows "http://domain.com/" format.', $value));
                                                }else{
                                                $_quoteadv->setPath($value);                             
                                        }						        
	                           }
	                        }
                        } 
                        
                        // Setting Status
                        if($data['status']){
                            // Auto update to proposal sent
                            if ($this->getRequest()->getParam('back')) {
                                $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL);
                                $_quoteadv->setSubstatus();
                                // Setting Proposal sent date and time
                                $_quoteadv->setProposalSent(NOW());
                            } elseif(!$this->getRequest()->getParam('hold')) {
                                // check for status and substatus
                                // @var Varien_Object
                                $status = Mage::getModel('qquoteadv/substatus')->getStatus($data['status']);
                                $_quoteadv->addData($status->getData());
                                if(!$status->getSubstatus()){
                                    $_quoteadv->setSubstatus();
                                }
                            }
                        }
                        
                        // Client Request
                        if ($client_request = $this->getRequest()->getParam('client_request')) {
                            $comment = trim(strip_tags($client_request));
                            $_quoteadv->setClientRequest($comment);
                        }else{
                            $_quoteadv->setClientRequest();
                        }
                        
                        // Internal Comment
                        if ($internal_comment = $this->getRequest()->getParam('internal_comment')) {
                            $internalComment = trim(strip_tags($internal_comment));
                            $_quoteadv->setInternalComment($internalComment);                        
                        }else{
                            $_quoteadv->setInternalComment();
                        }
                        
                        // Expiry date                        
                        if ($expiry = $this->getRequest()->getParam('expiry') and preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $expiry)) {
                            $m = substr($expiry, 0, 2);
                            $d = substr($expiry, 3, 2);
                            $y = substr($expiry, 6, 4);
                            $expiryFormatted = $y."-".$m."-".$d;
                            $_quoteadv->setExpiry($expiryFormatted);
                        }                       
                        $no_expiry = ($this->getRequest()->getParam('no_expiry') && $this->getRequest()->getParam('no_expiry')=="on")? 1:0;
                        $_quoteadv->setNoExpiry($no_expiry);
                        
                        // Reminder
                        if ($reminder = $this->getRequest()->getParam('reminder') and preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $reminder)) {
                            $m = substr($reminder, 0, 2);
                            $d = substr($reminder, 3, 2);
                            $y = substr($reminder, 6, 4);
                            $reminderFormatted = $y."-".$m."-".$d;
                            $_quoteadv->setReminder($reminderFormatted);
                        }
                        $no_reminder = ($this->getRequest()->getParam('no_reminder') && $this->getRequest()->getParam('no_reminder')=="on")? 1:0;
                        $_quoteadv->setNoReminder($no_reminder);
                        
                        // Reminder
                        if ($reminder = $this->getRequest()->getParam('reminder') and preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $reminder)) {
                            $m = substr($reminder, 0, 2);
                            $d = substr($reminder, 3, 2);
                            $y = substr($reminder, 6, 4);
                            $reminderFormatted = $y."-".$m."-".$d;
                            $_quoteadv->setReminder($reminderFormatted);
                        }
                        $no_reminder = ($this->getRequest()->getParam('no_reminder') && $this->getRequest()->getParam('no_reminder')=="on")? 1:0;
                        $_quoteadv->setNoReminder($no_reminder);
                        
                        // Reminder
                        if ($reminder = $this->getRequest()->getParam('reminder_2') and preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $reminder)) {
                            $m = substr($reminder, 0, 2);
                            $d = substr($reminder, 3, 2);
                            $y = substr($reminder, 6, 4);
                            $reminderFormatted = $y."-".$m."-".$d;
                            $_quoteadv->setData('reminder_2',$reminderFormatted);
                        }
                        $no_reminder = ($this->getRequest()->getParam('no_reminder_2') && $this->getRequest()->getParam('no_reminder_2')=="on")? 1:0;
                        $_quoteadv->setData('no_reminder_2',$no_reminder);
                        
                        // Reminder
                        if ($reminder = $this->getRequest()->getParam('reminder_3') and preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $reminder)) {
                            $m = substr($reminder, 0, 2);
                            $d = substr($reminder, 3, 2);
                            $y = substr($reminder, 6, 4);
                            $reminderFormatted = $y."-".$m."-".$d;
                            $_quoteadv->setData('reminder_3',$reminderFormatted);
                        }

                        $no_reminder = ($this->getRequest()->getParam('no_reminder_3') && $this->getRequest()->getParam('no_reminder_3')=="on")? 1:0;
                        $_quoteadv->setData('no_reminder_3',$no_reminder);
                        
                        // Follow Up
                        if ($followup = $this->getRequest()->getParam('followup') and preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $followup)) {
                            $m = substr($followup, 0, 2);
                            $d = substr($followup, 3, 2);
                            $y = substr($followup, 6, 4);
                            $followupFormatted = $y."-".$m."-".$d;
                            $_quoteadv->setFollowup($followupFormatted);
                        }
                        $no_followup = ($this->getRequest()->getParam('no_followup') && $this->getRequest()->getParam('no_followup')=="on")? 1:0;
                        if($no_followup == 1){
                            $_quoteadv->setFollowup();      //clear date
                            $_quoteadv->setNoFollowup(0);   //clear checked
                        }
                        // Show item price
                        if($this->getRequest()->getParam('itemprice') == "on"){
                            $_quoteadv->setData('itemprice', 1);
                        }else{
                            $_quoteadv->setData('itemprice', 0);                            
                        }
                        // Salesrule
                        if ($salesrule = $this->getRequest()->getParam('salesrule')) {
                            $_quoteadv->setSalesrule($salesrule);
                        }else{
                            $_quoteadv->setSalesrule(null);                            
                        }
                        
                        // Salesrule
                        
                        if ($nofityadmin = $this->getRequest()->getParam('notify_admin')) {
                            $_quoteadv->setNotifyAdmin($nofityadmin);
                        }else{
                            $_quoteadv->setNotifyAdmin(null);                            
                        }
                        
                        
                        // Assign Salesrep
                        if ($assignedTo = $this->getRequest()->getParam('assigned_to') ) {                            
                          $saveas =  Mage::getModel('admin/user')->load($assignedTo); 
                          if(!$saveas->getUserId()){
                               Mage::getSingleton('adminhtml/session')->addError($this->__('Could not find user with email address: %s', $email));
                               $saveas = Mage::getSingleton('admin/session')->getUser();
                          }
                        }else{
                          $saveas = Mage::getSingleton('admin/session')->getUser();
                        }
                        
                        $_quoteadv->setUserId($saveas->getUserId());

                        //#save shipping price
                        $shippingType = $this->getRequest()->getPost("shipping_type", "");
                        $_quoteadv->setShippingType($shippingType);

                        $shippingPrice = $this->getRequest()->getPost("shipping_price", -1);
                        
                        $_quoteadv->setShippingPrice($shippingPrice);
                        $shippingBasePrice = $shippingPrice / $rate;
                        $_quoteadv->setShippingBasePrice($shippingBasePrice);
                        
                        $_quoteadv->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
                        
                        $userId = $_quoteadv->getUserId(); 
                        if(empty($userId)) {
                          $adm_id = Mage::getSingleton('admin/session')->getUser()->getId();
                          $_quoteadv->setUserId($adm_id);
                        }else{
                          $model = Mage::getModel('admin/user')->load($userId);
                          //#admin is not exists
                          if(!$model->getId() && $id) {
                            $adm_id = Mage::getSingleton('admin/session')->getUser()->getId();
                            $_quoteadv->setUserId($adm_id);                            
                          }                          
                        }

                        // Unset data from sales rep if not allowed
                        if(!Mage::getSingleton('admin/session')->isAllowed('sales/qquoteadv/salesrep'))
                        {
                            $_quoteadv->setUserId($_quoteadv->getOrigData('user_id'));
                        }
                        
                        try{
                            $shippingType = $_quoteadv->getShippingType();
                            if( $shippingType == "I" ||  $shippingType == "O" ){
                                 $_quoteadv->setAddressShippingMethod('flatrate_flatrate');
                                 $_quoteadv->save();
                            }
                            
                            // Get Default shipping
                            if(isset($data['shipping']['rate_id'])){
                                $rateData = Mage::getModel('qquoteadv/quoteshippingrate')->load(420);
                                if($rateData){
                                    $_quoteadv->getAddress()->addData($rateData->getData());
                                }
                            }else{
                                $rateData = Mage::getModel('qquoteadv/quoteshippingrate');
                                $address = $_quoteadv->getAddress();
                                foreach($rateData->getData() as $del){
                                    $address->setData($del, '');
                                }
                            }

                            $_quoteadv->collectTotals();
                            $_quoteadv->save();
                            
                            // Quote Price Recalculation
                            if($recalPrice = $this->getRequest()->getParam('recal_price')){                                
                                if($recalPrice['fixed'] != null && !(float)$recalPrice['fixed'] > 0){
                                    Mage::getSingleton('adminhtml/session')->addNotice($this->__('Fixed Quote Total was not a valid decimal number'));
                                }elseif($recalPrice['percentage'] != null && !(float)$recalPrice['percentage'] > 0){
                                    Mage::getSingleton('adminhtml/session')->addNotice($this->__('Quote Reduction was not a valid decimal number'));                                    
                                }elseif($recalPrice['fixed'] != null || $recalPrice['percentage'] != null ){
                                    if($_quoteadv->recalculateFixedPrice($recalPrice)){
                                        $_quoteadv->save();
                                    }else{
                                        Mage::getSingleton('adminhtml/session')->addError($this->__('Could not recalculate Quote Price'));
                                    }
                                }
                            }
                            
                            Mage::helper('qquoteadv')->sentAnonymousData('save','b');
                        }catch(Exception $e){ 
                            Mage::log($e->getMessage()); 
                        }

                        // unset flag and data from creating PDF method
                        if($pdfPrint === true){                            
                            $this->setFlag('qquoteadv', 'print', false);
                            unset($this->_postData);
                            return $this;
                        }
                        
                        if ($this->getRequest()->getParam('back')) {

                            $realQuoteadvId = $_quoteadv->getIncrementId() ? $_quoteadv->getIncrementId() : $_quoteadv->getId();

                            //#send Proposal email
                            if ($customerId = $_quoteadv->getCustomerId())
                                
                                Mage::register('qquoteadv', $_quoteadv);
                                $this->_sendProposalEmail($customerId, $realQuoteadvId);
                                Mage::unregister('qquoteadv');
                        }
                        
                        // check for hold status
                        if ($hold = $this->getRequest()->getParam('hold')) {
                            if($hold == 1){
                                if(Mage::getModel('qquoteadv/substatus')->getParentStatus($_quoteadv->getSubstatus()) != $_quoteadv->getStatus()){
                                    $_quoteadv->setSubstatus($_quoteadv->getStatus());
                                }
                                $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED);
                                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quote is currently on hold'));
                            }elseif($hold == 2){
                                $statusses = Mage::getModel('qquoteadv/substatus')->getStatus($_quoteadv->getSubstatus());
                                if($statusses){
                                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quote is succesfully unhold!'));
                                    $_quoteadv->addData($statusses->getData());
                                    if(!$statusses->getSubstatus()){
                                        $_quoteadv->setSubstatus();
                                    }
                                }else{
                                    Mage::getSingleton('adminhtml/session')->addNotice($this->__('Old status could not be determined'));
                                    $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST);
                                }
                            }
                            $_quoteadv->save();                            
                        }
                    }
                }
                
                
                
                if(count(Mage::getSingleton('adminhtml/session')->getMessages()->getErrors())) {
                    Mage::getSingleton('adminhtml/session')->addNotice($this->__('Quote was saved with errors'));
                }else{
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quote was successfully saved'));
                }
                
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
                if($data['redirect2neworder']==1){
                    $this->_redirect('*/*/convert/', array('id' => $quoteId, 'q2o_serial' => serialize($data['q2o'])));
                }elseif ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $quoteId));                                             
                }else{
                    $this->_redirect('*/*/edit', array('id' => $quoteId));                
                }
                
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {

        $id = (int) $this->getRequest()->getParam('id');

        if ($id > 0) {
            try {
                $model = Mage::getModel('qquoteadv/qqadvcustomer');

                $model->setId($id)
                        ->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_CANCELED) //STATUS_REJECTED
                        ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quote was successfully canceled'));
                Mage::helper('qquoteadv')->sentAnonymousData('cancel','b');
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $qquoteIds = $this->getRequest()->getParam('qquote');
        if (!is_array($qquoteIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($qquoteIds as $qquoteId) {
                    $qquote = Mage::getModel('qquoteadv/qqadvcustomer')->load($qquoteId);
                    $qquote->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__(
                                'Total of %d record(s) were successfully deleted', count($qquoteIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Set Status and substatus for quote
     * 
     */
    public function massStatusAction() {
        $qquoteIds = $this->getRequest()->getParam('qquote');
        if (!is_array($qquoteIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $status = Mage::getModel('qquoteadv/status')->getStatus($this->getRequest()->getParam('status'));
                foreach ($qquoteIds as $qquoteId) {                    
                    $qquote = Mage::getSingleton('qquoteadv/qqadvcustomer')
                                    ->load($qquoteId)
                                    ->addData($status->getData())
                                    ->setIsMassupdate(true)
                                    ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($qquoteIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * Mass Follow Up Update
     * 
     * Updates follow up date for 
     * selected quotes. If no valid date
     * is given, date is set to null 
     */
    public function massFollowupAction(){
        $qquoteIds = $this->getRequest()->getParam('qquote');
        if (!is_array($qquoteIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($qquoteIds as $qquoteId) {
                   
                    if(strtotime($this->getRequest()->getParam('followup'))){
                        $qquote = Mage::getSingleton('qquoteadv/qqadvcustomer')
                                    ->load($qquoteId)
                                    ->setFollowup($this->getRequest()->getParam('followup'))
                                    ->setIsMassupdate(true)
                                    ->save();
                    }else{
                        $qquote = Mage::getSingleton('qquoteadv/qqadvcustomer')
                                    ->load($qquoteId)
                                    ->setFollowup()
                                    ->setIsMassupdate(true)
                                    ->save();                        
                    }
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($qquoteIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
        
    }

    public function exportCsvAction() {
        $fileName = 'qquote.csv';
        $content = $this->getLayout()->createBlock('qquoteadv/adminhtml_qquote_grid')
                        ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'qquote.xml';
        $content = $this->getLayout()->createBlock('qquoteadv/adminhtml_qquote_grid')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /**
     * Send email to client to informing about the quote proposition
     * @param array $params customer address
     */
    public function sendEmail($params) {
        $admin = Mage::getModel("admin/user")->getCollection()->getData();

        //Create an array of variables to assign to template
        $vars = array();

        $this->quoteId = (int) $this->getRequest()->getParam('id');
        /* @var $_quoteadv Ophirah_Qquoteadv_Model_Qqadvcustomer */
        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($this->quoteId);
        
        $quoteItems = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
                ->addFieldToFilter('quote_id', $this->quoteId)
                ->load();
        
        // check items
        $errorMsg = array();
        $errors = array();
        foreach($quoteItems as $quoteItem) {
            $check = Mage::helper('qquoteadv')->isInStock($quoteItem->getData('product_id'));
            if($check->getData('has_error')){
                $errors[] = $check->getData('message');
            }    
        }             

        //#return back in case any error found
        if(count($errors)) {
            $errorMsg = array_merge($errorMsg, $errors);
            foreach($errorMsg as $message) {
                Mage::getSingleton('adminhtml/session')->addError($message);                
            }
        }

        $vars['quote'] = $_quoteadv;
        $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());
		$vars['rep'] = $_quoteadv->getSalesRepresentative();
		
        $template = Mage::getModel('core/email_template');

	$quoteadv_param = Mage::getStoreConfig('qquoteadv/emails/proposal', $_quoteadv->getStoreId());
        $disabledEmail  = Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL;
        if($quoteadv_param != $disabledEmail):
            if ($quoteadv_param) {
                $templateId = $quoteadv_param;
            } else {
                $templateId = self::XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE;
            }

            // get locale of quote sent so we can sent email in that language	
            $storeLocale = Mage::getStoreConfig('general/locale/code', $_quoteadv->getStoreId());

            if (is_numeric($templateId)) {
                $template->load($templateId);
            } else {
                $template->loadDefault($templateId, $storeLocale);
            }

            $vars['attach_pdf'] = $vars['attach_doc'] = false;

            //Create pdf to attach to email
            if (Mage::getStoreConfig('qquoteadv/attach/pdf', $_quoteadv->getStoreId())) {
                $_quoteadv->_saveFlag = true;
                $pdf = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($_quoteadv);
                $_quoteadv->_saveFlag = false;
                $realQuoteadvId = $_quoteadv->getIncrementId() ? $_quoteadv->getIncrementId() : $_quoteadv->getId();
                try{
                    $file = $pdf->render();
                    $name = Mage::helper('qquoteadv')->__('Price_proposal_%s', $realQuoteadvId);
                    $template->getMail()->createAttachment($file,'application/pdf',Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $name.'.pdf');
                    $vars['attach_pdf'] = true;
                }catch(Exception $e){ Mage::log($e->getMessage()); }

            }
            //Check if attachment needs to be sent with email
            if ($doc = Mage::getStoreConfig('qquoteadv/attach/doc', $_quoteadv->getStoreId())) { 
                    $pathDoc =  Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA). DS .'quoteadv'. DS .$doc;         	 
                    try{
                    $file = file_get_contents($pathDoc);

                    $info = pathinfo($pathDoc);
                    //$extension = $info['extension']; 
                    $basename = $info['basename'];
                    $template->getMail()->createAttachment($file, '' ,Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,$basename); 
                    $vars['attach_doc'] = true;  
                }catch(Exception $e){ Mage::log($e->getMessage()); }
            }
            //Get remark
            $remark = Mage::getStoreConfig('qquoteadv/general/qquoteadv_remark', $_quoteadv->getStoreId());
            if ( $remark ) {
                $vars['remark'] = $remark;
            }

            $adm_name = $this->getAdminName($_quoteadv->getUserId()); 
            $adm_name = trim($adm_name);        
            if ( empty($adm_name)) { $adm_name = $this->getAdminName(Mage::getSingleton('admin/session')->getUser()->getId()); } 
            if ( !empty($adm_name)) {
               $vars['adminname'] = $adm_name;
            }        

            $subject = $template['template_subject'];

            $vars['link']   = Mage::helper('qquoteadv')->getAutoLoginUrl($_quoteadv, 2);

            $sender = $_quoteadv->getEmailSenderInfo();
            $template->setSenderName($sender['name']);
            $template->setSenderEmail($sender['email']);

            $template->setTemplateSubject($subject);
            $bcc = Mage::getStoreConfig('qquoteadv/emails/bcc', $_quoteadv->getStoreId());
            if ($bcc) {
                $bccData = explode(";", $bcc);
                $template->addBcc($bccData);
            }

            if((bool) Mage::getStoreConfig('qquoteadv/emails/send_linked_sale_bcc', $_quoteadv->getStoreId())) {
                $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
            }

            $template->setDesignConfig(array('store' => $_quoteadv->getStoreId()));            

            /**
             * Opens the qquote_request.html, throws in the variable array
             * and returns the 'parsed' content that you can use as body of email
             */
            
            $processedTemplate = $template->getProcessedTemplate($vars);
            
            /*
             * getProcessedTemplate is called inside send()
             */
            $res = $template->send($params['email'], $params['name'], $vars);
            
			if (!$res) {
				return $template->getData('error');
				}
            return $res;
        
        endif;
        
        return $disabledEmail;
        
    }
    
    
    /*
     * Add quote comment action
     */

    public function addCommentAction() {
        if ($qquoteadv = $this->_initQuoteadv()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');

                $comment = trim(strip_tags($data['comment']));

                $this->loadLayout('empty');
                $this->renderLayout();
            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $this->__('Can not add quote history.')
                );
            }
            if (is_array($response)) {
                $response = Zend_Json::encode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Initialize qquoteadv model instance
     *
     * @return Quote || false
     */
    protected function _initQuoteadv() {
        $id = $this->getRequest()->getParam('quote_id');
        $qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($id);

        if (!$qquoteadv->getId()) {
            $this->_getSession()->addError($this->__('This quote no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        Mage::register('qquote_data', $qquoteadv);

        return $qquoteadv;
    }

    public function pdfqquoteadvAction() {
        $errorMsg = array();
        $errors = array();
        
        // From Print button, save quote first
        if($this->getRequest()->getPost() && $this->_saveFlag === false){
            $this->setFlag('qquoteadv', 'print', true);            
            $this->_postData  = $this->getRequest()->getPost();
            $save = $this->saveAction(); 
            if(!empty($save) && !is_object($save)){$errors = $save;}
            $this->setFlag('qquoteadv', 'print', false);
        }
        
        $quoteadvId = $this->getRequest()->getParam('id');
        
        $flag = false;
        if (!empty($quoteadvId) && !$errors) {
            //foreach ($ids as $quoteadvId) {
            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteadvId);
            $quoteItems = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
                            ->addFieldToFilter('quote_id', $quoteadvId)
                            ->load();

            // check items
            foreach($quoteItems as $quoteItem) {
                $check = Mage::helper('qquoteadv')->isInStock($quoteItem->getData('product_id'));
                if($check->getData('has_error')){
                    $errors[] = $check->getData('message');
                }    
            }             
            
            if(count($errors) < 1) {

                if ($quoteItems->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $_quoteadv->collectTotals();
                        $pdf = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($_quoteadv);
                    } else {
                        $pages = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($quoteItems);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }

                if ($flag) {
                    $realQuoteadvId = $_quoteadv->getIncrementId();
                    $fileName = Mage::helper('qquoteadv')->__('Price_proposal_%s', $realQuoteadvId);

                    return $this->_prepareDownloadResponse($fileName . '.pdf', $pdf->render(), 'application/pdf');
                } else {
                    $this->_getSession()->addError($this->__('There are no printable documents related to selected quotes'));
                    $this->_redirect('*/*/');
                }
            }
        }
        
        //#return back in case any error found
        if(count($errors)) {
            $urlReturn = '*/*/edit/id/'.$quoteadvId;            
            $errorMsg = array_merge($errorMsg, $errors);
            foreach($errorMsg as $message) {
                Mage::getSingleton('adminhtml/session')->addError($message);                
            }
        }
        
        if(!$urlReturn){$urlReturn = '*/*/';}
        $this->_redirect($urlReturn);
    }
    
    function isTrialVersion($createHash = NULL){
        return Mage::helper('qquoteadv')->isTrialVersion($createHash);
    }

    function getMsgToUpgrade($updateMsg=false) {
        $createHash = Mage::registry('createHash');         
        
         
        
        $msg = '
        <style>

.leightbox1, .leightboxlink   {
	background-color:#FFFFFF;
	border:2px solid #B8B8B8;
	color:#0A263C;
	display:none;
	font:25px Arial,sans-serif;
	left:30%;
	margin:0;
	overflow:auto;
	padding:0;
	position:absolute;
	text-align:left;
	top:25%;
	width:550px;
	min-height:150px;
	z-index:1001;
}
#overlay, #overlaylink{
	display:none;
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:200%;
	z-index:1000;
	background-color:#333;
	-moz-opacity: 0.8;
	opacity:.80;
	filter: alpha(opacity=80);
}

</style>

<script type="text/javascript">
function prepareIE(height, overflow)
{
bod = document.getElementsByTagName(\'body\')[0];
bod.style.height = height;
bod.style.overflow = overflow;

htm = document.getElementsByTagName(\'html\')[0];
htm.style.height = height;
htm.style.overflow = overflow;
}

function initMsg() {
	bod 				= document.getElementsByTagName(\'body\')[0];
	overlay 			= document.createElement(\'div\');
	overlay.id			= \'overlay\';
	bod.appendChild(overlay);
	$(\'overlay\').style.display = \'block\';
	$(\'lightbox1\').style.display = \'block\';
	prepareIE("auto", "auto");
}

function hideBox() {
    $(\'lightbox1\').style.display = \'none\';
    $(\'overlay\').style.display = \'none\';
}

</script>';
        $msg.='
<div id="lightbox1" class="leightbox1" style="display:none;">
<div >';


   
        $headerText = "";
        $onClick = "";
        if($this->isTrialVersion($createHash) && $this->hasExpired()) {

                $text =  Mage::helper('qquoteadv')->_expiryText;
                $onClick = 'history.back()';
                $headerText = $this->__('Your Trial has expired');

                $btn1 = '<button target="_blank" class="button button1" title="Upgrade" onclick="'.$onClick.'">'.$this->__('No thanks I will use the free version').'</button>';
                $btn2 = '<button target="_blank" class="button button2" title="Request a license" onclick="document.location.href=\'http://www.cart2quote.com/pricing-magento-quotation-module.html?utm_source=Customer%2Bwebsite&utm_medium=license%2Bpopup&utm_campaign=Trial%2BVersion\'">'.$this->__('Yes take me there').'</button> ';
                $smallPrint = $this->__('*Ordered Cart2Quote, but no license yet? <a href="https://cart2quote.zendesk.com/entries/20199692-cfg-request-a-license-key-for-cart2quote">Request your license number.</a>');
        } elseif($this->isTrialVersion($createHash) && !$this->hasExpired()) { 
                $expiry = Mage::helper('qquoteadv')->_expiryDate;

                $now = now();
                $expiry = substr($expiry, 0,4)."-".substr($expiry, 4,2)."-".substr($expiry, 6,2);
                $diff = abs(strtotime($expiry) - strtotime($now));
                $days = floor( $diff/ (60*60*24));
                $headerText = $this->__('Thanks for trying Cart2Quote!');
                $daysToGo =   sprintf("%d", $days);
                $text = $this->__(Mage::helper('qquoteadv')->_trialText, $daysToGo);
                $onClick = 'hideBox()';
                $btn1 = '<button target="_blank" class="button" title="Continue Trial" href="" onclick="'.$onClick.'">'.$this->__('Continue Trial').'</button>';
                $btn2 = '<button target="_blank" class="button button2" title="Purchase a license" onclick="document.location.href=\'http://www.cart2quote.com/pricing-magento-quotation-module.html?utm_source=Customer%2Bwebsite&utm_medium=license%2Bpopup&utm_campaign=Trial%2BVersion%2BExpired\'">'.$this->__('Purchase a License*').'</button> ';
                $smallPrint = $this->__('*If you already ordered a license, you should receive your serial shortly');
        }elseif(!$this->isTrialVersion($createHash)){

                $text =  Mage::helper('qquoteadv')->_wrongQuoteText;
                $onClick = 'history.back()';
                $headerText = $this->__('Your Trial has expired for this Quote');

                $btn1 = '<button target="_blank" class="button button1" title="Upgrade" onclick="'.$onClick.'">'.$this->__('No thanks I will keep using this free version').'</button>';
                $btn2 = '<button target="_blank" class="button button2" title="Request a license" onclick="document.location.href=\'http://www.cart2quote.com/pricing-magento-quotation-module.html?utm_source=Customer%2Bwebsite&utm_medium=license%2Bpopup&utm_campaign=Trial%2BVersion\'">'.$this->__('Yes take me there').'</button> ';
                $smallPrint = $this->__('*Ordered Cart2Quote, but no license yet? <a href="https://cart2quote.zendesk.com/entries/20199692-cfg-request-a-license-key-for-cart2quote">Request your license number.</a>');
        }

        $msg .= '<div id="quoteadv-box-header">';
        $msg .= $headerText;   

        $msg .= '<a onclick="'.$onClick.'" id="quoteadv-box-header-close-btn"></a>';
        $msg .= '</div>';
        $msg.= '<div class="text" >'.$text.'</div>';
        $msg.= $btn1;
        $msg.= $btn2;
        $msg.='<div class="smallprint" >'.$smallPrint.'</div>
        </div>
        </div><script type="text/javascript">document.observe(\'dom:loaded\', function(){
         initMsg();
        });</script>';

        Mage::unregister('createHash');
        return $msg;
    }
    
   private function getAccessLevel($createHash = NULL) {
    return Mage::helper('qquoteadv')->getAccessLevel($createHash);	    
   }
   
   private function hasExpired() {
	     return Mage::helper('qquoteadv')->hasExpired();	    
   }
    
    private function fileUpload($quoteId){
    	
    	$filePath =''; 
    	if(isset($_FILES['file_path']['name']) and (file_exists($_FILES['file_path']['tmp_name']))) {
		
		  try {
		    $uploader = new Varien_File_Uploader('file_path');
		
		    //$uploader->setAllowedExtensions(array('pdf', 'doc', 'jpg','jpeg','gif','png')); // or pdf or anything
		    $uploader->setAllowRenameFiles(true);
		    // setAllowRenameFiles(true) -> move your file in a folder the magento way
		    // setAllowRenameFiles(true) -> move your file directly in the $path folder
		
		    $uploader->setFilesDispersion(false);
                    $uploader->setAllowCreateFolders(true);
                    $path = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadDirPath($quoteId);
                    
		    $result = $uploader->save($path, $_FILES['file_path']['name']);
			if(isset($result['file'])) {
				$filePath = $result['file'];
			} else { 
        		    	$filePath = $_FILES['file_path']['name'];
		    }
		
		  }catch(Exception $e) { 
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage()); 
		  	//throw new Exception($e); //die($e->getMessage());
		  }
		}
		
		return $filePath; 
    }
    
     /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieve order create model
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }    
    
    protected function _convertQuoteItemsToOrder($quoteadvId, $requestedItems){
         //# build sql
        $resource = Mage::getSingleton('core/resource');
        $read= $resource->getConnection('core_read');
        $tblProduct     = $resource->getTableName('quoteadv_product');
        $tblRequestItem = $resource->getTableName('quoteadv_request_item');

        $sql =  "select * from $tblProduct p INNER JOIN $tblRequestItem i  
                            ON p.quote_id=i.quote_id 
                            AND i.quoteadv_product_id=p.id AND p.quote_id=$quoteadvId"; 
                
        if (count($requestedItems)) {
            $items = implode(",", $requestedItems);
            $sql.= " AND i.request_id IN($items)";
        }else{ return; }
                
        $data = Mage::getSingleton('core/resource') ->getConnection('core_read')->fetchAll($sql);

        //add items from quote to order
        foreach($data as $item){
            $productId = $item['product_id'];

            $product    = Mage::getModel('catalog/product')->load($productId);
            //observer will check customPrice after add item to card/quote
            
            Mage::register('customPrice', $item['owner_cur_price']);
           
            if($product->getTypeId() == 'bundle' ){
            	$attr = array(); 
                $attr[$productId] =  @unserialize($item['attribute']); 
                $attr[$productId]['qty'] = (int)$item['request_qty'];
                $this->_getOrderCreateModel()->addProducts($attr);                
                
            }else{
                $params     = @unserialize($item['attribute']);
                $params['qty'] = (int)$item['request_qty'];
            
                try{                                          
                      $this->_getOrderCreateModel()->addProduct($product, $params);
                }catch(Exception $e){                        
                      Mage::log($e->getMessage());
                }                   
            }
            
            Mage::unregister('customPrice');
        }   
        
        
    }
    /*
     * 
     * params
    (
      [id] => 64
      [q2o_serial] => array(109,110)
    )
     */
    
    public function convertAction() {

        $requestedItems = array();
        $quoteadvId = $this->getRequest()->getParam('id');
        $requestedItems = $this->getRequest()->getParam('q2o');
        if(empty($requestedItems)){
            $requestedItems = $this->getRequest()->getParam('q2o_serial'); 
             if(!empty($requestedItems)){
                $requestedItems = unserialize($requestedItems);
            }
        }
        
        if ($requestedItems) {
           foreach($requestedItems as $k=>$v){
             if(empty($v)){
               unset($requestedItems[$k]);
             }
           }                
        }        

        if (!empty($quoteadvId)) {            
            $_quoteadv      = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteadvId);              
            $currencyCode   = $_quoteadv->getData('currency');
            $storeId        = $_quoteadv->getStoreId();
            $this->_getSession()->setStoreId((int) $storeId);

            $customerId     = (int)$_quoteadv->getCustomerId();
            $customer       = Mage::getModel('customer/customer')->load($customerId);
            $this->_getSession()->setCustomerId($customerId);
			
            // empty the quote before adding the items
            $this->_getOrderCreateModel()->getQuote()->removeAllItems();
            
            // get customer address
            $helperAddress = Mage::helper('qquoteadv/address');
            $customerAddresses  = $helperAddress->buildQuoteAdresses($_quoteadv);
                        
            $this->_getOrderCreateModel()
                    ->getQuote()
                    ->setBillingAddress($helperAddress->getQuoteAddress($customer, $customerAddresses['billingAddress'], $storeId, Mage_Customer_Model_Address_Abstract::TYPE_BILLING));
            $this->_getOrderCreateModel()
                    ->getQuote()
                    ->setShippingAddress($helperAddress->getQuoteAddress($customer, $customerAddresses['shippingAddress'], $storeId, Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING));
            
            if($customerAddresses['billingAddress'] != $customerAddresses['shippingAddress'] ){
                $this->_getOrderCreateModel()->getQuote()->getShippingAddress()->setData('same_as_billing', 0);                
            } else {
                $this->_getOrderCreateModel()->getQuote()->getShippingAddress()->setData('same_as_billing', 1);                
            }       
            
            $this->_getOrderCreateModel()->getQuote()->setCustomerId($customerId);
            
            // Apply Coupon Code
            if($_quoteadv->getData('salesrule') > 0){
                $this->_getOrderCreateModel()->applyCoupon($_quoteadv->getCouponCodeById($_quoteadv->getData('salesrule')));
            }
            
            if( count($requestedItems) ){             
                //convert quote items to order
                Mage::app()->getStore()->setCurrentCurrencyCode($currencyCode);
                $this->_convertQuoteItemsToOrder($quoteadvId, $requestedItems);
                Mage::getSingleton('adminhtml/session')->setUpdateQuoteId($quoteadvId);
            }else{
                $msg = $this->__('To create an order, select product(s) and quantity');  
                Mage::getSingleton('adminhtml/session')->addError($msg ); 
                $url = $_SERVER['HTTP_REFERER']; 
                $this->_redirectUrl($url);
                return;
            }
             
            $this->_getOrderCreateModel()
                ->initRuleData()
                ->saveQuote();
            $this->_getOrderCreateModel()->getSession()->setCurrencyId($currencyCode);
            
            Mage::helper('qquoteadv')->sentAnonymousData('confirm', 'b');
            
            $url =  $this->getUrl('adminhtml/sales_order_create/index');
            $this->_redirectUrl($url);

            return;
        }else{
            $this->_redirect('*/*');
        }
    }   
    
    protected function _redirectErr($errorMsg, $url=null){     
        
        if(is_string($errorMsg)) $errorMsg = array($errorMsg);
        if(count($errorMsg)){
            foreach($errorMsg as $msg){
                Mage::getSingleton('adminhtml/session')->addError($msg);
            }
            if($url == null){
                $url = $_SERVER['HTTP_REFERER'];
            }
            
            $this->_redirectUrl($url);				
        }
    }
    
     /**
     * Save customer
     *
     * @param $quote
     */
    protected function _saveCustomerAfterQuote($quote){         
                
        $customer           = $quote->getCustomer();
        $store              = $quote->getStore();
        $billingAddress     = null;
        $shippingAddress    = null; 
        if(!$quote->getCustomer()->getId() ) {
            $customer->addData($quote->getBillingAddress()->exportCustomerAddress()->getData())
                    ->setPassword($customer->generatePassword())
                    ->setStore($store);
            $customer->setEmail($quote->getData('customer_email'));
            $customer->setGroupId($quote->getData('customer_group_id'));

            $customerBilling = $quote->getBillingAddress()->exportCustomerAddress();
            $customerBilling->setIsDefaultBilling(true);
            $customer->addAddress($customerBilling);

            $shipping = $quote->getShippingAddress();
            if (!$quote->isVirtual() && !$shipping->getSameAsBilling()) { 
               $customerShipping = $shipping->exportCustomerAddress();
               $firstname   = $customerShipping->getData('firstname');
               $lastname    = $customerShipping->getData('lastname');

              if( empty($firstname) || empty($lastname) ){
                $msg = $this->__("There was an error, because the customer shipping address was undefined");
                $this->_redirectErr( array($msg)); return;   
              }else{
                $customerShipping->setIsDefaultShipping(true);
                $customer->addAddress($customerShipping);
              }

            } else { 
               $customerBilling->setIsDefaultShipping(true);
            }
            
            try{
             $customer->save();
             $customer->sendNewAccountEmail('registered', '', $customer->getStoreId());  
            }catch(Exception $e){
             $this->_redirectErr( array($e->getMessage()) ); return;  
            }
        }
        
        // set customer to quote and convert customer data to quote
        $quote->setCustomer($customer);
    }
    
    public function switch2QuoteAction(){
        $this->swith2QuoteAction();
    }
   
    public function swith2QuoteAction() {
        
        //unique id for c2q session
        $c2qId	= Mage::getSingleton('adminhtml/session')->getUpdateQuoteId(); //null;

        //pool error messages
        $errorMsg = array();
        
        //quote Data from session
        $quote	= Mage::getSingleton('adminhtml/session_quote')->getQuote();
        
        //post data from input fields
        $data   = $this->getRequest()->getPost();
        
        $baseToQuoteRate    = $quote->getData('base_to_quote_rate');
        $currencyCode       = $quote->getData('quote_currency_code');
        $customerId         = $quote->getCustomer()->getId();
        if(!$customerId && $quote->getData('customer_email')){
            $this->_saveCustomerAfterQuote($quote);
            $customerId = $quote->getCustomer()->getId();
        }        
        
        $billingAddress = $quote->getBillingAddress();
        $shipAddress    = $quote->getShippingAddress();
        
        // Add addresses to customers account
        if($billingAddress->getData('save_in_address_book') == 1 || $shipAddress->getData('save_in_address_book') == 1){
            $helperAddress = Mage::helper('qquoteadv/address');
            if($billingAddress->getData('save_in_address_book') == 1){               
                $helperAddress->addQuoteAddress($customerId, $billingAddress->exportCustomerAddress()->getData());
            }
            if($shipAddress->getData('save_in_address_book') == 1){
                $helperAddress->addQuoteAddress($customerId, $shipAddress->exportCustomerAddress()->getData());
            }
        }

        $email = $quote->getCustomer()->getEmail();
        $items = $quote->getAllItems();
        
    	if (!Mage::getStoreConfig('qquoteadv/general/enabled', $quote->getStoreId())) {			
            $errorMsg[] = $this->__("Module is disabled");
        }		
        if (empty($customerId)) {
            $errorMsg[] = $this->__("Customer not recognized for new quote");
        }		
        if (empty($email) ) {
            $errorMsg[] = $this->__("Customer's email was undefined");
        }
        if (!count($items) ){
            $errorMsg[] = $this->__("There was an error, because the product quantities were not defined");
        }

        foreach($items as $item){ 

            // Simple child products from configurable
            // needs to be checked with qty of the parent item
            // Check if product is a configurable product
            $checkConfigurable = Mage::helper('qquoteadv')->isConfigurable($item, $item->getData('qty'));
            if( $checkConfigurable != false ) {                      
                $qty = $checkConfigurable;                    
            } else {
                $qty = $item->getData('qty');
            }

            // Bundled products need to be checked,
            // including child products
            if($item->getData('product')->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                $bundleOptions  = Mage::getModel('qquoteadv/bundle')->getOrderOptions($item->getData('product'));
                $itemChildren   = Mage::getModel('qquoteadv/qqadvproduct')->getBundleOptionProducts($item->getData('product'), $bundleOptions['info_buyRequest']);
            }

            // Creating ChildProducts array
            // in case product has a product type Bundle
            // All childproducts need to be checked
            $checkQty           = $qty;
            $checkProductArray  = array();
            // Parent product gets added first
            $checkProductArray[] = $item->getData('product');
            if(isset($itemChildren)){
                $checkProductArray = array_merge($checkProductArray, $itemChildren);
            }

            // Cycle through childproducts
            foreach($checkProductArray as $checkProduct){
                if($checkProduct->getQuoteItemQty()){
                    $checkQty = $checkProduct->getQuoteItemQty();
                }
                $check = Mage::helper('qquoteadv')->isQuoteable($item->getData('product'), $checkQty);
            }

            if($check->getHasErrors()){
                $errors = $check->getErrors();
                $errorMsg = array_merge($errorMsg, $errors);
            }
        }

        Mage::getSingleton('adminhtml/session')->setConfParent();

        //#return back in case any error found
        if( count($errorMsg) ){ $this->_redirectErr($errorMsg); return; }
        
        //#c2q insert data
        if($customerId && $email){ 
            $modelCustomer	= Mage::getModel('qquoteadv/qqadvcustomer');            
            $copyShippingParams = array(
                    'shipping_amount'               =>'shipping_amount',
                    'base_shipping_amount'          =>'base_shipping_amount',
                    'shipping_amount_incl_tax'      =>'shipping_amount_incl_tax',
                    'base_shipping_amount_incl_tax' =>'base_shipping_amount_incl_tax',
                    'base_shipping_tax_amount'      =>'base_shipping_tax_amount',
                    'shipping_tax_amount'           =>'shipping_tax_amount',
                    'address_shipping_method'       =>'shipping_method',
                    'address_shipping_description'  =>'shipping_description',
                );
  
            $shipRates = $shipAddress->getShippingRatesCollection();
            
            $copyRateParams = array();
            $rate = null;
            foreach($shipRates as $rates){                                
                if($rates['code'] == $shipAddress->getShippingMethod()){

                 $rate = $rates;   
                 $copyRateParams = array(
                      'shipping_method'           =>'method', 
                      'shipping_description'      =>'method_description',
                      'shipping_method_title'     =>'method_title', 
                      'shipping_carrier'          =>'carrier',
                      'shipping_carrier_title'    =>'carrier_title',
                      'shipping_code'             =>'code'
                  );
                  break;
                }
            }   
                     
            $shipStreet = "";
            $billStreet = "";
            $shipAddressExists = false;
            foreach($shipAddress->getStreet() as $addressLine) {
            	if($addressLine != "") $shipAddressExists = true;
            }
			
            $billAddressExists = false;
            foreach($billingAddress->getStreet() as $addressLine) {
            	if($addressLine != "") $billAddressExists = true;
            }
			
            if($shipAddressExists)  $shipStreet = implode(',',$shipAddress->getStreet());
            if($billAddressExists)  $billStreet = implode(',',$billingAddress->getStreet());            
            
            if(!$c2qId) {
                $name =  $billingAddress->getFirstname();
                //if($name != "") { // &&  count($quote->getCustomer()->getAddresses()) ){
                    /* @var $helper Ophirah_Qquoteadv_Helper_Data */
                    $helper = Mage::helper('qquoteadv');
                    /* @var $admin Mage_Admin_Model_Session */
                    $admin = Mage::getSingleton('admin/session');
                    
                    $itemprice = (Mage::getStoreConfig('qquoteadv/general/itemprice') == 1)?1:0;
                    
                    $qcustomer = array(
                            'created_at' => NOW(),
                            'updated_at' => NOW(),
                        
                            'customer_id'        => $customerId,
                            'currency'           => $currencyCode,
                            'base_to_quote_rate' => $baseToQuoteRate,
                            'prefix'             => $billingAddress->getPrefix(),
                            'firstname'          => $billingAddress->getFirstname(),
                            'middlename'         => $billingAddress->getMiddlename(),
                            'lastname'           => $billingAddress->getLastname(),
                            'suffix'             => $billingAddress->getSuffix(),                        
                            'company'            => $billingAddress->getCompany(),
                            'email'              => $email,
                            'country_id'         => $billingAddress->getCountryId(),
                            'region'             => $billingAddress->getRegion(),
                            'city'               => $billingAddress->getCity(),
                            'address'            => $billStreet,
                            'postcode'           => $billingAddress->getPostcode(),
                            'telephone'          => $billingAddress->getTelephone(),
                            'fax'                => $billingAddress->getFax(),
                            'store_id'           => $quote->getStoreId(),
                            'itemprice'          => $itemprice,
                            'notify_admin'		=> 1,
                            //#shipping
                            'shipping_prefix'        => $shipAddress->getData("prefix"),
                            'shipping_firstname'     => $shipAddress->getData("firstname"),
                            'shipping_middlename'    => $shipAddress->getData("middlename"),
                            'shipping_lastname'      => $shipAddress->getData("lastname"),
                            'shipping_suffix'        => $shipAddress->getData("suffix"),                        
                            'shipping_company'       => $shipAddress->getData("company"),
                            'shipping_country_id'    => $shipAddress->getData("country_id"),
                            'shipping_region'        => $shipAddress->getData("region"),
                            'shipping_region_id'     => $shipAddress->getData("region_id"),
                            'shipping_city'          => $shipAddress->getData("city"),
                            'shipping_address'       => $shipStreet,
                            'shipping_postcode'      => $shipAddress->getData("postcode"),
                            'shipping_telephone'     => $shipAddress->getData("telephone"),
                            'shipping_fax'           => $shipAddress->getData("fax"),              
                        
                    );                  
                    
                    // Assigning Salesrep
                    $modelCustomer->setData($qcustomer);
                    $qcustomer['user_id'] = $helper->getExpectedQuoteAdminId($modelCustomer, $admin->getUserId(), true );
                    
                    foreach($copyShippingParams  as $key){
                        $qcustomer[$key] = $shipAddress->getData($key);
                    }
                    
                    foreach($copyRateParams as $key=>$value){
                        $qcustomer[$key] = $rate[$value];
                    }

                    //#add customer to c2q
                    try {
                        $c2qId = $modelCustomer->addQuote($qcustomer)->getQuoteId();
						
                        //#save c2q id into session
                        $this->getCustomerSession()->setQuoteadvId($c2qId);					
                    }catch(Exception $e){ Mage::log($e->getMessage()); }
                //}else{
                //    $errorMsg[] = $this->__("There was an error, because the customer address was undefined");
                //}
                
            }else{ //$c2qId is given
                
                $this->getCustomerSession()->setQuoteadvId($c2qId);
                $shipStreet     = implode(',',$shipAddress->getStreet());
                $billingStreet  = implode(',',$billingAddress->getStreet());
                $params = array();
                $params['currency'] = $currencyCode;
                
                $addressData = Mage::helper('qquoteadv/address')->addressFieldsArray();                

                foreach($addressData as $key){
                    // Setting params to fix street/address naming issue
                    if($key != 'address'){
                        $params[$key]               = $billingAddress->getData($key);
                        $params["shipping_" . $key] = $shipAddress->getData($key);
                    }
                }

                foreach($copyShippingParams as $key=>$value){
                   $params[$key] = $shipAddress->getData($value);
                }
                 
                foreach($copyRateParams as $key=>$value){
                   $params[$key] = $rate[$value];
                }
                
                // Setting params to fix street/address naming issue
                $params['address']          = $billingStreet;
                $params['shipping_address'] = $shipStreet;
                              
                if(count($params) > 0){
                    try{
                        $modelCustomer	= Mage::getModel('qquoteadv/qqadvcustomer')->updateQuote($c2qId, $params);
                    }catch(Exception $e){
                        Mage::log($e->getMessage());
                    }
                }              

                $qCollection = Mage::getModel('qquoteadv/qqadvproduct');
                $ids = $qCollection->getIdsByQuoteId($c2qId);
                if($ids){
                    foreach($ids as $lineId){
                        try {
                           // remove item to quote mode
                           Mage::getModel('qquoteadv/qqadvproduct')->deleteQuote($lineId);
                         } catch (Exception $e) {
                                $errorQuote[] = $e->getMessage();
                         } 
                    }
                }
                
            }
            
            //#return back in case any error found
            if( count($errorMsg) ){ $this->_redirectErr($errorMsg); return; }

            //#parse in case quote has items
            foreach ($quote->getAllVisibleItems() as $item) { 
                $superAttribute = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $taxStoreConfig = Mage::getStoreConfig('tax/calculation/price_includes_tax');

                $optionalAttrib = '';
                if (isset($superAttribute['info_buyRequest'])) {
                    if (isset($superAttribute['info_buyRequest']['uenc'])){
                        unset($superAttribute['info_buyRequest']['uenc']);
                    }
                    
                    $superAttribute['info_buyRequest']['product'] = $item->getData('product_id');
                    $superAttribute['info_buyRequest']['qty'] = $item->getQty();
                    
                    $optionalAttrib = serialize($superAttribute['info_buyRequest']);   
                }
                
                if($item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $original_price = Mage::getModel('bundle/product_price')->getFinalPrice(1, $item->getProduct());
                    
                    if($taxStoreConfig == 1):
                        $price = $item->getBasePriceInclTax();
                    elseif($item->getProduct()->getPriceType() == 0):
                        $price = $item->getPrice();
                    else:
                        $price = $item->getBasePrice();                    
                    endif;
                } else {
                    $original_price = $item->getProduct()->getPrice();                    
                    if($taxStoreConfig == 1):
                        $price = $item->getProduct()->getBasePriceInclTax();
                    else:
                        $price = $item->getProduct()->getBasePrice();
                    endif;
                }

                // Only Custom Prices needs to be recalculated by currency rate                
                if($item->getOriginalCustomPrice()) {
                    $rate = $baseToQuoteRate;
                    $customPrice = $item->getOriginalCustomPrice();
                } else {    
                    $rate = 1;
                    if($taxStoreConfig == 1):
                        $customPrice = $item->getBasePriceInclTax()*$baseToQuoteRate;
                    elseif($item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $item->getProduct()->getPriceType() == 0):
                        $customPrice = $item->getPrice()*$baseToQuoteRate;
                    else:
                        $customPrice = $item->getBasePrice()*$baseToQuoteRate;
                    endif;
                }
      
                $params = array(
                        'product_id'        => $item->getProductId(),
                        'qty'               => $item->getQty(),
                        'price'             => $price,
                        'custom_price'      => $customPrice,
                        'original_price'    => $original_price,
                        'base_quote_rate'   => $baseToQuoteRate
                );

                
                $this->_create($params, $optionalAttrib);				
            }

            //#update c2q status to make visible c2q request 
            try{
                $modelCustomer->load($c2qId);
                $modelCustomer->setIsQuote(1);
                //DEPRACTED
//                $modelCustomer->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED);
                
                //#for new quote we need correct increment id
                if(!Mage::getSingleton('adminhtml/session')->getUpdateQuoteId()){
                    $modelCustomer->setIncrementId(Mage::getModel('qquoteadv/entity_increment_numeric')->getNextId());                
                }           
                // Add create hash
                $modelCustomer->setCreateHash(Mage::helper('qquoteadv')->getCreateHash($modelCustomer->getIncrementId()));
                //# Add applied SalesRule
                if($quote->getData('applied_rule_ids')){                
                    $code = null;             
                    if(is_string($quote->getData('applied_rule_ids'))){
                        $code = $quote->getData('coupon_code');
                    }

                    if($code != null){
                        $modelCustomer->setData('salesrule', $quote->getData('applied_rule_ids'));
                    }       
                }
                
                // Update Address data
                if($shipAddress->getData('same_as_billing')){
                    $modelCustomer->setData('same_as_billing',$shipAddress->getData('same_as_billing'));
                }
                $modelCustomer->updateAddress();
                
                // Save data
                $modelCustomer->save();              

                 Mage::helper('qquoteadv')->sentAnonymousData('request','b');
            }catch(Exception $e){ Mage::log($e->getMessage()); }     
            
            // Clear session quote data
            Mage::getSingleton('adminhtml/session_quote')->clear();
            
        }//if
		
        Mage::getSingleton('adminhtml/session')->setUpdateQuoteId(null);
        
        if ($c2qId) {
          $this->_redirect('adminhtml/qquoteadv/edit', array('id'=>$c2qId));
        }else{
           $this->_redirect('*/*');   
        }
    }    
    
    /**
     * Get customer session data
     */
    public function getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }
     /**
     * Insert quote data 
      * $params = array(
		'product' => $item->getProductId(),
		'qty'     => $item->getQty(),
        'price'   => $item->getPrice()
        'original_price' => $item->getProduct()->getPrice();
		);
     * @param string $superAttribute
     */
    private function _create($params, $superAttribute) {
        $_product = Mage::getModel('catalog/product')->load($params['product_id']);
        $modelProduct	= Mage::getModel('qquoteadv/qqadvproduct');
        $customerId     = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getCustomer()->getId();
         
        $hasOption = 0;
        $options   = '';
        if (isset($params['options'])) {
               $options     = serialize($params['options']);
               $hasOption   = 1;
        } elseif(isset($superAttribute)) {
               $attr = unserialize($superAttribute);
               
               if (isset($attr['options'])) {
                $options        = serialize($attr['options']);
                $hasOption      = 1; 
                $params['qty']  = $attr['qty'];
               }
        }

        $quoteId = $this->getCustomerSession()->getQuoteadvId();
        $qproduct = array(
            'quote_id'      => $quoteId,
            'product_id'    => $params['product_id'],
            'qty'           => $params['qty'],
            'attribute'     => $superAttribute,
            'has_options'   => $hasOption,
            'options'       => $options,
            'store_id'      => Mage::getSingleton('adminhtml/session_quote')->getStoreId()  //$this->getCustomerSession()->getStoreId()
        );

        // Get Currency rate from database
        $_quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        $rate = (isset($params['base_quote_rate']))?$params['base_quote_rate']:1;

        // Defining Prices
        $basePrice      = $params['custom_price'] / $rate ;
        $price          = $params['custom_price'];
        $orgPrice       = $params['original_price'];
        $orgCurPrice    = $orgPrice * $rate;    
      
        try{
            $obj = $modelProduct->addProduct($qproduct); 
            $requestData = array(
                    'quote_id'              => $this->getCustomerSession()->getQuoteadvId(),
                    'product_id'            => $params['product_id'],
                    'request_qty'           => $params['qty'],
                    'owner_base_price'      => $basePrice,
                    'owner_cur_price'       => $price,
                    'original_price'        => $orgPrice,
                    'original_cur_price'    => $orgCurPrice,
                    'quoteadv_product_id'   => $obj->getId()
            );

            Mage::getModel('qquoteadv/requestitem')->setData($requestData)->save();
	
        }catch(Exception $e) {
            Mage::log($e->getMessage()); 
        }                     
        
        return $this;
    }
    
    /**
     * Get core session data
    */
    public function getCoreSession() {
        return Mage::getSingleton('core/session');
    }

    public function deleteQtyFieldAction(){
            $requestId	= (int) $this->getRequest()->getParam('request_id');
            $c2qId		= null;
            if(empty($requestId)){ $this->_redirect('*/*/*'); }

            $item	= Mage::getModel('qquoteadv/requestitem')->load($requestId);
            $c2qId	= $item->getData('quote_id');

             $_quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load($c2qId);

            $quoteProductId = $item->getData('quoteadv_product_id'); 
            $listRequests = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($_quote);
            $listRequests->addFieldToFilter('quoteadv_product_id', $quoteProductId);
            $size = $listRequests->getSize() ;

            if($size>1){ 
                    try{
                            $item->delete();
                    }catch(Exception $e){ 
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());  
                    }
            }else{
                    $msg = $this->__('Minimum of one Qty is required');
                    Mage::getSingleton('adminhtml/session')->addError($msg);
            }

            $this->_redirect('*/*/edit', array('id'=>$c2qId));
    }

    public function addQtyFieldAction() {
            $quoteProductId		= (int) $this->getRequest()->getParam('quote_product_id');
            $quoteProduct               = Mage::getModel('qquoteadv/qqadvproduct')->load($this->getRequest()->getParam('quote_product_id'));                       
            $product                    = Mage::getModel('catalog/product')->load($quoteProduct->getData('product_id'));

            // For configurable product, use the simple product 
            if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $attribute = $quoteProduct->getData('attribute');               
                if(!is_array($attribute)) {$attribute = unserialize($attribute);}
                $prod_simple = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($attribute['super_attribute'], $product);
                $check_prod = $prod_simple;                
            } else {
                $check_prod = $product;
            }

            $requestQty			= (int) $this->getRequest()->getParam('request_qty');
            $c2qId                      = (int) $this->getRequest()->getParam('quoteadv_id');

            $check = Mage::helper('qquoteadv')->isQuoteable( $check_prod , $requestQty);
            if($check->getHasErrors()){
                $errors = $check->getErrors();
                $this->_redirectErr($errors);
                return;
            }

            $originalPrice	= 0;
            $productId		= null;

            if(empty($quoteProductId) or empty($requestQty)){
                $errorMsg = $this->__("Not valid data"); 
                Mage::getSingleton('adminhtml/session')->addError($errorMsg);

                if (!empty($c2qId)) {
                  return $this->_redirect('*/*/edit', array('id' => $c2qId));
                }
                else{
                  return $this->_redirect('*/*/');
                }
            }

            //#SEARCH ORIGINAL PRICE
            $_quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load($c2qId);

            $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($_quote)
                            ->addFieldToFilter('quoteadv_product_id', $quoteProductId);

            //#trying to find duplicate of requested quantity value
            foreach($_collection as $item){      
                $c2qId = $item->getData('quote_id');

                $productId = $item->getData('product_id');
                $check = Mage::helper('qquoteadv')->isQuoteable( $productId , $requestQty);
                if($check->getHasErrors()){
                    $errors = $check->getErrors();
                    $this->_redirectErr($errors);
                    return;
                }

                if($requestQty == $item->getData('request_qty')){
                    $errorMsg = $this->__('Duplicate value entered');
                    Mage::getSingleton('adminhtml/session')->addError($errorMsg);
                    return $this->_redirect('*/*/edit', array('id'=>$c2qId));
                }
            }
            
            $ownerPrice     = Mage::helper('qquoteadv')->_applyPrice($quoteProductId, $requestQty);  
            $originalPrice  = Mage::helper('qquoteadv')->_applyPrice($quoteProductId, 1);

            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($c2qId);
            
            $rate = $_quoteadv->getBase2QuoteRate();
            /* // DEPRACTED
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $currencyCode = $_quoteadv->getData('currency');
            if($currencyCode == "") $currencyCode = $baseCurrency;
            if($currencyCode != $baseCurrency){
                  $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrency,$currencyCode);
                  $rate = $rates[$currencyCode];
            }else{
                $rate = 1;
            }
            */

            $basePrice = $ownerPrice;

		if($c2qId && $productId && $originalPrice && $requestQty) {
                    $requestData = array(
                                    'quote_id'              => $c2qId,
                                    'product_id'            => $productId,
                                    'request_qty'           => $requestQty,
                                    'owner_base_price'      => $basePrice,
                                    'owner_cur_price'       => $ownerPrice*$rate,
                                    'original_price'        => $originalPrice,
                                    'quoteadv_product_id'   => $quoteProductId,
                                    'original_cur_price'    => $basePrice*$rate                            
                    );
                    
                    if($requestQty){
                            try{
                                    Mage::getModel('qquoteadv/requestitem')->setData($requestData)->save();
                            }catch(Exception $e){ 
                                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage()); 
                            }
                    }
		}
        if(!empty($c2qId)) {
          $this->_redirect('*/*/edit', array('id'=>$c2qId));
        }else{
          $this->_redirect('*/*/');
        }
    }
        
    /**
     * Acl check for admin
     *
     * @return bool
    */
    protected function _isAllowed()
    {   
	    $aclResource = 'admin/sales/qquoteadv';
		return Mage::getSingleton('admin/session')->isAllowed($aclResource);        
    }
    
    public function getAdminName($id){
		return Mage::helper('qquoteadv')->getAdminName($id);
    } 
    
    
    /**
    * Export selected quotes as csv
    */
    public function exportAction() {
        
    	$quoteIds = $this->getRequest()->getParam('qquote');
    	if(!is_array($quoteIds) || empty($quoteIds)) {
            $this->_redirectErr($this->__('No quotes selected to export'));
            return;
    	}
        
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteIds);
        
        if(!Mage::helper('qquoteadv')->validLicense('export', array($quote->getData('create_hash'), $quote->getData('increment_id')))){
          $this->_redirectErr($this->__('The CSV export function is only available in Cart2Quote Enterprise. 
          To update please go to <a href="http://www.cart2quote.com/pricing-magento-quotation-module.html?utm_source=Customer%2Bwebsite&utm_medium=license%2Bpopup&utm_campaign=Upgrade%2Bversion">http://www.cart2quote.com</a>')); 
          return;  
        }
    
    	$folder = Mage::getBaseDir().self::EXPORT_FOLDER_PATH;
    	$filename = "cart2quoteExport_".date("ymdHis").".csv";
    	
    	//check the folder exists or create it
    	if(!file_exists($folder)){
    		try{
    			mkdir($folder);
    		}catch(Exception $e){
    			Mage::Log($e->getMessage());
    			$this->_redirectErr($this->__('Could not create cart2quote export folder: '). $folder); return;
    		}
    	}else{
    		if(!is_writable($folder)){
    			$this->_redirectErr($this->__('The cart2quote export folder is not writable: '). $folder); return;
    		}
    	}
    	
    	//set filepath
    	$filepath = $folder.$filename;
    	
    	//export quotes to file
    	$exported = Mage::getSingleton('qquoteadv/qqadvcustomer')->exportQuotes($quoteIds, $filepath);  
    	
    	if($exported){
    		$contents = file_get_contents($filepath);
    		$this->_prepareDownloadResponse($filename, $contents);
    	}else{
    		$this->_redirectErr($this->__('Could not export quotes')); return;
    	}
    }
}
