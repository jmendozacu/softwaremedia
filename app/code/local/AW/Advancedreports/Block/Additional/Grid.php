<?php

class AW_Advancedreports_Block_Additional_Grid extends AW_Advancedreports_Block_Advanced_Grid
{
    /*
     * Returns report name from registry
     */
    protected function _getName()
    {
        return Mage::registry('aw_advancedreports_additional_name');
    }

    public function getChartParams()
    {
        return Mage::helper('advancedreports/additional_' . $this->_getName())->getChartParams($this->_routeOption);
    }

    public function hasRecords()
    {
        return (count($this->_customData) > 1)
        && Mage::helper('advancedreports/additional_' . $this->_getName())->getChartParams($this->_routeOption)
        && count(Mage::helper('advancedreports/additional_' . $this->_getName())->getChartParams($this->_routeOption));
    }

    public function getNeedReload()
    {
        return Mage::helper('advancedreports/additional_' . $this->_getName())->getNeedReload($this->_routeOption);
    }

    public function getNeedTotal()
    {
        return Mage::helper('advancedreports/additional_' . $this->_getName())->getNeedTotal($this->_routeOption);
    }
}