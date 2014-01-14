<?php
    // Create Cms/page - 404 page
try {
    $installer = $this;
    $installer->startSetup();
    
    $content =
            <<<EOD
   <div class="page-404">
    <ul class="disc">
    <li>If you typed the URL directly, please make sure the spelling is correct.</li>
    <li>If you clicked on a link to get here, we must have moved the content.<br />Please try our store search box above to search for an item.</li>
    <li>If you are not sure how you got here, <a onclick="history.go(-1);" href="#">go back</a> to the previous page or return to our <a href="{{store url=""}}">store homepage</a>.</li>
    </ul>
    </div>
    <!-- <div class="page-head-alt"><h3>We're sorry, the page you’re looking for can not be found.</h3></div>
    <div  style="font-size:12px;">
    <ul class="disc"
    <li>If you typed the URL directly, please make sure the spelling is correct.</li>
    <li>If you clicked on a link to get here, we must have moved the content.<br/>Please try our store search box above to search for an item.</li>
    <li>If you are not sure how you got here, <a href="#" onclick="history.go(-1);">go back</a> to the previous page</a> or return to our <a href="{{store url=''}}">store homepage,</a></li>
    </ul>
    <br/>
    </div> -->
EOD;
    
    if(Mage::getModel('cms/page')->load('no-route')->getId()){
        Mage::getModel('cms/page')->load('no-route')->setContent($content)->save();
    }
    
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}	