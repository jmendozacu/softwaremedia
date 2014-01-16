<?php
        // update static block software_brands

try {
    $installer = $this;
    $installer->startSetup();
	$content=
	<<<EOD
   <p>
    <a title="title" href="http://www.softwaremedia.com/microsoft/"><img src="{{skin url="images/brand/top-brand-01.png"}}" alt="title" /></a>
    <a title="title" href="http://www.softwaremedia.com/adobe/"><img src="{{skin url="images/brand/top-brand-02.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/symantec/"><img src="{{skin url="images/brand/top-brand-03.png"}}" alt="title" /></a>
    <a title="title" href="http://www.softwaremedia.com/vmware/"><img src="{{skin url="images/brand/top-brand-04.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/mcafee/"><img src="{{skin url="images/brand/top-brand-05.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/corel/"><img src="{{skin url="images/brand/top-brand-06.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/autodesk/"><img src="{{skin url="images/brand/top-brand-07.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/trend-micro/"><img src="{{skin url="images/brand/top-brand-08.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/sap/"><img src="{{skin url="images/brand/top-brand-09.png"}}" alt="title" /></a> 
    <a title="title" href="http://www.softwaremedia.com/quickbooks/"><img src="{{skin url="images/brand/top-brand-10.png"}}" alt="title" /></a>
</p>
EOD;
	$block = Mage::getModel('cms/block')->load('software_brands');
    if ($block->getId()) {
        $block->setContent($content)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}
?>
