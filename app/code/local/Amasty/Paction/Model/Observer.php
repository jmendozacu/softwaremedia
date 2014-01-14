<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Observer
{
    public function handleAmProductGridMassaction($observer) 
    {
        $grid = $observer->getGrid();

        $types = Mage::getStoreConfig('ampaction/general/commands');
        if (!$types)
            return $this;
        
        $types = explode(',', $types);
        foreach ($types as $i => $type) {
            if (strlen($type) > 2) {
                $command = Amasty_Paction_Model_Command_Abstract::factory($type);
                $command->addAction($grid);
            } else { // separator
                $grid->getMassactionBlock()->addItem('ampaction_separator' . $i, array(
                    'label'=> '---------------------',
                    'url'  => '' 
                ));                
            }
        }
        
        return $this;
    }
}