<?php
        // update Copyright

try {
    $installer = $this;
    $installer->startSetup();
	
    $content = 
    <<<EOD
   <p>
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/microsoft/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-01.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-01-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/adobe/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-02.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-02-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/symantec/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-03.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-03-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/vmware/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-04.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-04-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/mcafee/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-05.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-05-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/corel/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-06.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-06-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/autodesk/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-07.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-07-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/trend-micro/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-08.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-08-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/sap/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-09.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-09-aft.png"}}" alt="title" />
    </a> 
    <a class="a-hover" title="title" href="http://www.softwaremedia.com/quickbooks/">
        <img class="img-def" src="{{skin url="images/brand/top-brand-10.png"}}" alt="title" />
        <img class="img-aft" src="{{skin url="images/brand/top-brand-10-aft.png"}}" alt="title" />
    </a>
</p>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery(".img-aft").hide();
  jQuery(".a-hover").hover(function(){
  jQuery(this).children(".img-def").hide();
  jQuery(this).children(".img-aft").show();
  },function(){
  jQuery(this).children(".img-def").show();
  jQuery(this).children(".img-aft").hide();
}); 
});
</script>
EOD;
    $block = Mage::getModel('cms/block')->load('software_brands');
    if($block->getId()){
        $block->setContent($content)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


