<?php

try {
    $installer = $this;
    $installer->startSetup();
    //Update static block  shop_with_confidence
    $content =
        <<<EOD
    <p><a class="facebook" href="http://www.facebook.com/SoftwareMedia" target="_blank">Facebook</a><a class="twitter" href="https://twitter.com/softwaremedia" target="_blank">Twitter</a><a class="googleplus" href="https://plus.google.com/103485723133992972837/posts" target="_blank">Google +</a><a class="tube" href="http://www.youtube.com/user/SoftwareMedia" target="_blank">Youtube</a></p>
EOD;
    $block = Mage::getModel('cms/block')->load('follow_us');
    if ($block->getId()) {
        $block->setContent($content)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}