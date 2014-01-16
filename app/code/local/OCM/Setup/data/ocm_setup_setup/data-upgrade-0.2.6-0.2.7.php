<?php
try {
    $installer = $this;
    $installer->startSetup();
	$content=
	<<<EOD
<p><a id="bbb-privacy-button" title="Click to verify BBB accreditation and to see a BBB report." href="http://www.bbb.org/utah/business-reviews/computer-software-services/softwaremediacom-in-salt-lake-city-ut-13002481" target="_blank"> <img src="//sm-img.com/skin/default/badges/bbb.gif" alt="Click to verify BBB accreditation and to see a BBB report." align="absmiddle" border="0" /></a> 
	<a id="hackersafe-button" href="https://www.scanalert.com/RatingVerify?ref=www.softwaremedia.com" target="_blank"> <img src="//images.scanalert.com/meter/www.softwaremedia.com/13.gif" alt="McAfee Secure" align="absmiddle" border="0" /></a> 
	<a id="pricegrabber-button" title="Read reviews on PriceGrabber.com" href="http://www.pricegrabber.com/rating_getreview.php/retid=767" target="_blank"> <img src="//sm-img.com/skin/default/badges/pricegrabber.gif" alt="Read reviews on PriceGrabber.com" align="absmiddle" border="0" /></a>
	<img src="{{skin url='images/footer-card-04.png'}}" alt="" /></p>
EOD;
    $staticBlock = array(
        'title' => 'Footer Card',
        'identifier' => 'footer_card',
        'content' => $content,
        'is_active' => 1,
        'stores' => array(0)
    );
	$block = Mage::getModel('cms/block')->load('footer_card');
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