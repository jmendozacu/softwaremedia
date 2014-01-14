<?php

try {


    $installer = $this;
    $installer->startSetup();
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
<li><a href="{{store direct_url="customer/account/login"}}">Login</a>/<a href="{{store direct_url="customer/account/create/"}}">Register</a></li>
<li><a href="{{store direct_url="sales/order/history"}}">Order Status</a></li>
<li><a href="{{store direct_url="sales/order/history"}}">Order History</a></li>
<li><a href="{{store direct_url="rma/return/history"}}">Returns</a></li>
</ul>
</div>
<div class="bx-links">
<h2>Company Info</h2>
<ul>
<li><a href="{{store direct_url="about"}}">About Us</a></li>
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
    
    // FAQs
    $content = "FAQs content";
    $cmsPage = array(
        'title' => 'FAQs',
        'identifier' => 'faqs',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(0),
        'root_template' => 'one_column'
    );
    Mage::getModel('cms/page')->setData($cmsPage)->save();
    
    // Policies
    $content = "Policies content";
    $cmsPage = array(
        'title' => 'Policies',
        'identifier' => 'policies',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(0),
        'root_template' => 'one_column'
    );
    Mage::getModel('cms/page')->setData($cmsPage)->save();
    
    // Testimonials
    $content = "Testimonials content";
    $cmsPage = array(
        'title' => 'Testimonials',
        'identifier' => 'testimonials',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(0),
        'root_template' => 'one_column'
    );
    Mage::getModel('cms/page')->setData($cmsPage)->save();  
    
    // Credentials
    $content = "Credentials content";
    $cmsPage = array(
        'title' => 'Credentials',
        'identifier' => 'credentials',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(0),
        'root_template' => 'one_column'
    );
    Mage::getModel('cms/page')->setData($cmsPage)->save(); 

    // About
    $content = "About content";
    $cmsPage = array(
        'title' => 'About',
        'identifier' => 'about',
        'content' => $content,
        'is_active' => 1,
        'sort_order' => 0,
        'stores' => array(0),
        'root_template' => 'one_column'
    );
    Mage::getModel('cms/page')->setData($cmsPage)->save(); 

    $installer->endSetup();
} catch (Excpetion $e) {
    Mage::logException($e);
    Mage::log("ERROR IN SETUP " . $e->getMessage());
}	