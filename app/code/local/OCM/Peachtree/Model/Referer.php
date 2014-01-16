<?php

class OCM_Peachtree_Model_Referer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('peachtree/referer');
    }
    
    static public function getNameByCode($code) {
    
        $referers = new Varien_Object(array( 
    
            'PGR' => 'PriceGrabber',
            'FRO' => 'Froogle',
            'CJ' => 'Commission Junction',
            'NXT' => 'Nextag',
            'GGL' => 'Google Adwords',
            'Email' => 'Email Blast',
            //‘Wholesale’ if the wholesale checkbox is checked on the order info page (we’ll need an option for this on the order info page in Magento)
            'Direct' => 'Direct',
            'BNG' => 'Bing Shopping',
            'BUYM' => 'Buy.com',
            'BEST' => 'Best Buy',
            'AdCenter' => 'MSN Adcenter',
            //‘Unknown’ for anything else

            )
        )
        ;
        
        $name = ($referers->getData($code)) ? $referers->getData($code) : 'Unknown';
        
        return $name;
        

    }
    
    
}