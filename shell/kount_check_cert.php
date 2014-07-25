<?php
// @codingStandardsIgnoreStart
/**
 * StoreFront Consulting Kount Magento Extension
 *
 * PHP version 5
 *
 * @category  SFC
 * @package   SFC_Kount
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 *
 */
// @codingStandardsIgnoreEnd

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

umask(0);

// Log mem usage
Mage::log('Memory usage: ' . memory_get_usage(), Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
echo "\n";
echo 'Memory usage: ' . memory_get_usage() . "\n";
echo "\n";
echo "\n";

try {
    // Output
    echo "Checking Kount certificate / key configuration...\n";
    echo "----------------------------------------------------------------------\n";
    // Get credentials / cert info from config
    $pathHelper = Mage::helper('kount/paths');
    $keyFile = $pathHelper->getKeyFilePath();    
    $certFile = $pathHelper->getCertFilePath();
    $keyPassword = Mage::helper('core')->decrypt(Mage::getStoreConfig('kount/cert/password'));

    // Output
    echo 'Certificate file: ' . $certFile . "\n";
    echo 'Key file: ' . $keyFile . "\n";
    echo 'Key password: ' . $keyPassword . "\n";
    echo "\n";
	
    // Locals to hold results of exec() calls    
	$output = array();
	$returnVar = 0;
    // Output
    echo "Checking certificate file...\n";
    echo "----------------------------------------------------------------------\n";
    // Build command
    $cmd = 'openssl x509 -noout -modulus -in ' . $certFile;
    echo $cmd . "\n";
	// Call openssl on cert file
	exec($cmd, $output, $returnVar);
	// Output
	echo "openssl output: \n";
	foreach($output as $curLine) {
        echo $curLine . "\n";
    }
	// Check output
	if($returnVar !== 0) {
	    Mage::log("openssl output: \n" . $output, Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	    Mage::throwException('Failed to open and / or process certificate file: ' . $certFile);
	}
	$certModulus = $output[0];
    echo "\n";
	
    // Locals to hold results of exec() calls    
	$output = array();
	$returnVar = 0;
    // Output
    echo "Checking key file...\n";
    echo "----------------------------------------------------------------------\n";
    // Build command
    $cmd = 'openssl rsa -noout -modulus -in ' . $keyFile . ' -passin pass:' . $keyPassword;
    echo $cmd . "\n";
	// Call openssl on key file
	exec($cmd, $output, $returnVar);
	// Output
	echo "openssl output: \n";
	foreach($output as $curLine) {
        echo $curLine . "\n";
    }
	// Check output
	if($returnVar !== 0) {
	    Mage::log("openssl output: \n" . $output, Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
	    Mage::throwException('Failed to open and / or process key file: ' . $keyFile . "\nIt's possible that the password doesn't match the key, or the key file is corrupt.");
	}
	$keyModulus = $output[0];
    echo "\n";
	
    echo "Results:\n";
    echo "----------------------------------------------------------------------\n";
	// Now check that cert & key match
	if($certModulus === $keyModulus) {
	    echo "Certificate file and key file match!\n";
	}
	else {
	    Mage::throwException('Certificate file and key file do not match!');
	}	

} catch (Exception $e) {
	Mage::log($e, Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
    echo $e->getMessage() . "\n";
}

// Log mem usage
Mage::log('Memory usage: ' . memory_get_usage(), Zend_Log::DEBUG, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
echo "\n";
echo "\n";
echo 'Memory usage: ' . memory_get_usage() . "\n";
echo "\n";

