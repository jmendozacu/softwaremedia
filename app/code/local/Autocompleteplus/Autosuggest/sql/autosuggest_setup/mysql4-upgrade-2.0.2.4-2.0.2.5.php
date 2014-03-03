<?php

$helper=Mage::helper('autosuggest');

$keyOld=$helper->getKey();


//getting site url
$url=$helper->getConfigDataByFullPath('web/unsecure/base_url');

//getting site owner email
$storeMail=$helper->getConfigDataByFullPath('autocompleteplus/config/store_email');

//getting site design theme package name
$package=$helper->getConfigDataByFullPath('design/package/name');



$collection=Mage::getModel('catalog/product')->getCollection();
//$productCount=$collection->count();


$multistoreJson=$helper->getMultiStoreDataJson();

try{

    $commandOrig="http://magento.autocompleteplus.com/install";

    $data=array();
    $data['multistore']=$multistoreJson;

    $key=$helper->sendPostCurl($commandOrig,$data);

    if(strlen($key)>50){
        $key='InstallFailedUUID';
    }

    Mage::log(print_r($key,true),null,'autocomplete.log');

    $errMsg='';
    if($key=='InstallFailedUUID'){
        $errMsg.='Could not get license string.';
    }

    if($package=='base'){
        $errMsg.= ';The Admin needs to move autocomplete template files to his template folder';
    }

    if($errMsg!=''){

        $command="http://magento.autocompleteplus.com/install_error";
        $data=array();
        $data['site']=$url;
        $data['msg']=$errMsg;
        $data['email']=$storeMail;
        //$data['product_count']=$productCount;
        $data['multistore']=$multistoreJson;
        $res=$helper->sendPostCurl($command,$data);
    }

}catch(Exception $e){

    $key='failed';
    $errMsg=$e->getMessage();

    Mage::log('Install failed with a message: '.$errMsg,null,'autocomplete.log');

    $command="http://magento.autocompleteplus.com/install_error";

    $data=array();
    $data['site']=$url;
    $data['msg']=$errMsg;
    $data['original_install_URL']=$commandOrig;
    $res=$helper->sendPostCurl($command,$data);
}

$installer = $this;

$installer->startSetup();

$res=$installer->run("UPDATE {$this->getTable('autocompleteplus_config')} SET licensekey='".$key."' WHERE id=1;");

$installer->endSetup();


?>