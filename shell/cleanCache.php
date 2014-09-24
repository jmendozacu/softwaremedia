<?php
date_default_timezone_set("America/Denver");
echo "Start Cleaning all caches at ... " . date("Y-m-d H:i:s") . "\n\n";
ini_set("display_errors", 1);
$path = substr(realpath(dirname(__FILE__)),0,-5);

require $path . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
Mage::getConfig()->init();

$types = Mage::app()->getCacheInstance()->getTypes();

try {
    echo "Cleaning data cache... \n";
    flush();
    foreach ($types as $type => $data) {
        echo "Removing $type ... ";
        echo Mage::app()->getCacheInstance()->clean($data["tags"]) ? "[OK]" : "[ERROR]";
        echo "\n";
    }
} catch (exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}

echo "\n";

/*

try {
    echo "Cleaning merged JS/CSS...";
    flush();
    $dir = Mage::getBaseDir('media') . DS . 'css';
	Varien_Io_File::rmdirRecursive($dir);
	
	$dir = Mage::getBaseDir('media') . DS . 'css_secure';
	Varien_Io_File::rmdirRecursive($dir);

                //Mage::getModel('core/design_package')->cleanMergedJsCss();
    Mage::dispatchEvent('clean_media_cache_after');
    echo "[OK]\n\n";
} catch (Exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}

try {
    echo "Cleaning image cache... ";
    flush();
    echo Mage::getModel('catalog/product_image')->clearCache();
    echo "[OK]\n";
} catch (exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}
*/

try {
    echo "Cleaning stored cache... ";
    flush();
    echo Mage::app()->getCacheInstance()->clean() ? "[OK]" : "[ERROR]";
    echo "\n\n";
} catch (exception $e) {
    die("[ERROR:" . $e->getMessage() . "]");
}