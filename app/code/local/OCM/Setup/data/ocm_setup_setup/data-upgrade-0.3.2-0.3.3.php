<?php
        // update static block credentials

try {
    $installer = $this;
    $installer->startSetup();
    //update About Us page
	$content=
	<<<EOD
	<p>About content</p>
EOD;
    $_cmsPage = array(
        'title' => 'About us',
        'identifier' => 'about.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );

    $cmsPage = Mage::getModel('cms/page')->load('about.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('About Us')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    //shipping page
    $content =
        <<<EOD
<div class="shipping_orange_header">Shipping Methods</div>
<div class="shipping_text">SoftwareMedia ships throughout the 48 contiguous United States and to Alaska, Hawaii, Puerto Rico and Canada.</div>
<table class="shipping_table">
<tbody>
<tr>
<td class="shipping_text_bold">Free Budget ($0)</td>
<td class="shipping_text3">5-9 business days within the contiguous U.S.</td>
</tr>
<tr>
<td class="shipping_text_bold">Express ($6)</td>
<td class="shipping_text3">3-5 business days within the contiguous U.S.</td>
</tr>
<tr>
<td class="shipping_text_bold">Expedited ($12)</td>
<td class="shipping_text3">2-3 business days within the contiguous U.S., depending on destination zip code.</td>
</tr>
<tr>
<td class="shipping_text_bold">Alaska/Hawaii ($20)</td>
<td class="shipping_text3">3-5 business days, depending on destination zip code.</td>
</tr>
<tr>
<td class="shipping_text_bold">Standard Overnight ($30)</td>
<td class="shipping_text3">Arrives next day by 3:00 pm to most addresses; 4:30 to rural areas.</td>
</tr>
<tr>
<td class="shipping_text_bold">Priority Overnight ($40)</td>
<td class="shipping_text3">Arrives next day by 10:30 am to most addresses; 4:30 pm to remote areas.</td>
</tr>
<tr>
<td class="shipping_text_bold">International (Canada)</td>
<td class="shipping_text3">Time-definite delivery to <em>Canada and Puerto Rico only</em> , typically in one, two or three business days. Price depends on destination.</td>
</tr>
<tr>
<td class="shipping_text_bold">Saturday Delivery</td>
<td class="shipping_text3">And you thought nobody worked on the weekends! FedEx does provide Saturday delivery for a small fee, but you must call first. To secure Saturday delivery, please call <span class="skype_pnh_container" dir="ltr" onmouseover="SkypeClick2Call.MenuInjectionHandler.showMenu(this, event)" onmouseout="SkypeClick2Call.MenuInjectionHandler.hideMenu(event)"> <span class="skype_pnh_highlighting_inactive_common" dir="ltr"> <span class="skype_pnh_textarea_span"> <img class="skype_pnh_logo_img" src="{{skin url='images/numbers_button_skype_logo.png'}}" alt="" /> <span class="skype_pnh_text_span">1-800-474-1045</span> </span> </span> </span> Ext:3 during regular business hours.</td>
</tr>
<tr>
<td class="shipping_text4" colspan="2"><strong>Note:</strong> Shipping times and prices are subject to product availability, and may change depending on warehouse location. Our FedEx carrier requires a street address, thus we do not ship to P.O. Boxes.</td>
</tr>
</tbody>
</table>
<div class="shipping_orange_header">Free Shipping</div>
<div class="shipping_text">Remember that amazing deal you got on the Clap-On Popcorn-Maker? Then you found out it was going to cost an arm and a leg to ship, right? Yeah, we know that feeling. That's why you get Free Shipping on <em>all orders</em> ... on top of great deals.</div>
<div class="shipping_text">Anyone within the 48 Continental United States can take advantage of this by selecting the <strong>"Free Ground"</strong> shipping method upon checkout. You'll be charged <em>nothing</em> and your package will arrive in 5-7 business days.</div>
<div class="shipping_orange_header">Order Cutoff time</div>
<div class="shipping_text">Want your order to ship the same day you place it? Easy! Just place your order before 6:00 p.m. EST (5:00 CST/ 4:00 MST/ 3:00 PST) and it'll ship out that day. Orders placed after the cutoff time will go out the following day. We're talking business days here, people... That means orders placed Saturday or Sunday will go out Monday.</div>
<div class="shipping_orange_header">International Shipping</div>
<div class="shipping_text">We accept orders from Puerto Rico. To order, enter your 6-digit alphanumeric postal code or your zip code in the ZIP code field and choose either FedEx International Economy or FedEx International Priority as your shipping method.</div>
<div class="shipping_orange_header">Order Tracking</div>
<div class="shipping_text">We make it easy to tell exactly where your products are at any given time. Once your order leaves our warehouse, you'll receive an email with your individual order's tracking information. You can view detailed tracking information from FedEx by clicking on the link in the email or by going to "Order Tracking" on our website. It's easier to track your order than your own kids!</div>
<div class="shipping_orange_header">Return Policy</div>
<div class="shipping_text">Need to cancel your order? No problem - give us a call or contact us via Live Chat. We also make returns easy - We'll even pay for the return shipping! Once you receive your software, you have 60 days to decide whether to keep it.** If you are unsatisfied, simply fill out a <a href="/account/Returns">product return form</a> and we'll send you a pre-paid FedEx air bill within 3 business days. Then just drop the package at any FedEx drop-off and we'll take care of the rest</div>
<div class="shipping_text">SoftwareMedia.com employs a strict quality control process for all returns. This includes verifying package contents and checking with the manufacturer to see if the product has been registered or activated. Products that have a broken seal, have been activated, registered, damaged, or are otherwise not in condition for resale are not eligible for return, will be shipped back to you and are subject to additional shipping charges and/or restocking fees. Once your return passes quality control your refund will be issued within 72 business hours. For information on returns after the 60 day window, visit our <a href="/policies.html">policies page.</a></div>
<div class="shipping_text">**Due to manufacturer restrictions, we are unable to provide a refund for Oracle, Blackberry, Embarcadero, Veeam, or Corel Transactional Licensing products. Returns of download items are at the discretion of Softwaremedia.com, and if approved may take up to 2-3 weeks.</div>
<div class="shipping_text">* Shipping times and prices are subject to product availability, and may change depending on warehouse location. Our FedEx carrier requires a street address, thus we do not ship to P.O. Boxes.</div>
EOD;
    $_cmsPage = array(
        'title' => 'Shipping and Delivery',
        'identifier' => 'shipping.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );
    $cmsPage = Mage::getModel('cms/page')->load('shipping.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('Shipping and Delivery')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    //FAQs
    $content=
        <<<EOD
    <p>FAQs content</p>
EOD;
    $_cmsPage = array(
        'title' => 'FAQs',
        'identifier' => 'faqs.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );

    $cmsPage = Mage::getModel('cms/page')->load('faqs.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('FAQs')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    //Policies page
    $content=
        <<<EOD
    <p>Policies content</p>
EOD;
    $_cmsPage = array(
        'title' => 'Policies',
        'identifier' => 'policies.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );

    $cmsPage = Mage::getModel('cms/page')->load('policies.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('Policies')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    //Testimonials Page
    $content=
        <<<EOD
<p>Testimonials content</p>
EOD;
    $_cmsPage = array(
        'title' => 'Testimonials',
        'identifier' => 'testimonials.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );

    $cmsPage = Mage::getModel('cms/page')->load('testimonials.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('Testimonials')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    //credentials page
    $content=
        <<<EOD
<p>Credentials content</p>
EOD;
    $_cmsPage = array(
        'title' => 'Credentials',
        'identifier' => 'credentials.html',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(1),
        'root_template' => 'one_column'
    );

    $cmsPage = Mage::getModel('cms/page')->load('credentials.html');
    if ($cmsPage->getId()) {
        $cmsPage->setContent($content)->setTitle('Credentials')->save();
    }else{
        $cmsPage->setData($_cmsPage)->save();
    }
    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}


