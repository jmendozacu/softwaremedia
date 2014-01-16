<?php
$installer = $this;
$installer->startSetup();

// create block
$content1 = <<<EOD
<script type="text/javascript" language="javascript">// <![CDATA[
    jQuery(function() {
        jQuery('#hotDealsBlock').carouFredSel({
            items				: 1,
            auto: false,
		scroll : {
			items			: 1,
			pauseOnHover	: true,
                        duration        : 1000
		},
            pagination : '#hotDealsPager',
            prev: '#prevHotDeals',
            next: '#nextHotDeals',
            mousewheel: true,
					swipe: {
						onMouse: true,
						onTouch: true
					}
        });
    });
// ]]></script>
<div class="hot-deals-block">
<ul id="hotDealsBlock">
<li><a href="#"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 1" /></a></li>
<li><a href="#"><img src="{{media url="wysiwyg/slide2.png"}}" alt="Slide 2" /></a></li>
<li><a href="#"><img src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 3" /></a></li>
</ul>
<a id="prevHotDeals" class="prev-carousel">&lt;</a> <a id="nextHotDeals" class="next-carousel">&gt;</a>
<div id="hotDealsPager">&nbsp;</div>
</div>
EOD;

$staticBlock1 = array(
    'title' => 'Hot Deals',
    'identifier' => 'hot_deals',
    'content' => $content1,
    'is_active' => 1,
    'stores' => array(0)
);
$block1 = Mage::getModel('cms/block')->load('hot_deals');
if(!$block1->getId()){
    Mage::getModel('cms/block')->setData($staticBlock1)->save();
}else{
    $block1->setContent($content1)->save();
}


$content2 = <<<EOD
<script type="text/javascript" language="javascript">// <![CDATA[
    jQuery(function() {
        jQuery('#featuredVideoBlock').carouFredSel({
            items	   : 1,
            auto       : true,
            scroll     : {
		items			: 1,
		pauseOnHover	: true,
                duration                 : 1000
	        },
            pagination : {
		container		: "#pagerFeaturedVideo",
		anchorBuilder	: function(nr) {
			return "<a href='#' class='video-"+nr+"'><span class='play'>play</span>"+nr+"</a>";
		        }
	        },
            mousewheel : true,
	    swipe              :  {
		onMouse: true,
		onTouch: true
		}
        });
    });
// ]]></script>
<div class="feature-video-block">
<ul id="featuredVideoBlock">
<li><a href="#"><span class="play">play</span><img class="img-video-01" src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 1" /></a>
<p><label>Video title</label>video descriptions</p>
</li>
<li><a href="#"><span class="play">play</span><img class="img-video-02" src="{{media url="wysiwyg/slide2.png"}}" alt="Slide 2" /></a>
<p><label>Video title</label>video descriptions</p>
</li>
<li><a href="#"><span class="play">play</span><img class="img-video-03" src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 3" /></a>
<p><label>Video title</label>video descriptions</p>
</li>
</ul>
<div id="pagerFeaturedVideo">&nbsp;</div>
</div>
<script type="text/javascript" language="javascript">// <![CDATA[
    jQuery(document).ready(function() {
        jQuery('#pagerFeaturedVideo .video-1').append('<img src=" ' +jQuery(".img-video-01").attr('src')+ ' "/>');
        jQuery('#pagerFeaturedVideo .video-2').append('<img src=" ' +jQuery(".img-video-02").attr('src')+ ' "/>');
        jQuery('#pagerFeaturedVideo .video-3').append('<img src=" ' +jQuery(".img-video-03").attr('src')+ ' "/>');
    });
// ]]></script>
EOD;

$staticBlock2 = array(
    'title' => 'Featured Video',
    'identifier' => 'featured_video',
    'content' => $content2,
    'is_active' => 1,
    'stores' => array(0)
);
$block2 = Mage::getModel('cms/block')->load('featured_video');
if(!$block2->getId()){
    Mage::getModel('cms/block')->setData($staticBlock2)->save();
}else{
    $block2->setContent($content2)->save();
}


$content3 = <<<EOD
<p><a title="title" href="#"><img src="{{skin url="images/brand/top-brand-01.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-02.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-03.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-04.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-05.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-06.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-07.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-08.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-09.png"}}" alt="title" /></a> <a title="title" href="#"><img src="{{skin url="images/brand/top-brand-10.png"}}" alt="title" /></a></p>
EOD;

$staticBlock3 = array(
    'title' => 'Software Brands',
    'identifier' => 'software_brands',
    'content' => $content3,
    'is_active' => 1,
    'stores' => array(0)
);
$block3 = Mage::getModel('cms/block')->load('software_brands');
if(!$block3->getId()){
    Mage::getModel('cms/block')->setData($staticBlock3)->save();
}else{
    $block3->setContent($content3)->save();
}


$content4 = <<<EOD
<p><a href="#"><img src="{{skin url='images/credentials/credentials-01.png'}}" alt="microsoft-silver" /></a> <a href="#"><img src="{{skin url='images/credentials/credentials-02.png'}}" alt="AdobeGold" /></a> <a href="#"><img src="{{skin url='images/credentials/credentials-03.png'}}" alt="symantec-silver" /></a> <a href="#"><img src="{{skin url='images/credentials/credentials-04.png'}}" alt="vmware-professional" /></a> <a href="#"><img src="{{skin url='images/credentials/credentials-05.png'}}" alt="Microsoft Specialist" /></a> <a href="#"><img src="{{skin url='images/credentials/credentials-06.png'}}" alt="oracleGold" /></a></p>
<p class="a-right"><a class="link-blue" href="{{store direct_url="credentials"}}">See All ...</a></p>
EOD;

$staticBlock4 = array(
    'title' => 'Credentials',
    'identifier' => 'credentials',
    'content' => $content4,
    'is_active' => 1,
    'stores' => array(0)
);
$block4 = Mage::getModel('cms/block')->load('credentials');
if(!$block4->getId()){
    Mage::getModel('cms/block')->setData($staticBlock4)->save();
}else{
    $block4->setContent($content4)->save();
}


$content5 = <<<EOD
<div class="customer_testimonial">&ldquo; Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod<br /> tempor incididunt ut labore et dolore magna aliqua. &rdquo; <br /><span>- Satisfied Customer</span>
<p class="a-right"><a class="link-blue" href="{{store direct_url="testimonials"}}">Read More ...</a></p>
</div>
EOD;

$staticBlock5 = array(
    'title' => 'Customer Testimonials',
    'identifier' => 'customer_testimonials',
    'content' => $content5,
    'is_active' => 1,
    'stores' => array(0)
);
$block5 = Mage::getModel('cms/block')->load('customer_testimonials');
if(!$block5->getId()){
    Mage::getModel('cms/block')->setData($staticBlock5)->save();
}else{
    $block5->setContent($content5)->save();
}


$content6 = <<<EOD
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_floating_style addthis_counter_style" style="left: 50px; top: 50px;">&nbsp;</div>
<script type="text/javascript">// <![CDATA[
var addthis_config = {"data_track_addressbar":true};
// ]]></script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4ef3722431aa8e44"></script>
<!-- AddThis Button END -->
EOD;

$staticBlock6 = array(
    'title' => 'Find Us Online',
    'identifier' => 'find_us_online',
    'content' => $content6,
    'is_active' => 1,
    'stores' => array(0)
);
$block6 = Mage::getModel('cms/block')->load('find_us_online');
if(!$block6->getId()){
    Mage::getModel('cms/block')->setData($staticBlock6)->save();
}else{
    $block6->setContent($content6)->save();
}


$homeContent = <<<EOD
<div class="f-fix soft-gift-guide">Shopping for someone? Check out our <a href="#">Software Gift Guide!</a></div>
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
if($home->getId()){
    $home->setContent($homeContent)->save();
}

$installer->endSetup();