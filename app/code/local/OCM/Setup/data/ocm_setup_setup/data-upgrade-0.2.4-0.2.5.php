<?php
try {
    $installer = $this;
    $installer->startSetup();
    $content =
        <<<EOD
		<div class="bx-links">
<h2>Questions?<span>800.474.1045</span></h2>
<ul>
<li><a href="{{store direct_url="contacts"}}">Contact Us</a></li>
<li>
<div id="lpButDivID-1287434146578"></div>
</li>
<li><a href="{{store direct_url="faqs"}}">FAQs</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Customer Info</h2>
<ul>
<li><a href="{{store direct_url="customer/account"}}">My Account</a></li>
<li><a href="{{store direct_url="customer/account/login"}}">Login</a>/<a href="{{store direct_url="customer/account/create/"}}">Register</a></li>
<li><a href="{{store direct_url="sales/order/history"}}">Order Status</a></li>
<li><a href="{{store direct_url="sales/order/history"}}">Order History</a></li>
<li><a href="{{store direct_url="rma/return/history"}}">Returns</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Company Info</h2>
<ul>
<li><a href="{{store direct_url="about"}}">About Us</a></li>
<li><a href="{{store direct_url="policies"}}">Policies</a></li>
<li><a href="{{store direct_url="testimonials"}}">Testimonials</a></li>
<li><a href="{{store direct_url="credentials"}}">Credentials</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Why Buy From Us?</h2>
<p>SoftwareMedia provides discount software to businesses &amp; consumers and carries over 80 top software brands.</p>
<p>Free shipping is offered on all orders and we offer a 100% satisfaction guarantee. If you have questions our knowledgeable staff can help find the best software solution for you.</p>
</div>
<script type="text/javascript" src="<?php echo $this->getSkinUrl('js/livechat.js')?>"></script>
EOD;
    $block = Mage::getModel('cms/block')->load('footer_blocks');
    if ($block->getId()) {
        $block->setContent($content)->save();
    }
	$content=
	<<<EOD
<p><a id="bbb-privacy-button" title="Click to verify BBB accreditation and to see a BBB report." href="http://www.bbb.org/utah/business-reviews/computer-software-services/softwaremediacom-in-salt-lake-city-ut-13002481" target="_blank"> <img src="//sm-img.com/skin/default/badges/bbb.gif" alt="Click to verify BBB accreditation and to see a BBB report." align="absmiddle" border="0" /></a> 
	<a id="hackersafe-button" href="https://www.scanalert.com/RatingVerify?ref=www.softwaremedia.com" target="_blank"> <img src="//images.scanalert.com/meter/www.softwaremedia.com/13.gif" alt="McAfee Secure" align="absmiddle" border="0" /></a> 
	<a id="pricegrabber-button" title="Read reviews on PriceGrabber.com" href="http://www.pricegrabber.com/rating_getreview.php/retid=767" target="_blank"> <img src="//sm-img.com/skin/default/badges/pricegrabber.gif" alt="Read reviews on PriceGrabber.com" align="absmiddle" border="0" /></a>
	<img src="<?php echo $this->getSkinUrl('images/footer-card-04.png') ?>" alt="" /></p>
EOD;
	$block = Mage::getModel('cms/block')->load('footer_card');
    if ($block->getId()) {
        $block->setContent($content)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}