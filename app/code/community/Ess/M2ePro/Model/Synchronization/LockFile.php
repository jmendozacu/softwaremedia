<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
class Ess_M2ePro_Model_Synchronization_LockFile extends Ess_M2ePro_Model_LockFile
{
    public function __construct()
    {
        $this->id = 'synchronization';
        $this->modelName = 'M2ePro/Synchronization_LockFile';
    }

    public function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')
            ->getConfig()
                ->getGroupValue('/synchronization/lockFile/','mode');
    }
}