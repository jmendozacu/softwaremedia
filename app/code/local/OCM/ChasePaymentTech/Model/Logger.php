<?php

class OCM_ChasePaymentTech_Model_Logger
{ 
    protected $_enabled = 0;
    
    const ERROR_LOG = "ChasePaymentTechError.log";
    const DEBUG_LOG = "ChasePaymentTechDebug.log";
    
    
    public function __construct()
    {
        $this->_enabled = Mage::getStoreConfig('payment/chasePaymentTech/debug',Mage::app()->getStore());
    }
    
    public function debug($message)
    {
        if ($this->_enabled)
        {
            Mage::log("\n".$message,null,self::DEBUG_LOG);
        }
    }
    
    public function error($message)
    {
        Mage::log("\n".$message,null,self::ERROR_LOG);
    }
}