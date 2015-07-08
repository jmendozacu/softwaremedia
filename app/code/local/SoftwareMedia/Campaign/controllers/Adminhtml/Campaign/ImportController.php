<?php

class SoftwareMedia_Campaign_Adminhtml_Campaign_ImportController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('campaign/import')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}

	public function indexAction() {

		$this->loadLayout();

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

		$this->_addContent($this->getLayout()->createBlock('softwaremedia_campaign/adminhtml_import'));

		$this->renderLayout();
	}
	
	public function importAction() {
		if ($data = $this->getRequest()->getPost()) {

			if (isset($_FILES['import']['name']) && $_FILES['import']['name'] != '') {
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
					$path = Mage::getBaseDir('media') . DS;
					$uploader->save($path, 'campaign.csv');
					
					$handle = fopen($path . 'campaign.csv', 'r');
					$count = 0;
					while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
						$count++;
						$note = Mage::getModel('customernotes/notes');

						$admin = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username',$row[0])->getFirstItem();
						

						if (!$admin->getId()) {
							Mage::getSingleton('adminhtml/session')->addError('Error in row ' . $count . ': Username ' . $row[0] . ' does not exist');				
							$error = true;
							continue;
						}
						
						$customer = Mage::getModel('customer/customer')->loadByEmail($row[1]);
						if (!$customer->getId()) {
							Mage::getSingleton('adminhtml/session')->addError('Error in row ' . $count . ': Customer Email ' . $row[1] . ' does not exist');			
							$error = true;
							continue;
						}
						
						$time = date('Y-m-d H:i:s',strtotime($row[2]));

						$dupeNote = Mage::getModel('customernotes/notes')->getCollection()->addFieldToFilter('customer_id',$customer->getId())->addFieldToFilter('created_time', $time);
						if (count($dupeNote)>0) {
							Mage::getSingleton('adminhtml/session')->addError('Error in row ' . $count . ': Note already exists for this customer and time');			
							$error = true;
							continue;
						}
						$lastNote = Mage::getModel('customernotes/notes')->getCollection()->addFieldToFilter('customer_id',$customer->getId())->addFieldToFilter('update_time', array('null' => true));
						echo $lastNote->getSelect();
		
		                foreach($lastNote as $lNote) {
							$lNote->setUpdateTime($row[2]);
							$lNote->save();
							
		                }
                
						$note->setUserId($admin->getId());
						$note->setUsername($admin->getUsername());
						$note->setCustomerId($customer->getId());
						$note->setCustomerName($customer->getFirstName() . " " . $customer->getLastName());
						$note->setCreatedTime($row[2]);
						$note->setContactMethod($row[3]);
						if ($row[4])
							$note->setCampaignId($row[4]);
						if ($row[5])
							$note->setStepId($row[5]);
						$note->setNote($row[6]);
						$note->save();
						
						
					}
					if (!$error)
						Mage::getSingleton('adminhtml/session')->addSuccess('All rows imported succesfully');
						
				} catch (Exception $e) {

				}
				
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('peachtree')->__('Import Unsuccesful'));
			}
		}


		$this->_redirect('*/*/');
	}
	
}

?>