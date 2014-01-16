<?php
        // update Copyright

try {
    $installer = $this;
    $installer->startSetup();
	
    $content = 
    <<<EOD
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
<li><a href="http://www.softwaremedia.com/microsoft/sql-server/"><img src="{{skin url="images/hotdeals_sql_promo.png"}}" alt="Hotdeals SQL promo" /></a></li>
<li><a href="http://www.softwaremedia.com/adobe/acrobat/"><img src="{{skin url="images/hotdeals_office_acrobat_promo.png"}}" alt="Hotdeals Office Acrobat Promo" /></a></li>
<li><a href="http://www.softwaremedia.com/microsoft/sql-server"><img src="{{skin url="images/hotdeals_sql_promo.png"}}" alt="Hotdeals SQL promo"/></a></li>
</ul>
<a id="prevHotDeals" class="prev-carousel">&lt;</a> <a id="nextHotDeals" class="next-carousel">&gt;</a>
<div id="hotDealsPager">&nbsp;</div>
</div>
EOD;
    $block = Mage::getModel('cms/block')->load('hot_deals');
    if($block->getId()){
        $block->setContent($content)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


