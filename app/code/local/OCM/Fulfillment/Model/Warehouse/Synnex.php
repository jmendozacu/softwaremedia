<?php
class OCM_Fulfillment_Model_Warehouse_Synnex extends OCM_Fulfillment_Model_Warehouse_Abstract
{
    const SYNNEX_SKU_ATTR = 'synnex';
    const SYNNEX_PRICE_ATTR = 'unit_cost';
    const SYNNEX_QTY_ATTR = 'qty_on_hand_total';
    
    protected $_collection = array();
	protected $_collectionList = array();
    public function _construct(){
        parent::_construct();
        $this->_init('ocm_fulfillment/warehouse_synnex');
    }
    
    public function urlConnect(){
       
        $this->tmp_dir = Mage::getBaseDir() . DS . "var" . DS . "synnex_data";
        if (!file_exists($this->tmp_dir)) {
            mkdir($this->tmp_dir,0775,true);
        }
    
        $ftp_server = "ftp.synnex.com";
        $ftp_user = "u520985";
        $ftp_pass = "c6h2b4v5";
        $local_file = $this->tmp_dir . DS . '520985.zip';
        $server_file = '520985.zip';

        // set up a connection or die
        $conn_id = ftp_connect($ftp_server); 
        
        if (!$conn_id) return;

        // try to login
        if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            //echo "Connected as $ftp_user@$ftp_server\n";
        } else {
            Mage::log("Couldn't connect as $ftp_user\n");
        }
        
        // try to download $server_file and save to $local_file
        if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
            //echo "Successfully written to $local_file\n";
        } else {
            Mage::log("There was a problem\n");
        }
        
        try {
            ftp_get($conn_id, $local_file, $server_file, FTP_BINARY);
        } catch (Exception $e){
            Mage::log($e->getMessage());
        }

        if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
            $zip = new ZipArchive;
            $zip->open($local_file);
            $zip->extractTo($this->tmp_dir);
            
            chmod('../var/synnex_data/520985.ap',0777);
            $zip->close();
    
        } else {
            Mage::log("There was a problem\n");
        }
        // close the connection
        ftp_close($conn_id);    
        $this->insertSynnexData();
    }
    
    public function insertSynnexData(){
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $truncateQuery = "TRUNCATE TABLE ocm_fulfillment_synnex;";
        $writeConnection->query($truncateQuery);

        $table = "ocm_fulfillment_synnex";
        $file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');
        
        $query = "insert into ocm_fulfillment_synnex (synnex_sku,qty_on_hand_total,unit_cost) values ";
        $values = array();
        
        while(! feof($file)) {
            $fileArr=fgetcsv($file,0,'~');
//          $query = "insert into ocm_fulfillment_synnex (trading_partner_code,detail_record_id,mfg_part_num,synnex_internal_use,synnex_sku,status_code,part_desc,manufacturer_name,synnex_internal_use2,qty_on_hand_total,synnex_internal_use3,contract_price,msrp,warehouse_qty_on_hand,warehouse_qty_on_hand2,returnable_flag,warehouse_qty_on_hand3,parcel_shippable,warehouse_qty_on_hand4,unit_cost,warehouse_qty_on_hand5,media_type,warehouse_qty_on_hand6,synnex_cat_code,warehouse_qty_on_hand7,synnex_internal_use4,ship_weight,serialized_flag,warehouse_qty_on_hand8,warehouse_qty_on_hand9,synnex_reserved_use,upc_code,unspsc_code,synnex_internal_use5,sku_created_date,one_source_flag,eta_date,abc_code,kit_stand_alone_flag,state_gov_price,federal_gov_price,educational_price,taa_flag,gsa_pricing,promotion_flag,promotion_comment,promotion_expiration_date,long_desc1,long_desc2,long_desc3,length_width_height,synnex_reserved_use2,gsa_nte_price,platform_type,product_description,warehouse_qty_on_hand10,warehouse_qty_on_hand11,synnex_reserved_use3,synnex_reserved_use4,synnex_reserved_use5,replacement_sku,minimum_order_qty,purchasing_requirements,gov_class,warehouse_qty_on_hand12,mfg_drop_ship_warehouse_qty,update_time) values ('".$fileArr[0]."','".$fileArr[1]."','".$fileArr[2]."','".mysql_real_escape_string($fileArr[3])."','".mysql_real_escape_string($fileArr[4])."','".$fileArr[5]."','".mysql_real_escape_string($fileArr[6])."','".$fileArr[7]."','".$fileArr[8]."','".$fileArr[9]."','".$fileArr[10]."','".$fileArr[11]."','".$fileArr[12]."','".$fileArr[13]."','".$fileArr[14]."','".$fileArr[15]."','".$fileArr[16]."','".$fileArr[17]."','".$fileArr[18]."','".$fileArr[19]."','".$fileArr[20]."','".$fileArr[21]."','".$fileArr[22]."','".$fileArr[23]."','".$fileArr[24]."','".$fileArr[25]."','".$fileArr[26]."','".$fileArr[27]."','".$fileArr[28]."','".$fileArr[29]."','".$fileArr[30]."','".$fileArr[31]."','".$fileArr[32]."','".$fileArr[33]."','".$fileArr[34]."','".$fileArr[35]."','".$fileArr[36]."','".$fileArr[37]."','".$fileArr[38]."','".$fileArr[39]."','".$fileArr[40]."','".$fileArr[41]."','".$fileArr[42]."','".$fileArr[43]."','".$fileArr[44]."','".$fileArr[45]."','".$fileArr[46]."','".$fileArr[47]."','".$fileArr[48]."','".$fileArr[49]."','".$fileArr[50]."','".$fileArr[51]."','".$fileArr[52]."','".$fileArr[53]."','".$fileArr[54]."','".$fileArr[55]."','".$fileArr[56]."','".$fileArr[57]."','".$fileArr[58]."','".$fileArr[59]."','".$fileArr[60]."','".$fileArr[61]."','".$fileArr[62]."','".$fileArr[63]."','".$fileArr[64]."','".$fileArr[65]."','".$fileArr[66]."');";
            
            
            $values[] = "('".$fileArr[4]."','".$fileArr[9]."','".$fileArr[20]."')";
        }
        $query = $query . implode(',', $values) . ";";
        $writeConnection->query($query);

        //Empty the directory where the data is downloaded and unzipped
        function rrmdir($dir) {
            foreach(glob($dir . '/*') as $file) {
                if(is_dir($file))
                    rrmdir($file);
                else
                    unlink($file);
            }
            rmdir($dir);
        }
        rrmdir($this->tmp_dir);
        fclose($file);
    }
        

    public function getQty($sku){
        
        $product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
		$stock = $product->toArray($product);

		return (int)$product->getSynnexQty();
    }

    public function getPrice($sku){
        
        $product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
		$stock = $product->toArray($product);

		return (int)$product->getSynnexPrice();
    }
    public function loadCollectionArray($collection) {

        $collection->addAttributeToSelect(self::SYNNEX_SKU_ATTR);
        $cloned_collection = clone $collection;

          foreach ($cloned_collection as $product) {
            if($synnex_sku = $product->getData(self::SYNNEX_SKU_ATTR)) {
                $this->_collectionList[$synnex_sku] = array('id'=>$product->getId(),'price'=>'','qty'=>'');
            }
          }
                    
        $col = $this->getCollection()
            ->addFieldToFilter('synnex_sku',array('in'=>array_keys($this->_collectionList)));
        foreach($col as $item) {
            $synnex_sku = trim($item->getData('synnex_sku'));
            $this->_collection[ $synnex_sku ]['id'] = $this->_collectionList[$synnex_sku]['id'];
            $this->_collection[ $synnex_sku ]['price'] = str_replace('$','',$item->getData(self::SYNNEX_PRICE_ATTR));
            $this->_collection[ $synnex_sku ]['qty'] = $item->getData(self::SYNNEX_QTY_ATTR);
        }
        
        $this->setData('collection_array',$this->_collection);
        return $this;
    }



}
