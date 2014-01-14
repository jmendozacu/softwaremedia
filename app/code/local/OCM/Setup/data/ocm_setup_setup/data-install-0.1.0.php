<?php

try {
    $installer = $this;
    $installer->startSetup();
    // enable RMA
    $config = new Mage_Core_Model_Config();
    $config->saveConfig('sales/enterprise_rma/enabled', '1', 'default', 0);

    // homepage
    $content =
            <<<EOD
<div id="container">
<div id="example">
<div id="slides">
<div class="slides_container">
<div class="slide"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 1" width="570" height="270" /></div>
<div class="slide"><img src="{{media url="wysiwyg/slide2.png"}}" alt="Slide 2" width="570" height="270" /></div>
<div class="slide"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 1" width="570" height="270" /></div>
</div>
</div>
</div>
</div>
<div class="clear">
<h5 style="float: left; border: 1px solid #EFEFEF; padding: 4px 15px; margin: 13px 0 0 20px; border-bottom: 0;">Software Bestsellers</h5>
<div class="clear" style="border-top: 1px solid #EFEFEF; height: 13px;">&nbsp;</div>
{{block type="ocm_catalog/product_bestsellers" name="catalog.product.betseller" alias="product_bestseller" template="catalog/product/bestsellers.phtml" productsCount="15"}}</div>
<div class="clear">
<h5 style="float: left; border: 1px solid #EFEFEF; padding: 4px 15px; margin: 13px 0 0 20px; border-bottom: 0;">New software Product</h5>
<div class="clear" style="border-top: 1px solid #EFEFEF; height: 13px;">&nbsp;</div>
{{block type="catalog/product_new" name="catalog.product.new" alias="product_new" template="catalog/product/new.phtml" productsCount="15"}}</div>
<script type="text/javascript">// <![CDATA[
		jQuery(function(){
			jQuery('#slides').slides({
				preload: true,
				generateNextPrev: true
			});
		});
// ]]></script>   
EOD;
    $layout =
            <<<EOD
<reference name="head">
            <action method="addItem"><type>js</type><name>slideshow/slides.min.jquery.js</name></action>
            <action method="addItem"><type>js</type><name>slideshow/jquery.slider.js</name></action>
            <action method="addItem"><type>skin_css</type><name>css/global.css</name></action>
           <action method="addItem"><type>skin_css</type><name>css/slider.css</name></action>
</reference>    
EOD;
    $homePage = Mage::getModel('cms/page')->load('home');
    if ($homePage->getId())
        $homePage->setContent($content)->setLayoutUpdateXml($layout)->save();


    // footer
    $content =
            <<<EOD
<div class="bx-links">
<h2>Questions?<span>800.474.1045</span></h2>
<ul>
<li><a href="{{store direct_url="contacts"}}">Contact Us</a></li>
<li><a href="#">Live Chat</a></li>
<li><a href="{{store direct_url="faqs"}}">FAQs</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Customer Info</h2>
<ul>
<li><a href="{{store direct_url="customer/account"}}">My Account</a></li>
<li><a href="{{store direct_url="customer/account/login/"}}">Login</a>/<a href="#">Register</a></li>
<li><a href="{{store direct_url="sales/order/history/"}}">Order Status</a></li>
<li><a href="{{store direct_url="sales/order/history/"}}">Order History</a></li>
<li><a href="{{store direct_url="rma/return/history/"}}">Returns</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Company Info</h2>
<ul>
<li><a href="{{store direct_url="about/"}}">About Us</a></li>
<li><a href="{{store direct_url="policies"}}">Policies</a></li>
<li><a href="{{store direct_url="testimonials"}}">Testimonials</a></li>
<li><a href="{{store direct_url="credentials"}}">Credentials</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Why Buy From Us?</h2>
<p>SoftwareMedia provides discount software to businesses and consumers, carrying over 80 top software brands.</p>
<p>If you don&rsquo;t know what you&rsquo;re looking for, our knowledgeable staff can help find the best software solution for you.</p>
</div>
EOD;
    $footer = Mage::getModel('cms/block')->load('footer_blocks');
    if ($footer->getId())
        $footer->setContent($content)->save();

    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}
