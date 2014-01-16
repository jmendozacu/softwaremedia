<?php

try {
    $installer = $this;
    $installer->startSetup();



    $states = Mage::getModel('directory/country')->load('US')->getRegions();
    foreach($states->getData() as $state){
        if($state['name'] == 'Utah'){
            $region_id = $state['region_id'];
            break;
        }
    }
    $ratePost = array(
        "code"=>'US-UT-*-Rate 1',
        'tax_country_id'=>'US',
        "zip_is_range"=>'0',
        "tax_postcode"=>'*',
        "rate"=>'6.8500',
        "tax_region_id"=>$region_id
    );
    $rateModel = Mage::getModel('tax/calculation_rate')->setData($ratePost);
    $rateModel->save();
    //echo '<pre>';var_dump($rateModel->getId());echo '</pre>';die;
    $ruleData = array(
        "code"=>'only customers in the state of Utah',
        'tax_customer_class'=>array(3),
        "tax_product_class"=>array(2),
        "tax_rate"=>array($rateModel->getId()),
        'priority' => 1,
        'position' => 1
    );
    $ruleModel = Mage::getSingleton('tax/calculation_rule');
    $ruleModel->setData($ruleData);
    $ruleModel->save();
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}
