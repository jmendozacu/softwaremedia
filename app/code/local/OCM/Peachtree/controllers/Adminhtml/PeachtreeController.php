<?php

class OCM_Peachtree_Adminhtml_PeachtreeController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('peachtree/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {

			$this->loadLayout();
			$this->_setActiveMenu('peachtree/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('peachtree/adminhtml_peachtree_edit'));

			$this->renderLayout();

	}

  
    public function exportAction()
    {
        $post = $this->getRequest()->getPost();
    
        $fileName   = 'peachtree_'.$post['from'].'_'.$post['to'].'.csv';
        $content    = Mage::getModel('peachtree/csv')->setData($post)->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }




/* will not need functions below */


    public function exportXmlAction()
    {
        $fileName   = 'peachtree.xml';
        $content    = $this->getLayout()->createBlock('peachtree/adminhtml_peachtree_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }


	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('peachtree/peachtree')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('peachtree_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('peachtree/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('peachtree/adminhtml_peachtree_edit'))
				->_addLeft($this->getLayout()->createBlock('peachtree/adminhtml_peachtree_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['import']['name']) && $_FILES['import']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('import');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('csv'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, 'peachtree.csv');
					
				} catch (Exception $e) {
		      
		        }
		        //Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFromCsv();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('peachtree')->__('Peachtree Import Uploaded Successfully'));
				$this->_redirect('*/*/');
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Please choose a file to upload'));
			}
	  			
        }
        
        
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('peachtree/peachtree');
				 
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
        $peachtreeIds = $this->getRequest()->getParam('peachtree');
        if(!is_array($peachtreeIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($peachtreeIds as $peachtreeId) {
                    $peachtree = Mage::getModel('peachtree/peachtree')->load($peachtreeId);
                    $peachtree->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($peachtreeIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $peachtreeIds = $this->getRequest()->getParam('peachtree');
        if(!is_array($peachtreeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($peachtreeIds as $peachtreeId) {
                    $peachtree = Mage::getSingleton('peachtree/peachtree')
                        ->load($peachtreeId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($peachtreeIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}