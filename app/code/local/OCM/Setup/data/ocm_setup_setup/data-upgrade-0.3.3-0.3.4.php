<?php
        // update static block 

try {
    $installer = $this;
    $installer->startSetup();
	$content=
	<<<EOD
<script type="text/javascript" language="javascript">// <![CDATA[
    jQuery(function() {
        jQuery('#featuredVideoBlock').carouFredSel({
            items	   : 1,
            auto       : false,
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
<li><a class="html5lightbox" href="http://www.youtube.com/watch?v=3iF5CQjbv0k&amp;feature=youtu.be"><span class="play">play</span><img class="img-video-01" src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 1" /></a>
<p><label>About SoftwareMedia.com</label>Meet some of the staff at SoftwareMedia and hear some reviews from a few of our local customers. Not only do we offer discount software, but we pride ourselves in our dedication and service to our customers.</p>
</li>
<li><a class="html5lightbox" href="http://www.youtube.com/watch?v=SurNY4twV7c&amp;feature=share&amp;list=SP9F4C45A6CEC6F4D5"><span class="play">play</span><img class="img-video-02" src="{{media url="wysiwyg/slide2.png"}}" alt="Slide 2" /></a>
<p><label>SQL Server 2012 Licensing Explained</label>With the release of SQL Server 2012, Microsoft has made considerable changes to the SQL Server licensing model, including the elimination of the per processor licensing, as well as the addition of what is called "Per Core" licensing. Learn more about these changes in this video.</p>
</li>
<li><a class="html5lightbox" href="http://www.youtube.com/watch?v=TQ3yaRSQfsM&amp;feature=share&amp;list=PL12BD49B686AD6045"><span class="play">play</span><img class="img-video-03" src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 3" /></a>
<p><label>Autodesk Sketchbook Pro 6 Review</label>Autodesk SketchBook Pro 6 paint and drawing software enables you to transform your desktop, laptop, or tablet PC into the ultimate sketchbook. With professional-quality sketching capabilities and an intuitive interface, even new users can be productive within minutes.</p>
</li>
<li><a class="html5lightbox" href="http://www.youtube.com/watch?v=nVR8S1-a6Ak&amp;feature=share&amp;list=SP58D6AD54C3BB47D8"><span class="play">play</span><img class="img-video-04" src="{{media url="wysiwyg/banner.jpg"}}" alt="Slide 4" /></a>
<p><label>Getting Started in Premiere Pro in 10 Minutes</label>This beginner Adobe Premiere Pro CS6 tutorial will teach you the basics, and help to get you started by teaching you various tasks including: creating a new sequence, introduction to Premiere Pro workspace, how to import footage into Premiere Pro, and more.</p>
</li>
</ul>
<div id="pagerFeaturedVideo"></div>
</div>
<script type="text/javascript" language="javascript">// <![CDATA[
    jQuery(document).ready(function() {
        jQuery('#pagerFeaturedVideo .video-1').append('<img src=" ' +jQuery(".img-video-02").attr('src')+ ' "/>');
        jQuery('#pagerFeaturedVideo .video-2').append('<img src=" ' +jQuery(".img-video-03").attr('src')+ ' "/>');
        jQuery('#pagerFeaturedVideo .video-3').append('<img src=" ' +jQuery(".img-video-04").attr('src')+ ' "/>');
   
var tag_a = document.getElementById('pagerFeaturedVideo').getElementsByTagName('a')[0];
	tag_a.setAttribute("style","display:none");
document.getElementById('pagerFeaturedVideo').onclick = function(event) {
e = event||window.event
target = e.target || e.srcElement
  if(target.nodeName == 'IMG' || target.nodeName == 'SPAN'){
	  var tag_click = target.parentNode;
	  var elem = document.getElementById('pagerFeaturedVideo').getElementsByTagName('a');
	  for(var i = 0; i < elem.length; i++){
		  if(elem[i].getAttribute('style') == 'display:none'){
			elem[i].setAttribute("style","display:block");
		  }
	  }
	  tag_click.setAttribute("style","display:none");
  }
  return false
}
 });
// ]]></script>
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


