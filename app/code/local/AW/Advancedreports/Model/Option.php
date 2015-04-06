<?php

class AW_Advancedreports_Model_Option extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('advancedreports/option');
    }

    public function load3params($reportId, $adminId, $path)
    {
        $this->_getResource()->load3params($this, $reportId, $adminId, $path);
        return $this;
    }
}