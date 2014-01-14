<?php

try {


    $installer = $this;
    $installer->startSetup();

    //Update static block  shop_with_confidence

    $content =
<<<EOD
<div class="box-area">
<div class="title-orange">
<h3>Shop With Confidence</h3>
</div>
<div class="box-content">
<div class="f-fix box-confidence">
<div class="f-fix privacy">
<div class="item-body">
<h3 class="item-title">Privacy | We value your privacy and will never disclose your information</h3>
<p class="item-content">Your privacy is important to us. We use this information to process, ship, track and complete your order as well as communicate with you throughout the order process in case any issues arrise. We do not share this information with outside parties except to the extent necessary to complete that order. We will never use your personal information in ways unrelated to the ones described above without providing you with an opportunity to opt-out or otherwise prohibit such unrelated uses.</p>
</div>
</div>
</div>
<div class="f-fix box-confidence">
<div class="f-fix secure">
<div class="item-body">
<h3 class="item-title">Security | We are McAfee Secure certified and all transactions are encrypted</h3>
<p class="item-content">To prevent unauthorized access, maintain data accuracy, and ensure the correct use of information, we have put in place the appropriate procedures to safeguard and secure the information we collect online. We meet all industry guidelines for remote web server vulnerability testing to help protect your personal information from hackers. SoftwareMedia.com is tested and certified daily by the McAfee Secure Security Scan, and the logo will only appear on the footer of our site if we have passed their standards.</p>
</div>
</div>
</div>
<div class="f-fix box-confidence">
<div class="f-fix guaranteed">
<div class="item-body">
<h3 class="item-title">Satisfaction | We have an easy "No questions asked" return policy</h3>
<div class="item-content">Your satisfaction is our goal. We offer free shipping and live customer support to ensure that your shopping experience is the best it can be. And with our No Hassle Returns policy, your satifsaction is guaranteed for 60 days after you receive your shipment. This "return by" date is printed on your invoice. We'll pay for your return shipping via pre-paid airbill. Just follow the instructions located on the bottom of your invoice. Please note that registered/activated software is not eligible for returns.</div>
</div>
</div>
</div>
</div>
</div>
EOD;
    $block = Mage::getModel('cms/block')->load('shop_with_confidence');
    if ($block->getId()) {
        $block->setContent($content)->save();
    }



    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}