<?php

class OCM_Quotedispatch_Adminhtml_QuotedispatchController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('quotedispatch/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Quote Dispatch'), Mage::helper('adminhtml')->__('Quote Dispatch'));
		
		return $this;
	}   
 
	public function indexAction() {
		$grid = $this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch');
		$this->_initAction();
		$this->getLayout()->getBlock('content')->append($grid,'quotedispatch_grid');
		$this->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('quotedispatch/quotedispatch')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('quotedispatch_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('quotedispatch/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Quote Dispatch'), Mage::helper('adminhtml')->__('Quote Dispatch'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Quote'), Mage::helper('adminhtml')->__('Edit Quote'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_edit'))
				->_addLeft($this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('quotedispatch')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
           
	}
 
	public function newAction() {
            
		$this->_forward('edit');
	}
 
	public function saveAction() {
	
	
		if ($data = $this->getRequest()->getPost()) {
		
		    $quotedata = $this->getRequest()->getPost();
                
			$quotemodel = Mage::getModel('quotedispatch/quotedispatch');		
			$quotemodel->setData($quotedata)
				->setId($this->getRequest()->getParam('id'));
				
			try {
			
                if(in_array($quotemodel->getStatus(),Mage::getModel('quotedispatch/status')->available_statuses)) {
                    $quotemodel->setAvailableTime(now());
                } else {
                    $quotemodel->setAvailableTime(null);
                }
                				
				$quotemodel->save();

				// Add new Items
        		if(isset($data['links'])){
        		    $quotedispatch_id = $quotemodel->getId();
                    $quoteitems = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['links']['quoteitems']);
        			$item_model = Mage::getModel('quotedispatch/quotedispatch_items');
        			$product_model = Mage::getModel('catalog/product');
        			foreach ($quoteitems as $product_id => $item) {
        			
            			$item_model->loadByMultiple(array(
            			     'quotedispatch_id' => $quotedispatch_id,
            			     'product_id' => $product_id
            			));
            			
            			if($item_model->getData('quotedispatch_item_id') && $item['qty'] < 1 ) {
                			$item_model->delete();
            			} else {
            			     
            			     if($item['qty'] < 1) {
                			     $item['qty'] = 1;
            			     }
            			     if($item['price'] <= 0){
                			     $item['price'] = $product_model->load($product_id)->getPrice();
            			     }
            			
                			$item_model->setData('quotedispatch_id', $quotedispatch_id);
                			$item_model->setData('product_id', $product_id);
                			$item_model->setData('price', $item['price']);
                			$item_model->setData('qty' , $item['qty']);
                			$item_model->save();
            			}
            			
            			
        			}
                }

                if(isset($data['note']) && $data['note'] != ''){
                
                    $current_user = Mage::getSingleton('admin/session')->getUser()->getUsername();
                    
                
                    $note_model = Mage::getModel('quotedispatch/quotedispatch_notes');
                    $note_data = array(
                        'content' => $data['note'],
                        'created_by' => $current_user,
                        'quotedispatch_id' => $quotemodel->getId()
                    );
                    $note_model->setData($note_data);
                    $note_model->save();
                
                }


				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('quotedispatch')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $quotemodel->getId()));
					return;
				}
				if ($this->getRequest()->getParam('email')) {
				    try {
    				    Mage::helper('quotedispatch')->sendEmail($quotemodel);
    				    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('quotedispatch')->__('Email has been sent'));
    				    $quotemodel->setStatus(1)->save();
    					$this->_redirect('*/*/edit', array('id' => $quotemodel->getId()));
    					return;
				    } catch (Exception $e) {
    				    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('quotedispatch')->__('Failed to send email with exception : %s',$e->getMessage()));
				    }
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('quotedispatch')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('quotedispatch/quotedispatch');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $quotedispatchIds = $this->getRequest()->getParam('quotedispatch');
        if(!is_array($quotedispatchIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($quotedispatchIds as $quotedispatchId) {
                    $quotedispatch = Mage::getModel('quotedispatch/quotedispatch')->load($quotedispatchId);
                    $quotedispatch->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($quotedispatchIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
  
    public function quoteitemsAction() {
        
        $id = $this->getRequest()->getParam('id');
        if(!$id) {
            die('You must save the quote info before adding items.');
        }
        
        $this->loadLayout();
        $this->getLayout()->getBlock('quoteitems.grid')
            ->setQuoteitems($this->getRequest()->getPost('quoteitems', null));
        $this->renderLayout();
        
    }
    
    public function quoteitemsgridAction() {
        
        $this->loadLayout();
        $this->getLayout()->getBlock('quoteitems.grid')
            ->setQuoteitems($this->getRequest()->getPost('quoteitems', null));
        $this->renderLayout();
        
    }
    
    public function exportCsvAction()
{
   $fileName   = 'quote_dispatch.csv';
   $grid       = $this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_grid');
   $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
}
public function exportExcelAction()
    {
        $fileName   = 'invoices.xml';
        $grid       = $this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}