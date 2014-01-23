<?php
class OCM_Fulfillment_Model_Warehouse_Ingram extends OCM_Fulfillment_Model_Warehouse_Abstract
{
    const INGRAM_SKU_ATTR = 'ingram_micro_usa';
    const INGRAM_PRICE_ATTR = 'cust_cost';
    const INGRAM_QTY_ATTR = 'avail_stock_flag';
    
    protected $_collection = array();

    public function _construct() {
        parent::_construct();
        $this->_init('ocm_fulfillment/warehouse_ingram');
    }

    protected function _getQty($item) {
        
        $qty = 0;
        if($item->getData(self::INGRAM_QTY_ATTR)=='Y'){
            $qty = 999;
        }
        return $qty;
        
    }
    
    public function urlConnect(){
    
        $ftp_server = "ftpsecure.ingrammicro.com";
        $path="/FUSION/US/CPLH36";
        $ftp_user = "920894";
        $ftp_pass = "T49h27";
        $local_dir = Mage::getBaseDir().'/var/ingram_data';
        $local_file_name = 'ingramdata.zip';
        $local_file = $local_dir . '/' . $local_file_name;
        $server_file = 'PRICE.zip';

        //check if ingram dir exists, if not make it
        if(!file_exists($local_dir)) {
            shell_exec('mkdir -p '.$local_dir);
        }

        // set up a connection
        $conn_id = ftp_connect($ftp_server); 
        
        if (!$conn_id) return;

        // try to login
        if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            //echo "Connected as $ftp_user@$ftp_server\n";
        } else {
            Mage::log("Couldn't connect as $ftp_user\n");
        }
        
        
        // try to change the directory to somedir
        if (ftp_chdir($conn_id, $path)) {
            //echo "Current directory is now: " . ftp_pwd($conn_id) . "\n";
        } else { 
            Mage::log("Couldn't change directory\n");
        }
        Mage::log("Getting Zip: " . $local_file,null,'Ingram.log');
        // try to download $server_file and save to $local_file
        if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
        	Mage::log("Getting Zip: " . $local_file,null,'Ingram.log');
            $zip = new ZipArchive;
            $zip->open('../var/ingram_data/ingramdata.zip');
            $zip->extractTo('../var/ingram_data/ingramdata');
            chmod('../var/ingram_data/ingramdata',0777);
            chmod('../var/ingram_data/ingramdata/PRICE.TXT',0777);
            $zip->close();
    
        } else {
           Mage::log("There was a problem\n");
        }
		Mage::log("Closing Connection\n",null,'Ingram.log');
        // close the connection
        ftp_close($conn_id);    
        $this->insertIngramData();
    }
    
    
    public function insertIngramData() {
    
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $truncateQuery = "TRUNCATE TABLE ocm_fulfillment_ingram;";
        $writeConnection->query($truncateQuery);

		Mage::log(Mage::getBaseDir()."/var/ingram_data/ingramdata/PRICE.TXT",null,"Ingram.log");
        $table = "ocm_fulfillment_ingram";
        if (!$file = fopen(Mage::getBaseDir()."/var/ingram_data/ingramdata/PRICE.TXT","r"))
			Mage::log("Error FOPEN",null,"Ingram.log");
		else
			Mage::log("File OPENED",null,"Ingram.log");
			
        while(! feof($file)) {
            $fileArr=fgetcsv($file);
            $query = "insert into ocm_fulfillment_ingram (change_code,price_part_nbr,price_vendor_nbr,price_vendor_name,price_desc1,price_desc2,price_retail,price_vendor_part,weight,upc_code,length,width,height,cust_cost_c,cust_cost,product_key,avail_stock_flag,price_status,new_cpu_code,new_media,new_cat_sub,whse_has_stock_sw,cost_rebate_appl_sw) values ('".$fileArr[0]."','".$fileArr[1]."','".$fileArr[2]."','".$fileArr[3]."','".$fileArr[4]."','".$fileArr[5]."','".$fileArr[6]."','".$fileArr[7]."','".$fileArr[8]."','".$fileArr[9]."','".$fileArr[10]."','".$fileArr[11]."','".$fileArr[12]."','".$fileArr[13]."','".$fileArr[14]."','".$fileArr[15]."','".$fileArr[16]."','".$fileArr[17]."','".$fileArr[18]."','".$fileArr[19]."','".$fileArr[20]."','".$fileArr[21]."','".$fileArr[22]."');";
            $writeConnection->query($query);
        }
        
        //Empty the directory where the data is downloaded and unzipped
        function rrrrmdir($dir) {
            foreach(glob($dir . '/*') as $file) {
                if(is_dir($file))
                    rrrmdir($file);
                else
                    unlink($file);
            }
            rmdir($dir);
        }
        fclose($file);
        
        $dir_name=Mage::getBaseDir().'/var/ingram_data/ingramdata';
        $zip_name=Mage::getBaseDir().'/var/ingram_data/ingramdata.zip';
        unlink($zip_name);
        rrrrmdir($dir_name);
        
    }
    
    public function getQty($sku){
    
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
        $this->load($product->getData(self::INGRAM_SKU_ATTR),'price_part_nbr');
        return $this->_getQty($this);
        
    }
    
    
    public function getPrice($sku) {
    
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
        
        $this->load($product->getData(self::INGRAM_SKU_ATTR),'price_part_nbr');
        return $this->getData(self::INGRAM_PRICE_ATTR);
        
    }


    public function loadCollectionArray($collection) {

        $collection->addAttributeToSelect(self::INGRAM_SKU_ATTR);
        $cloned_collection = clone $collection;

        foreach ($cloned_collection as $product) {
            if($ingram_sku = $product->getData(self::INGRAM_SKU_ATTR)) {
                $this->_collection[$ingram_sku] = array('id'=>$product->getId(),'price'=>'','qty'=>'');
            }
        }
                    
        $col = $this->getCollection()
            ->addFieldToFilter('price_part_nbr',array('in'=>array_keys($this->_collection)));
            
        foreach($col as $item) {
            $ingram_sku = trim($item->getData('price_part_nbr'));
            $this->_collection[ $ingram_sku ]['price'] = str_replace('$','',$item->getData(self::INGRAM_PRICE_ATTR));
            $this->_collection[ $ingram_sku ]['qty'] = $this->_getQty($item);
        }
        
        $this->setData('collection_array',$this->_collection);
        return $this;
    }


}
