<?php
class OCM_Peachtree_Adminhtml_PeachtreeController extends Mage_Adminhtml_Controller_Action {
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'peachtree/items' )->_addBreadcrumb ( Mage::helper ( 'adminhtml' )->__ ( 'Items Manager' ), Mage::helper ( 'adminhtml' )->__ ( 'Item Manager' ) );
		
		return $this;
	}
	public function indexAction() {
		$this->loadLayout ();
		$this->_setActiveMenu ( 'peachtree/items' );
		
		$this->_addBreadcrumb ( Mage::helper ( 'adminhtml' )->__ ( 'Item Manager' ), Mage::helper ( 'adminhtml' )->__ ( 'Item Manager' ) );
		$this->_addBreadcrumb ( Mage::helper ( 'adminhtml' )->__ ( 'Item News' ), Mage::helper ( 'adminhtml' )->__ ( 'Item News' ) );
		
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		
		$this->_addContent ( $this->getLayout ()->createBlock ( 'peachtree/adminhtml_peachtree_edit' ) );
		
		$this->renderLayout ();
	}
	public function exportAction() {
		$post = $this->getRequest ()->getPost ();
		
		$fileName = 'peachtree_' . $post ['from'] . '_' . $post ['to'] . '.csv';
		$content = Mage::getModel ( 'peachtree/csv' )->setData ( $post )->getCsv ();
		
		$this->_sendUploadResponse ( $fileName, $content );
	}
	
	/* will not need functions below */
	public function exportXmlAction() {
		$fileName = 'peachtree.xml';
		$content = $this->getLayout ()->createBlock ( 'peachtree/adminhtml_peachtree_grid' )->getXml ();
		
		$this->_sendUploadResponse ( $fileName, $content );
	}
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'peachtree/peachtree' )->load ( $id );
		
		if ($model->getId () || $id == 0) {
			$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
			if (! empty ( $data )) {
				$model->setData ( $data );
			}
			
			Mage::register ( 'peachtree_data', $model );
			
			$this->loadLayout ();
			$this->_setActiveMenu ( 'peachtree/items' );
			
			$this->_addBreadcrumb ( Mage::helper ( 'adminhtml' )->__ ( 'Item Manager' ), Mage::helper ( 'adminhtml' )->__ ( 'Item Manager' ) );
			$this->_addBreadcrumb ( Mage::helper ( 'adminhtml' )->__ ( 'Item News' ), Mage::helper ( 'adminhtml' )->__ ( 'Item News' ) );
			
			$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
			
			$this->_addContent ( $this->getLayout ()->createBlock ( 'peachtree/adminhtml_peachtree_edit' ) )->_addLeft ( $this->getLayout ()->createBlock ( 'peachtree/adminhtml_peachtree_edit_tabs' ) );
			
			$this->renderLayout ();
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'peachtree' )->__ ( 'Item does not exist' ) );
			$this->_redirect ( '*/*/' );
		}
	}
	public function newAction() {
		$this->_forward ( 'edit' );
	}
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			
			if (isset ( $_FILES ['import'] ['name'] ) && $_FILES ['import'] ['name'] != '') {
				try {
					/* Starting upload */
					$uploader = new Varien_File_Uploader ( 'import' );
					
					// Any extention would work
					$uploader->setAllowedExtensions ( array (
							'csv' 
					) );
					$uploader->setAllowRenameFiles ( false );
					
					// Set the file upload mode
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders
					// (file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion ( false );
					
					// We set media as the upload dir
					$path = Mage::getBaseDir ( 'media' ) . DS;
					$uploader->save ( $path, 'peachtree.csv' );
				} catch ( Exception $e ) {
				}
				// Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFromCsv();
				Mage::getModel ( 'ocm_fulfillment/warehouse_peachtree' )->importCsv ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'peachtree' )->__ ( 'Peachtree Import Uploaded Successfully' ) );
				$this->_redirect ( '*/*/' );
				// this way the name is saved in DB
				$data ['filename'] = $_FILES ['filename'] ['name'];
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'peachtree' )->__ ( 'Please choose a file to upload' ) );
			}
		}
		
		$this->_redirect ( '*/*/' );
	}
	
	public function convertAmazon() {
		try {
			/* Starting upload */
			$uploader = new Varien_File_Uploader ( 'amazon_converter' );
					
			// Any extention would work
			$uploader->setAllowedExtensions ( array (
					'txt','csv'
			) );
			$uploader->setAllowRenameFiles ( false );
			
			// Set the file upload mode
			// false -> get the file directly in the specified folder
			// true -> get the file in the product like folders
			// (file.jpg will go in something like /media/f/i/file.jpg)
			$uploader->setFilesDispersion ( false );
			
			// We set media as the upload dir
			$path = Mage::getBaseDir ( 'media' ) . DS;
			$uploader->save ( $path, 'amazon_converter.csv');
			
			$dataStart = 4;
			$row_count = 0;
			$handle = fopen ( $path . 'amazon_converter.csv', 'r' );
			for($row_count = 1; $row_count < $dataStart; $row_count ++) {
				fgetcsv ( $handle, 0, "\t" );
			}
			$header = array_map ( 'strtolower', fgetcsv ( $handle, 0, "\t" ) );
			
			$oldId = NULL;
			
			$fba = "SWM";
			
			while ( ($row = fgetcsv ( $handle, 0, "\t")) !== false ) {
				$rows[] = $row;
			}
			
			$date = date('n/j/Y',strtotime($rows[0][0]));
			$aMethod = "ACH";

			$oldId = null;
					
			$csvData = array();		
			$fbaData = array();	
			foreach($rows as $row) {
				$orderId =  $this->findVal($row,$header,'order id');
				//var_dump($row);
				if ($oldId != $orderId && $oldId) {
					$data = array (
							$date,
							'AMAZON' . $fba,
							'Online Sales',
							$aMethod,
							$date,
							'Amazon PP',
							'10104',
							$sumText,
							$countText,
							$oldId,
							$amount * -1
					);
					if ($fba == 'FBA' && $amount)
						$csvData[] = $data;
					elseif ($amount)
						$fbaData[] = $data;
					
					$amount = 0; 
					$shipping = 0;
					$fba = "SWM";
				}
			
				
				
				if (substr($row[2],-3) == 'FBA')
					$fba = "FBA";
					
				$amt = str_replace(',','',substr($row[6],1));
				if ($row[4] == 'Product charges') {
					$amount = $amt;
				} elseif ($row[4] == 'Other') {
					$shipping += $amt;
				} elseif ($row[4] == 'Promo rebates') {
					$shipping += $amt;
				} 
				$oldId = $orderId;
				
			}
			
			$data = array (
							$date,
							'AMAZON' . $fba,
							'Online Sales',
							$aMethod,
							$date,
							'AMAZON',
							'10104',
							$sumText,
							$countText,
							$oldId,
							($amount + $shipping) * -1
					);
			if ($fba == 'FBA' && $amount)
						$csvData[] = $data;
					elseif ($amount)
						$fbaData[] = $data;
			
			//Save SWM Data		
			$count = count($csvData);
			$sumText = '=SUM(K1:K' . $count . ')';
			$countText= '=COUNT(K1:K' . $count . ')';
			
			foreach ($csvData as $key => $val) {
				$csvData[$key][7] = $sumText;
				$csvData[$key][8] = $countText;
			}
			
			$filename = $path . 'amazon-swm.csv';
			$csv = new Varien_File_Csv ();
			
			$csv->saveData ( $filename, $csvData );
			
			
			//Save FBA Data
			$count = count($fbaData);
			$sumText = '=SUM(K1:K' . $count . ')';
			$countText= '=COUNT(K1:K' . $count . ')';
			
			foreach ($fbaData as $key => $val) {
				$fbaData[$key][7] = $sumText;
				$fbaData[$key][8] = $countText;
			}
			
			$filename = $path . 'amazon-fba.csv';
			$csv = new Varien_File_Csv ();
			
			$csv->saveData ( $filename, $fbaData );
			
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'peachtree' )->__ ( '<a href="/media/amazon-swm.csv?rand=' . rand () . '" target="_blank">Amazon - AMAZONSWM</a>' ) );
			
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'peachtree' )->__ ( '<a href="/media/amazon-fba.csv?rand=' . rand () . '" target="_blank">Amazon - AMAZONFBA</a>' ) );
			
			
		} catch ( Exception $e ) {
			$this->_getSession ()->addError ( $e->getMessage () );
		}
	
	}
	
	public function convertAction() {
	
		$adminUserModel = Mage::getModel('admin/user');
		$userCollection = $adminUserModel->getCollection()->load(); 
		$adminUsers = array();
		foreach($userCollection as $user) {
			$adminUsers[] = $user->getUsername();
		}
		
		$data = $this->getRequest ()->getPost ();
		if (! empty ( $data )) {
		
			//If amazon file uploaded, redirect request
			if (isset ( $_FILES ['amazon_converter'] ['name'] ) && $_FILES ['amazon_converter'] ['name'] != '') {
				$this->convertAmazon();
				
			}
			
			if (isset ( $_FILES ['orbital_converter'] ['name'] ) && $_FILES ['orbital_converter'] ['name'] != '') {
				try {
					/* Starting upload */
					$uploader = new Varien_File_Uploader ( 'orbital_converter' );
					
					// Any extention would work
					$uploader->setAllowedExtensions ( array (
							'csv' 
					) );
					$uploader->setAllowRenameFiles ( false );
					
					// Set the file upload mode
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders
					// (file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion ( false );
					
					// We set media as the upload dir
					$path = Mage::getBaseDir ( 'media' ) . DS;
					$uploader->save ( $path, 'orbital_converter.csv' );
					
					$dataStart = 5;
					$row_count = 0;
					$handle = fopen ( $path . 'orbital_converter.csv', 'r' );
					for($row_count = 1; $row_count < $dataStart; $row_count ++) {
						fgetcsv ( $handle, 1024, '\t' );
					}
					$header = array_map ( 'strtolower', fgetcsv ( $handle ) );
					
					$outFiles = array ();
					// $outFiles['visa/mc'] = array('name' => 'Visa/MasterCard',
					// 'filename' => 'pt-visa-mc.csv',
					// 'data' => array());
					$outFiles ['amex'] = array (
							'name' => 'American Express',
							'filename' => 'pt-amex.csv',
							'data' => array () 
					);
					$outFiles ['visa/mc/disc'] = array (
							'name' => 'Visa/MasterCard/Discover',
							'filename' => 'pt-visa-mc-disc.csv',
							'data' => array () 
					);
					$outFiles ['wholesale'] = array (
							'name' => 'Wholesale',
							'filename' => 'pt-wholesale.csv',
							'data' => array () 
					);
					$outFiles ['credits'] = array (
							'name' => 'Credits',
							'filename' => 'pt-credits.csv',
							'data' => array () 
					);
					
					$rows = array();
					
					
					while ( ($row = fgetcsv ( $handle, 0, ',', '"' )) !== false ) {
						$rows[] = $row;
						$date = date ( 'n/j/Y', strtotime ( $this->findItem ( $row, $header, array (
									'datetime' 
							) ) ) );
							
						if (strtotime($date) > strtotime($batchDate))
							$batchDate = $date;
					}
					
					foreach($rows as $row) {
						$responseCode = trim ( $this->findItem ( $row, $header, array (
								'resp code' 
						) ) );
						$amount = str_replace ( '$', '', $this->findItem ( $row, $header, array (
								'amount' 
						) ) );
						$amount = str_replace ( ',', '', $amount );
						$actionCode = $this->findItem ( $row, $header, array (
								'transaction type' 
						) );
						if (($responseCode == '00' || $responseCode == '') && $amount > 0 && in_array ( $actionCode, array (
								'Tran Accepted - Sales',
								'Tran Accepted - Credit' 
						) )) {
							$date = date ( 'n/j/Y', strtotime ( $this->findItem ( $row, $header, array (
									'datetime' 
							) ) ) );
							$orderID = $this->findItem ( $row, $header, array (
									'order #' 
							) );
							
							if ($actionCode != 'Tran Accepted - Credit') {
								$amount *= - 1.0;
							}
							
							switch ($this->findItem ( $row, $header, array (
									'card type' 
							) )) {
								case 'Visa' :
									$method = 'VISA';
									break;
								case 'American Express' :
									$method = 'AMEX';
									break;
								case 'MasterCard' :
									$method = 'MC';
									break;
								case 'Discover' :
									$method = 'DISC';
									break;
								default :
									$method = '';
							}
							
							$data = array (
									$date,
									'OO',
									'Online Sales',
									'Orbital',
									$date,
									$method,
									'10104',
									'',
									'',
									$orderID,
									$amount 
							);
							
							if ($actionCode == 'Tran Accepted - Credit') {
								$customer_id = 'O' . date ( 'my', strtotime ( $date ) );
								$data[1] = $customer_id;
								$outFiles ['credits'] ['data'] [0] [] = $data;
							} else if (! preg_match ( '/^[0-9]+$/', $orderID )) {
								$customer_id = 'O' . date ( 'my', strtotime ( $date ) );
								$data[1] = $customer_id;
								$outFiles ['wholesale'] ['data'] [0] [] = $data;
							} else {
								$dayOfWeek = date ( 'w', strtotime ( $batchDate ) );
								
								// Retrieve proper customer ID & separate the files
								$orders = Mage::getModel ( 'sales/order' )->loadByIncrementId ($orderID);

								$customer_id = 'O' . date ( 'my', strtotime ( $orders->getData ( 'created_at' ) ) );
								if (!$orders->getData('created_at'))
									$customer_id = 'O' . date ( 'my', strtotime ( $date ) );
	
								//Load order referer and add W to customer ID if wholesale
								$peach = Mage::getModel('peachtree/referer')->loadByAttribute('order_id',(int)$orders->getId());

					         	if ($peach) {
					         		if (in_array($peach->getRefererId(), $adminUsers))
					            		$customer_id = $customer_id . 'W';
					            }
					            	
								$data = array (
										'',
										$customer_id,
										'Online Sales',
										'',
										'',
										$method,
										'10104',
										'',
										'',
										$orderID,
										$amount 
								);
								switch ($method) {
									case 'VISA' :
									case 'MC' :
									case 'DISC' :
										$data [3] = 'Orb ' . date ( 'n/j', strtotime ( $batchDate)) . ' Visa/MC/Disc';
										
										// If day is Sunday-Thursday, just add one day. Else, set it to Monday
										if ($dayOfWeek < 5) {
											$date = date ( 'n/j/Y', strtotime ( $batchDate . ' + 1 day' ) );
										} else {
											$date = date ( 'n/j/Y', strtotime ( $batchDate . ' + ' . (8 - $dayOfWeek) . ' days' ) );
										}
										$data [0] = $date;
										$data [4] = $date;
										
										$outFiles ['visa/mc/disc'] ['data'] [$customer_id] [] = $data;
										break;
									case 'AMEX' :
										$data [3] = 'Orb ' . date ( 'n/j', strtotime ( $batchDate)) . ' Amex';
										
										// Set the date to the following logic
										/*
										 * Monday > following Friday
										 * Tues-Thurs > following Monday
										 * Fri-Sun > following Tuesday
										 */
										if ($dayOfWeek == 1) {
											$date = date ( 'n/j/Y', strtotime ( $batchDate . ' + 4 days' ) );
										} elseif ($dayOfWeek > 1 && $dayOfWeek < 5) {
											$date = date ( 'n/j', strtotime ( $batchDate . ' + ' . (8 - $dayOfWeek) . ' days' ) );
										} elseif ($dayOfWeek >= 5) {
											$date = date ( 'n/j/Y', strtotime ( $batchDate . ' + ' . (9 - $dayOfWeek) . ' days' ) );
										} elseif ($dayOfWeek == 0) {
											$date = date ( 'n/j/Y', strtotime ( $batchDate . ' + 2 days' ) );
										}
			
										$data [0] = $date;
										$data [4] = $date;
										
										$outFiles ['amex'] ['data'] [$customer_id] [] = $data;
										break;
								}
							}
						}
					}
					fclose ( $handle );
					
					foreach ( $outFiles as $outFile ) {
						$total = 0;
						if ($count = sizeof ( $outFile ['data'] )) {

//							$outFile['data'][0][1] = 'OO';
//							$outFile['data'][0][3] = 'Orbital';

							
							foreach ($outFile['data'] as $customer_key => $customer_files) 
							{
								$filename = $path . $customer_key . '-' . $outFile ['filename'];
								$csv = new Varien_File_Csv ();
								$count = sizeof ( $customer_files );
								$sum = 0;
								$oCount = 0;
								for($i = 0; $i < $count; $i ++) {
									$sum += $outFile ['data'] [$customer_key] [$i] [10];
									$oCount++;
								}
								
								for($i = 0; $i < $count; $i ++) {
									
									$outFile ['data'] [$customer_key] [$i] [7] = $sum;
									$outFile ['data'] [$customer_key] [$i] [8] = $oCount;
									
									$outFile ['data'] [$customer_key] [$i] [7] = '=SUM(K1:K' . $count . ')';
									$outFile ['data'] [$customer_key] [$i] [8] = '=COUNT(K1:K' . $count . ')';
								}
								
								$csv->saveData ( $filename, $outFile ['data'] [$customer_key] );
							}
						}
					}
					
					foreach ( $outFiles as $outFile ) {
						if (sizeof ( $outFile ['data'] )) {
							foreach ($outFile['data'] as $customer_key => $customer_file) {
								$name = (strcmp($customer_key, '0') != 0) ? $outFile ['name'] . ' - ' . $customer_key : $outFile ['name'];
								Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'peachtree' )->__ ( '<a href="/media/' . $customer_key . '-' . $outFile ['filename'] . '?rand=' . rand () . '" target="_blank">' . $name . '</a>' ) );
							}
						}
					}
				} catch ( Exception $e ) {
					$this->_getSession ()->addError ( $e->getMessage () );
				}
			}
		} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'peachtree' )->__ ( 'Please choose a file to upload' ) );
		}

		$this->_redirect ( '*/*/' );
	}
	public function deleteAction() {
		if ($this->getRequest ()->getParam ( 'id' ) > 0) {
			try {
				$model = Mage::getModel ( 'peachtree/peachtree' );
				
				$model->setId ( $this->getRequest ()->getParam ( 'id' ) )->delete ();
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'Item was successfully deleted' ) );
				$this->_redirect ( '*/*/' );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/edit', array (
						'id' => $this->getRequest ()->getParam ( 'id' ) 
				) );
			}
		}
		$this->_redirect ( '*/*/' );
	}
	public function massDeleteAction() {
		$peachtreeIds = $this->getRequest ()->getParam ( 'peachtree' );
		if (! is_array ( $peachtreeIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Please select item(s)' ) );
		} else {
			try {
				foreach ( $peachtreeIds as $peachtreeId ) {
					$peachtree = Mage::getModel ( 'peachtree/peachtree' )->load ( $peachtreeId );
					$peachtree->delete ();
				}
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'Total of %d record(s) were successfully deleted', count ( $peachtreeIds ) ) );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			}
		}
		$this->_redirect ( '*/*/index' );
	}
	public function massStatusAction() {
		$peachtreeIds = $this->getRequest ()->getParam ( 'peachtree' );
		if (! is_array ( $peachtreeIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $this->__ ( 'Please select item(s)' ) );
		} else {
			try {
				foreach ( $peachtreeIds as $peachtreeId ) {
					$peachtree = Mage::getSingleton ( 'peachtree/peachtree' )->load ( $peachtreeId )->setStatus ( $this->getRequest ()->getParam ( 'status' ) )->setIsMassupdate ( true )->save ();
				}
				$this->_getSession ()->addSuccess ( $this->__ ( 'Total of %d record(s) were successfully updated', count ( $peachtreeIds ) ) );
			} catch ( Exception $e ) {
				$this->_getSession ()->addError ( $e->getMessage () );
			}
		}
		$this->_redirect ( '*/*/index' );
	}
	protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
		$response = $this->getResponse ();
		$response->setHeader ( 'HTTP/1.1 200 OK', '' );
		$response->setHeader ( 'Pragma', 'public', true );
		$response->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true );
		$response->setHeader ( 'Content-Disposition', 'attachment; filename=' . $fileName );
		$response->setHeader ( 'Last-Modified', date ( 'r' ) );
		$response->setHeader ( 'Accept-Ranges', 'bytes' );
		$response->setHeader ( 'Content-Length', strlen ( $content ) );
		$response->setHeader ( 'Content-type', $contentType );
		$response->setBody ( $content );
		$response->sendResponse ();
		die ();
	}
	
	private function findVal($row, $header, $term) {
		if (($pos = array_search ( $term, $header )) !== false) {
			return $row [$pos];
		}
		
		return '';
	}
	
	private function findItem($row, $header, $terms) {
		foreach ( $terms as $term ) {
			if (($pos = array_search ( $term, $header )) !== false) {
				return $row [$pos];
			}
		}
		return '';
	}
}
