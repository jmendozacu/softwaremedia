<?php

try {


    $installer = $this;
    $installer->startSetup();
    // footer
    $homecontent =
        <<<EOD
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
</div>
{{block type="catalog/product_new" name="catalog.product.new" alias="product_new" template="catalog/product/new.phtml" productsCount="15"}}</div>
EOD;

    $homepage = Mage::getModel('cms/page')->load('home');
    if($homepage->getId()){
        $homepage->setContent($homecontent)->save();
    }



    // footer-card
    $footercontent =
    <<<EOD
<p><img src="{{skin url=images/footer-card-01.png}}" alt="" /><img src="{{skin url=images/footer-card-02.png}}" alt="" /><img src="{{skin url=images/footer-card-03.png}}" alt="" /><img src="{{skin url=images/footer-card-04.png}}" alt="" /></p>
EOD;
    $footer = Mage::getModel('cms/block')->load('footer_card');
    if ($footer->getId()){
        $footer->setContent($footercontent)->save();
    }



    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}