<?php

class Ophirah_Qquoteadv_Model_Substatus
    extends Varien_object 
{
    // Character used for dividing
    // status and substatus
    const EXPLODE_CHARACTER             = '.';
    
    // #### DEFINE SUBSTATUSES ####
    // substatus value is string
    // in this format:
    // [STATUSID][EXPOLODE_CHARACTER][SUBSTATUSID]
    // 
    // EXAMPLE:
    // SUBSTATUS FOR STATUS_REJECTED (value = 30) will look like: 
    // const SUBSTATUS_REJECTED_SUB100  = '30.100';
    //
    // MULTOPLE SUBSTATUSES LOOK LIKE:
    // const SUBSTATUS_REJECTED_SUB100  = '30.100';
    // const SUBSTATUS_REJECTED_SUB200  = '30.200';
    // etc...
    //
    
    // 30 - Substatus Rejected
    const SUBSTATUS_REJECTED_SUB100     = '30.100';
    
    // 40 - Substatus Cancelled
    const SUBSTATUS_CANCELLED_SUB100    = '40.100';
    
    // 50 - Substatus Proposal 
    const SUBSTATUS_PROPOSAL_SUB100	= '50.100';
    const SUBSTATUS_PROPOSAL_SUB200	= '50.200';
    const SUBSTATUS_PROPOSAL_SUB300	= '50.300';


    // 52 - Substatus Proposal Saved
	const SUBSTATUS_SAVED_SUB100	= '52.100';
	const SUBSTATUS_SAVED_SUB200	= '52.200';
    
    /**
     * create array of all substatuses
     * 
     * @return array
     */
    static public function substatuses(){

        $substatuses = array(
            self::SUBSTATUS_REJECTED_SUB100     => Mage::helper('qquoteadv')->__('SUBSTATUS_REJECTED_SUB100'),
            self::SUBSTATUS_CANCELLED_SUB100    => Mage::helper('qquoteadv')->__('SUBSTATUS_CANCELLED_SUB100'),
            self::SUBSTATUS_PROPOSAL_SUB100     => Mage::helper('qquoteadv')->__('SUBSTATUS_PROPOSAL_SUB100'),
            self::SUBSTATUS_PROPOSAL_SUB200     => Mage::helper('qquoteadv')->__('SUBSTATUS_PROPOSAL_SUB200'),
            self::SUBSTATUS_PROPOSAL_SUB300     => Mage::helper('qquoteadv')->__('SUBSTATUS_PROPOSAL_SUB300'),
            self::SUBSTATUS_SAVED_SUB100        => Mage::helper('qquoteadv')->__('SUBSTATUS_SAVED_SUB100'),
            self::SUBSTATUS_SAVED_SUB200        => Mage::helper('qquoteadv')->__('SUBSTATUS_SAVED_SUB200'),
        );
        
        if(count($substatuses) > 0){
            return $substatuses;
        }
        
        return false;
        
    }
    
    // #################### From here no customization needed #######################

    /**
     * Create Option Array for massupdate action
     * 
     * @return array
     */
    static public function getChangeSubOptionArray($statusArray, $substatus = false) {
        $returnArray    = array();
        $subStatusArray = self::_createSubStatusArray();
          
        if(is_array($subStatusArray) && count($subStatusArray) > 0){
            foreach($statusArray as $mainStatus){
                $returnArray[] = $mainStatus;
                if(isset($subStatusArray[$mainStatus['value']])):
                    foreach($subStatusArray[$mainStatus['value']] as $k => $v){
                        $label = ($substatus)?$mainStatus['label'].' '.Mage::helper('qquoteadv')->__($v):$mainStatus['label'];
                        $returnArray[] = array('value' =>$mainStatus['value'].self::EXPLODE_CHARACTER.$k, 'label' => $label);
                    }
                endif;
            }
        }
        
        return $returnArray;
    }
    
    /**
     * Create Option Array for Option listing
     * 
     * @return array
     */
    static public function getSubOptionArray($statusArray, $substatus = false){
        $returnArray    = array();
        $subStatusArray = self::_createSubStatusArray();
        
        if(is_array($subStatusArray) && count($subStatusArray) > 0){
            foreach($statusArray as $mainId => $mainLabel){
                $returnArray[$mainId] = $mainLabel;
                if(isset($subStatusArray[$mainId])):
                    foreach($subStatusArray[$mainId] as $k => $v){
                        $label = ($substatus)?$mainLabel.' '.$v:$mainLabel;
                        $returnArray[$mainId.self::EXPLODE_CHARACTER.$k] = $label;
                    }
                endif;
            }
        }
        
        return $returnArray;        
    }
    
      
    /**
     * Create ordered array of substatusses
     * 
     * @return Array
     */
    static protected function _createSubStatusArray(){

        $returnArray    = array();
        $subStatusArray = self::substatuses(); 
        
        foreach($subStatusArray as $k => $v){
            $subKeys = explode(self::EXPLODE_CHARACTER, $k);
            if(is_array($subKeys) && count($subKeys) >= 2){
              $returnArray[$subKeys[0]][$subKeys[1]] = $v;
            }
        }

        return $returnArray;     
    }

    /**
     * Check for substatus
     * 
     * @param string $status
     * @return Varien Object
     */
    static public function getStatus($status){
        $subStatus  = explode(self::EXPLODE_CHARACTER, $status);
        $return     = new Varien_Object();
        
        if(count($subStatus) > 1){
            $return->setStatus((int)$subStatus[0]);
            $return->setSubstatus($status);
        }else{
            $return->setStatus((int)$status);
            $return->setSubstatus();
        }
        
        return $return;
    }
    
    /**
     * Get parent status from substatus
     * 
     * @param int | string $subStatus
     * @return boolean | Array
     */
    static public function getParentStatus($subStatus){
        $statusArray = explode(self::EXPLODE_CHARACTER, $subStatus);
        
        if(isset($statusArray[0]) && $statusArray[0] != null){
            return (int)$statusArray[0]; 
        }
        return false;
    }
    
    static public function getCurrentStatus($status, $substatus=null){
        $gridOptionArray = Mage::getModel('qquoteadv/status')->getGridOptionArray(true);
        if($substatus !=null && self::getParentStatus($substatus) == $status){
            return $gridOptionArray[$substatus];
        }
        return $gridOptionArray[$status];
    }

}
