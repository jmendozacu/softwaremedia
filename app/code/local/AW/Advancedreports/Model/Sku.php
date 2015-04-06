<?php

class AW_Advancedreports_Model_Sku extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('advancedreports/sku');
    }

    public function incRelevance($sku = null)
    {
        if ($this->getId()) {
            $rel = $this->getRelevance();
            $this->setRelevance($rel + 1);
        } else {
            $this->setSku($sku)->setRelevance(1);
        }
        return $this;
    }
}