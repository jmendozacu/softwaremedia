<?php
        // update static block 

try {
    $installer = $this;
    $installer->startSetup();
	$content=
	<<<EOD
<div class="customer_testimonial">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod<br /> tempor incididunt ut labore et dolore magna aliqua. <br /><span>- Satisfied Customer</span>
<p class="a-right"><a class="link-blue" href="{{store direct_url="ratings.html"}}">Read More ...</a></p>
</div>
EOD;
    $staticBlock = array(
        'title' => 'Customer Testimonials',
        'identifier' => 'customer_testimonials',
        'content' => $content,
        'is_active' => 1,
        'stores' => array(0)
    );
    $block = Mage::getModel('cms/block')->load('customer_testimonials');
    if ($block->getId()) {
        $block->setContent($content)->save();
    }else{
        $block->setData($staticBlock)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


