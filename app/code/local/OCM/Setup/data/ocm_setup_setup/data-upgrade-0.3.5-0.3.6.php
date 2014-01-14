<?php
        // update static block 

try {
    $installer = $this;
    $installer->startSetup();
	$content=
	<<<EOD
<script type="text/javascript" language="javascript">// <![CDATA[
jQuery(function() {
    jQuery('#thumbsFeatureVideo').carouFredSel({
        synchronise: ['#featuredVideoBlock', false, true],
        auto: false,
        direction:"up",
        width: 130,
        height:238,
        items: {
            visible: 3,
            start: 0
        },
       scroll: {
            onBefore: function( data ) {
                data.items.old.eq(0).removeClass('selected');
                data.items.visible.eq(0).addClass('selected');
            }
        },
        prev: '#prevFeatureVideo',
        next: '#nextFeatureVideo'
    });
    jQuery('#featuredVideoBlock').carouFredSel({
        auto: false,
        items: 1,
        scroll: {
            fx: 'fade'
        }
    });
    jQuery('#thumbsFeatureVideo a').click(function() {
        jQuery('#thumbsFeatureVideo').trigger( 'slideTo', [this, 0] );
    });
    jQuery('#thumbsFeatureVideo a:eq(0)').addClass('selected');
});
// ]]></script>
<div class="feature-video-block">
<ul id="featuredVideoBlock">
<li><a class="html5lightbox" title="About SoftwareMedia.com" href="http://www.youtube.com/watch?v=3iF5CQjbv0k&amp;feature=youtu.be"><span class="play">play</span><img src="http://i.ytimg.com/vi/3iF5CQjbv0k/0.jpg" alt="Slide 1" /></a>
<p><label>About SoftwareMedia.com</label>Meet some of the staff at SoftwareMedia and hear some reviews from a few of our local customers. Not only do we offer discount software, but we pride ourselves in our dedication and service to our customers.</p>
</li>
<li><a class="html5lightbox" title="SQL Server 2012 Licensing Explained" href="http://www.youtube.com/watch?v=SurNY4twV7c&amp;feature=share&amp;list=SP9F4C45A6CEC6F4D5"><span class="play">play</span><img src="http://i.ytimg.com/vi/SurNY4twV7c/0.jpg" alt="SQL Server 2012 Licensing Explained" /></a>
<p><label>SQL Server 2012 Licensing Explained</label>With the release of SQL Server 2012, Microsoft has made considerable changes to the SQL Server licensing model, including the elimination of the per processor licensing, as well as the addition of what is called "Per Core" licensing. Learn more about these changes in this video.</p>
</li>
<li><a class="html5lightbox" title="Autodesk Sketchbook Pro 6 Review" href="http://www.youtube.com/watch?v=TQ3yaRSQfsM&amp;feature=share&amp;list=PL12BD49B686AD6045"><span class="play">play</span><img src="http://i.ytimg.com/vi/TQ3yaRSQfsM/0.jpg" alt="Slide 3" /></a>
<p><label>Autodesk Sketchbook Pro 6 Review</label>Autodesk SketchBook Pro 6 paint and drawing software enables you to transform your desktop, laptop, or tablet PC into the ultimate sketchbook. With professional-quality sketching capabilities and an intuitive interface, even new users can be productive within minutes.</p>
</li>
<li><a class="html5lightbox" title="Getting Started in Premiere Pro in 10 Minutes" href="http://www.youtube.com/watch?v=nVR8S1-a6Ak&amp;feature=share&amp;list=SP58D6AD54C3BB47D8"><span class="play">play</span><img src="http://i.ytimg.com/vi/nVR8S1-a6Ak/0.jpg" alt="Slide 4" /></a>
<p><label>Getting Started in Premiere Pro in 10 Minutes</label>This beginner Adobe Premiere Pro CS6 tutorial will teach you the basics, and help to get you started by teaching you various tasks including: creating a new sequence, introduction to Premiere Pro workspace, how to import footage into Premiere Pro, and more.</p>
</li>
</ul>
<div id="pagerFeaturedVideo">
<div id="thumbsFeatureVideo"><a title="About SoftwareMedia.com"><span class="play">play</span><img src="http://i.ytimg.com/vi/3iF5CQjbv0k/0.jpg" alt="About SoftwareMedia.com" /></a> <a title="SQL Server 2012 Licensing Explained"><span class="play">play</span><img src="http://i.ytimg.com/vi/SurNY4twV7c/0.jpg" alt="SQL Server 2012 Licensing Explained" /></a> <a title="Autodesk Sketchbook Pro 6 Review"><span class="play">play</span><img src="http://i.ytimg.com/vi/TQ3yaRSQfsM/0.jpg" alt="Autodesk Sketchbook Pro 6 Review" /></a> <a title="Getting Started in Premiere Pro in 10 Minutes"><span class="play">play</span><img src="http://i.ytimg.com/vi/nVR8S1-a6Ak/0.jpg" alt="Getting Started in Premiere Pro in 10 Minutes" /></a></div>
</div>
</div>
EOD;
    $staticBlock = array(
        'title' => 'Featured Video',
        'identifier' => 'featured_video',
        'content' => $content,
        'is_active' => 1,
        'stores' => array(0)
    );
    $block = Mage::getModel('cms/block')->load('featured_video');
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


