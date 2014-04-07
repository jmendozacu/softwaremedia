<?php
//uncomment when moved to server - to ensure this page is not accessed from anywhere else
//if ($_SERVER['REMOTE_ADDR'] !== '<your server ip address') {
//  die("You are not a cron job!");
//}
echo getcwd();
die();
require_once '../app/Mage.php';
// wget -O - http://<www.example.com>/Cron_Import.php/?files=3XSEEEE.csv
  umask(0);

  //$_SERVER['SERVER_PORT']='443';
  Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 

  $profileId = 1; //put your profile id here
  
  $filename =  'cpc_expost.log';  
  $recordCount = 0;

  Mage::log("Import Started",null,$logFileName);  
 
  $profile = Mage::getModel('dataflow/profile');
  
  $userModel = Mage::getModel('admin/user');
  $userModel->setUserId(0);
  Mage::getSingleton('admin/session')->setUser($userModel);
  
  if ($profileId) {
    $profile->load($profileId);
    if (!$profile->getId()) {
      Mage::getSingleton('adminhtml/session')->addError('The profile you are trying to save no longer exists');
    }
  }

  Mage::register('current_convert_profile', $profile);

  $profile->run();
  
  $batchModel = Mage::getSingleton('dataflow/batch');
  if ($batchModel->getId()) {
    if ($batchModel->getAdapter()) {
      $batchId = $batchModel->getId(); 
      $batchImportModel = $batchModel->getBatchImportModel();
      $importIds = $batchImportModel->getIdCollection();  

      $batchModel = Mage::getModel('dataflow/batch')->load($batchId);      
      $adapter = Mage::getModel($batchModel->getAdapter());
      foreach ($importIds as $importId) {
        $recordCount++;
        try{
          $batchImportModel->load($importId);
          if (!$batchImportModel->getId()) {
             $errors[] = Mage::helper('dataflow')->__('Skip undefined row');
             continue;
          }

          $importData = $batchImportModel->getBatchData();
          try {
            //$adapter->saveRow($importData);
          } catch (Exception $e) {
            Mage::log($e->getMessage(),null,$logFileName);          
            continue;
          }
        
          if ($recordCount%20 == 0) {
            Mage::log($recordCount . ' - Completed!!',null,$logFileName);
          }
        } catch(Exception $ex) {
          Mage::log('Record# ' . $recordCount . ' - SKU = ' . $importData['sku']. ' - Error - ' . $ex->getMessage(),null,$logFileName);        
        }
      }
      foreach ($profile->getExceptions() as $e) {
        Mage::log($e->getMessage(),null,$logFileName);          
      }
      
    }
  }
  echo 'Import Completed';
  Mage::log("Import Completed",null,$logFileName);
 ?>