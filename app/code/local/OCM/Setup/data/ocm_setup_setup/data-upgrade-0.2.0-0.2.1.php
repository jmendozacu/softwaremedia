<?php

$installer = $this;
$installer->startSetup();

// create block - Gift Guide
$content = <<<EOD
Shopping for someone? Check out our <a href="#">Software Gift Guide!</a>
EOD;

$staticBlock = array(
    'title' => 'Gift Guide',
    'identifier' => 'gift_guide',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);
$block = Mage::getModel('cms/block')->load('gift_guide');
if (!$block->getId()) {
    Mage::getModel('cms/block')->setData($staticBlock)->save();
} else {
    $block->setContent($content)->save();
}

// Update hompage
$homeContent = <<<EOD
<div class="f-fix soft-gift-guide">{{block type="cms/block" block_id="gift_guide" template="cms/content.phtml"}}</div>
<script type="text/javascript" language="javascript">// <![CDATA[
    jQuery(document).ready(function() {
        jQuery('#homeBanner li').css("width",jQuery(window).width());
    });

    jQuery(function() {
        jQuery('#homeBanner').carouFredSel({
            items				: 1,
            auto: true,
            width: '100%',
		scroll : {
			items			: 1,
			pauseOnHover	: true,
                        duration        : 1000
		},
            height: 'auto',
            mousewheel: true,
					swipe: {
						onMouse: true,
						onTouch: true
					}
        });
    });
// ]]></script>
<div class="list_carousel homeBanner">
<ul id="homeBanner">
<li><a href="#"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 1" /></a></li>
<li><a href="#"><img src="{{media url="wysiwyg/slide2.png"}}" alt="Slide 2" /></a></li>
<li><a href="#"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 3" /></a></li>
</ul>
</div>
<div class="page-center">
<div class="f-fix bestsellers-block">
<div class="title-orange">
<h3>Software Bestsellers</h3>
</div>
{{block type="ocm_catalog/product_bestsellers" name="catalog.product.betseller" alias="product_bestseller" template="catalog/product/bestsellers.phtml" productsCount="15"}}</div>
<div class="f-fix new-product-block">
<div class="title-orange">
<h3>New software Product</h3>
</div>
{{block type="catalog/product_new" name="catalog.product.new" alias="product_new" template="catalog/product/new.phtml" productsCount="15"}}</div>
<div class="f-fix">
<div class="home-hot-deals">
<div class="title-orange">
<h3>hot deals</h3>
</div>
<div class="f-fix hot-deals-and-featured-video">{{block type="cms/block" block_id="hot_deals"}}</div>
</div>
<div class="home-feature-video">
<div class="title-orange">
<h3>featured video</h3>
</div>
<div class="f-fix hot-deals-and-featured-video">{{block type="cms/block" block_id="featured_video"}}</div>
</div>
</div>
<div class="f-fix software-brand-block">
<div class="title-orange">
<h3>Top Software Brands</h3>
</div>
<div class="f-fix line-20">
<div class="top_software_brand">{{block type="cms/block" block_id="software_brands" template="cms/content.phtml"}}</div>
</div>
</div>
<div class="f-fix">
<div class="f-fix block-left-590">
<div class="title-orange">
<h3>Customer Testimonials</h3>
</div>
<div class="f-fix">{{block type="cms/block" block_id="customer_testimonials" template="cms/content.phtml"}}</div>
</div>
<div class="f-fix block-right-590">
<div class="title-orange">
<h3>Find Us Online</h3>
</div>
<div class="f-fix">{{block type="cms/block" block_id="find_us_online" template="cms/content.phtml"}}</div>
</div>
</div>
<div class="f-fix credential--block">
<div class="title-orange">
<h3>Credentials</h3>
</div>
<div class="f-fix credentials a-center">{{block type="cms/block" block_id="credentials" template="cms/content.phtml"}}</div>
</div>
</div>
EOD;

$home = Mage::getModel('cms/page')->load('enterprise-home');
if ($home->getId()) {
    $home->setContent($homeContent)->save();
}

$installer->endSetup();