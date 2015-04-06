<?php

class AW_Advancedreports_Model_Mysql4_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('advancedreports/option');
    }

    public function addAdminIdFilter($adminId)
    {
        $this->getSelect()->where('main_table.admin_id = ?', $adminId);
        return $this;
    }

    public function addReportIdFilter($reportId)
    {
        $this->getSelect()->where('main_table.report_id = ?', $reportId);
        return $this;
    }
}