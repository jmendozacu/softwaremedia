<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
 $attribute  = array(
   'type'          => 'text',
   'backend_type'  => 'text',
   'frontend_input' => 'text',
   'is_user_defined' => true,
   'label'         => 'Purchase Order',
   'visible'       => true,
   'required'      => false,
   'user_defined'  => true,
   'searchable'    => true,
   'filterable'    => true,
   'comparable'    => true,
   'default'       => 0
);
$installer->addAttribute('order', 'purchase_order', $attribute);
  $installer->endSetup();
  
  ?>