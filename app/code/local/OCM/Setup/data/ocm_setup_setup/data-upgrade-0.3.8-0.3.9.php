<?php
        // update Copyright

try {
    $installer = $this;
    $installer->startSetup();
	
    $content = 
    <<<EOD
<p>
    <a href="http://www.softwaremedia.com/microsoft/">
        <img class="img-logo" src="{{skin url='images/credentials/credentials-01.png'}}" alt="microsoft-silver" />
    </a> 
    <a href="http://www.softwaremedia.com/adobe/">
        <img class="img-logo" src="{{skin url='images/credentials/credentials-02.png'}}" alt="AdobeGold" />
    </a> 
    <a href="http://www.softwaremedia.com/symantec/">
        <img class="img-logo" src="{{skin url='images/credentials/credentials-03.png'}}" alt="symantec-silver" />
    </a> 
    <a href="http://www.softwaremedia.com/vmware/">
        <img class="img-logo" src="{{skin url='images/credentials/credentials-04.png'}}" alt="vmware-professional" />
    </a> 
    <a href="http://www.softwaremedia.com/microsoft/">
        <img class="img-logo" src="{{skin url='images/credentials/credentials-05.png'}}" alt="Microsoft Specialist" />
    </a> 
    <a href="http://www.softwaremedia.com/licensing/oracle/">
        <img class="img-logo" src="{{skin url='images/credentials/credentials-06.png'}}" alt="oracleGold" />
    </a>
</p>
<p class="a-right"><a class="link-blue" href="{{store direct_url="credentials"}}">See All ...</a></p>
EOD;
    $block = Mage::getModel('cms/block')->load('credentials');
    if($block->getId()){
        $block->setContent($content)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


