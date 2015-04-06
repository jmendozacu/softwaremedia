<?php

class AW_Advancedreports_Helper_Additional extends AW_Advancedreports_Helper_Abstract
{
    const REGISTRY_PATH = 'aw_advancedreports_additional';

    /**
     * Returns reports factory class
     *
     * @return AW_Advancedreports_Model_Additional_Reports
     */
    public function getReports()
    {
        return Mage::getSingleton('advancedreports/additional_reports');
    }

    /**
     * Item name
     *
     * @param $name
     *
     * @return AW_Advancedreports_Model_Additional_Item
     */
    protected function _getItemByName($name)
    {
        $reports = $this->getReports()->getReports();
        if (is_array($reports) || $reports instanceof Traversable) {
            foreach ($this->getReports()->getReports() as $report) {
                if ($report->getName() == $name) {
                    return $report;
                }
            }
        }
        return new AW_Advancedreports_Model_Additional_Item();
    }

    public function getVersionCheck($item)
    {
        if (is_string($item)) {
            return version_compare(
                $this->_helper()->getVersion(), $this->_getItemByName($item)->getRequiredVersion(), '>='
            );
        } elseif ($item instanceof AW_Advancedreports_Model_Additional_Item) {
            return version_compare($this->_helper()->getVersion(), $item->getRequiredVersion(), '>=');
        }
        return null;
    }
}