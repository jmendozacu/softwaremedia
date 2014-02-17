<?php
class OCM_Quotedispatch_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    
        if ($user_email = $this->_checkUid()) {
            
            $collection = Mage::getModel('quotedispatch/quotedispatch')->getCollection()
                ->addFieldToFilter('email',$user_email)
                ->addAvailableStatusFilter()
                ->addQuoteSubtotal()
            ;
            
            if (!$collection->getSize()) {
                //die($collection->getSelect());
                Mage::getSingleton('core/session')->addError(Mage::helper('quotedispatch')->__('You have no quotes available at this time'));
                $this->_redirect('/');
                return;
            }
            
            Mage::register('qdquotes',$collection);
            
            $this->loadLayout();     
            $this->renderLayout();
               
                        
        } else {
            $this->_redirect('/');
        }
    }

    
    public function viewAction() {
        if ($user_email = $this->_checkUid()) {
            
            $id = $this->getRequest()->getParam('id');
            
            $current_quote = Mage::getModel('quotedispatch/quotedispatch')->loadByMultiple(array(
                'email' => $user_email,
                'quotedispatch_id' => $id
            ));
            
            if (!$current_quote->getId()) {
                
                $this->_redirect('/');
                return;
            }

            Mage::register('current_quote',$current_quote);
            
            $this->loadLayout();     
            $this->renderLayout();
               
                        
        } else {
            $this->_redirect('/');
        }
        
    }

    public function orderAction() {
    
        //die(print_r($this->getRequest()->getParams()));
        
        if ($user_email = $this->_checkUid()) {
            
            $id = $this->getRequest()->getParam('id');
            
            $current_quote = Mage::getModel('quotedispatch/quotedispatch')->loadByMultiple(array(
                'email' => $user_email,
                'quotedispatch_id' => $id
            ));
            
            if (!$current_quote->getId()) {
                // TODO needs to goback and add error messaging
                $this->_redirect('/');
                return;
            }
            
            try {
            
                // Clear Current Cart
                $cart = Mage::getSingleton('checkout/cart');
                //$cart->truncate()->save();
                
                $product_model = Mage::getModel('catalog/product');
                
                foreach($current_quote->getAllItems() as $item) {
                
                    try {
                    //TODO : Move to helper or model
                    //print_r($item->getData());
                    $product =  Mage::getModel('catalog/product')->load($item->getProductId());
                    $request = new Varien_Object(array('qty' => $item->getQty()));
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $result = $quote->addProduct($product,$request);
                    
    
                    if (is_string($result)) {
                        Mage::throwException($result);
                    }
                    
                    // use quote price for item
                    $result->setCustomPrice($item->getPrice());
                    $result->setOriginalCustomPrice($item->getPrice());
                    $result->getProduct()->setIsSuperMode(true);
                    
                    // hack - cart displays empty template if we do not increment
                    $items_count = $quote->getItemsCount() + 1;
                    $quote->setItemsCount($items_count);
                    
                    $quote->save();
                    Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $result->getProduct()));
                    } catch (Exception $e) {
                    
                        //TODO :: do better
                    
                        Mage::getSingleton('core/session')->addError(Mage::helper('quotedispatch')->__('Failed to add Item '.$product->getSku().' to cart. Please Contact your sales rep'));
                        throw new Exception($e->getMessage());
                        
                    }

                }
                $this->addQuoteIdToSession($current_quote->getId());
                Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                //$cart->setHasQuoteDispatch(true);
                $cart->save();
                
                $this->_redirect('checkout/onepage');
                
            } catch (Exception $e) {
                Mage::log('Failed to add Quote!'."\n".$e,null,'quotedispatch.log');
                // TODO needs to goback and add error messaging
                $this->_redirect('/');
            }

       
        }
        
        
    }

    public function addQuoteIdToSession($id) {
        
        if(Mage::getSingleton('core/session')->getQuotedispatchIds()) {
            $quote_array = Mage::getSingleton('core/session')->getQuotedispatchIds();
        } else {
            $quote_array = array();
        }
        $quote_array[] = $id;
        Mage::getSingleton('core/session')->setQuotedispatchIds($quote_array);
        return $this;
        
    }

    public function createAction() {
    
        //die(print_r($this->getRequest()->getParams()));
        $post = $this->getRequest()->getParams();
        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        $now = new DateTime('now', new DateTimeZone('America/Denver'));
        $created_time = $now->format('Y-m-d H:i:s');

        
        if(isset($post['email']) && count($items)) {
            
            try {
                $model = Mage::getModel('quotedispatch/quotedispatch');
                $item_model = Mage::getModel('quotedispatch/quotedispatch_items');
                //die(var_dump($post));
<<<<<<< HEAD
               
=======
                $model->setNotes('Test Note');
                $model->setCreatedTime($created_time);
>>>>>>> chen
                $model->setData($post);
                $model->setStatus(0);
                $model->save();
                
                foreach($items as $item) {
                    if (!$item->getParentId()){
                        $item_model->setData($item->getData());
                        $item_model->setQuotedispatchId($model->getId());
                        $item_model->save();
                    }
                }
                
                $this->_redirect('quote-thank-you');
                return;
                
            } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
                Mage::getSingleton('core/session')->addError(Mage::helper('quotedispatch')->__('There was an error proccessing your request'));
                
            }
            
        }
        
        if(!count($items)) {
            Mage::getSingleton('core/session')->addError(Mage::helper('quotedispatch')->__('Your request contained no items!'));
            
        }

        $this->loadLayout();     
        $this->renderLayout();
            
    }
    
    public function successAction() {
        $this->loadLayout();     
        $this->renderLayout();
    }
    
    
    public function removefromcartAction() {
        $quotedispatch_id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('quotedispatch/quotedispatch')->load($quotedispatch_id);
        $quotedispatch_items = array();
        foreach ($model->getAllItems() as $item) {
            $quotedispatch_items[] = $item->getProductId();
        }
        
        $cart = Mage::getSingleton('checkout/cart');
        $quote_items = $cart->getQuote()->getAllItems();
        foreach ($quote_items as $item) {
            if(in_array($item->getProductId(), $quotedispatch_items)) {
                $cart->removeItem($item->getId());
            }
        }
        $cart->save();
       $this->_redirect('checkout/cart');
    }
    
    
    protected function _checkUid() {
        
        $user_email_hash = $this->getRequest()->getParam('uid');
        
        if ($user_email_hash) {
            $user_email = Mage::helper('quotedispatch')->decryptHash($user_email_hash);
            return $user_email;
        }
        return false;
        
    }
    
}

/*
http://experiment-repos.devocm.com/nate/quotedispatch/?uid=6f43147379d3c8dc49f680790555de066
*/