<?php
class OCM_Quotedispatch_Block_View_List extends OCM_Quotedispatch_Block_Abstract
{

    public function getAllItems() {
        return $this->getQuote()->getAllItems();
    }

}