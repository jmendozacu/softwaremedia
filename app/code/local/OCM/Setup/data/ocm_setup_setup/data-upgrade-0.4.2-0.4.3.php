<?php
        // update Copyright

try {
    $installer = $this;
    $installer->startSetup();
	
    $content = 
    <<<EOD
<p>SoftwareMedia.com is top rated throughout third party rating sites on the Web.</p>
<div class="ratings_image_container">
    <a style="position: relative; top: -2px;" href="http://reviews.pricegrabber.com/softwaremediacom/r/767/" target="_blank"> <img title="Rate Us At PriceGrabber.com" src="http://ah.pricegrabber.com/merchant_rating_image.php?retid=767" alt="Rate Us At PriceGrabber.com" /> </a>
    <a style="position: relative; top: -4px;" title="Click to verify BBB accreditation and to see a BBB report." href="http://www.bbb.org/utah/business-reviews/computer-software-services/softwaremediacom-in-salt-lake-city-ut-13002481" target="_blank"> <img title="Rate Us At PriceGrabber.com" src="http://sm-img.com/skin/default/badges/bbb.gif" alt="Click to verify BBB accreditation and to see a BBB report." /> </a>
    <a href="http://www.shopping.com/xMR-Software Media.com~MRD-301567~S-1" target="_blank"> <img title="Read Your Store Reviews" src="http://img.shoppingshadow.com/cctool/files/sdc_rating_trusted_45.gif" alt="Read Your Store Reviews" /> </a>
    <a style="position: relative; top: -18px;" href="https://www.scanalert.com/RatingVerify?ref=www.softwaremedia.com" target="_blank"> <img src="http://images.scanalert.com/meter/www.softwaremedia.com/13.gif" alt="McAfee Secure" /> </a> 
    <a style="position: relative; top: -18px;" title="NexTag Trusted Seller" href="http://www.nextag.com/SoftwareMedia~159869zzzreviewsz1zzzzmainz17-htm" target="_blank"> <img src="http://www.nextag.com/seller/NextagLogo.jsp?seller=159869" alt="Nextag Seller" /> </a> 
    <a href="http://www.stellaservice.com/profile/1126" target="_blank"> <img title="SoftwareMedia.com is top rated for customer service" src="http://seal.stellaservice.com/seals/stellaservice_excellent.png?c=1126" alt="" width="115" height="73" border="0" /> </a>
</div>
<div class="ratings_orange_header">Here's what some of our most valued customers have to say:</div>
<p>{{block type="ocm_testimonial/testimonial" template="testimonial/ratings.phtml"}} 
<a id="inline" style="text-align: center; cursor: pointer;" href="#data"><img title="Submit Your Testimonial" src="http://sm-img.com/skin/default/ratingsandcreds/submit_testimonial_btn.jpg" alt="Submit Your Testimonial" /></a> 
{{block type="core/template" template="testimonial/testimonial_form.phtml"}}</p>
<script type="text/javascript">// <![CDATA[
jQuery(document).ready(function(){
	jQuery("a#inline").fancybox();
	var myForm= new VarienForm('testimonial_form', true);
});
// ]]></script>
EOD;
    $_cmsPage = array(
        'title' => 'Customer Testimonials and Ratings',
        'identifier' => 'ratings.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );

    $cmsPage = Mage::getModel('cms/page')->load('ratings.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('SoftwareMedia.com Customer Reviews &amp; Ratings')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


