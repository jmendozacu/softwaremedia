<?php 



class OCM_Etilize_IndexController extends Mage_Core_Controller_Front_Action {    
	
   
	public function updateSpexAction() {
	try 
	{
		$ocm = new OCM_Etilize_Model_Etilize();
		$ocm->updateSpex();
    }
    catch (Exception $e)
    {
        echo $e->getTraceAsString();
    }		
	}
}
