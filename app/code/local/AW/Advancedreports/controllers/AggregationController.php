<?php

class AW_Advancedreports_AggregationController extends AW_Advancedreports_Controller_Action
{
    public function cleanAction()
    {
        $this->_helper()->getAggregator()->cleanCache();
    }
}