<?php
$helper=Mage::helper('autosuggest');

$key=$helper->getKey();

$url=$helper->getConfigDataByFullPath('web/unsecure/base_url');

if($key!='InstallFailedUUID' && $key!='failed'){

    $stemapUrl='Sitemap:http://magento.instantsearchplus.com/ext_sitemap?u='.$key.PHP_EOL;

    $robotsPath=Mage::getBaseDir().DS.'robots.txt';

    if (file_exists($robotsPath)) {

        if(is_writable($robotsPath)){

          //append sitemap
            file_put_contents($robotsPath, $stemapUrl, FILE_APPEND | LOCK_EX);
        }else{
            //write message that file is not writteble
            $command="http://magento.autocompleteplus.com/install_error";

            $data=array();
            $data['site']=$url;
            $data['msg']='File '.$robotsPath.' is not writable.';
            $res=$helper->sendPostCurl($command,$data);
        }

    }else{
        //create file
        if(is_writable(Mage::getBaseDir())){

            //create robots sitemap
            file_put_contents($robotsPath,$stemapUrl);
        }else{

            //write message that directory is not writteble
            $command="http://magento.autocompleteplus.com/install_error";

            $data=array();
            $data['site']=$url;
            $data['msg']='Directory '.Mage::getBaseDir().' is not writable.';
            $res=$helper->sendPostCurl($command,$data);
        }
    }
}