init = function() {
    
/***********************************************************
 * Image Sliders Config
***********************************************************/

var window_width = jQuery(window).width();
	
	var item_number_products;
	var item_number_reviews;
	
	/* Desktop Setting ******** */
	 
	 if ( window_width > 1201) { 
	 	var item_number_products = 5;
	 	var item_number_upsell   = 5;
	 	var item_number_reviews  = 3;
	 }
	
	/* Tablet Setting ******** */
	
	if (window_width < 1200 && window_width > 601) { 
		var item_number_products = 3;
		var item_number_upsell   = 3;
		var item_number_reviews  = 1;
	}
	
	/* Mobile Setting ******** */
	 
	if ( window_width < 601 && window_width > 200) { 
	 	var item_number_products  = 1;
	 	var item_number_upsell    = 1;
	 	var item_number_reviews   = 1;
	}
	 
	/***********************************************
	 * Bestsellser 
	 ***********************************************/
	
	jQuery('#bestseller').carouFredSel({
	    auto: false,
	    scroll: 1,
	    prev: '#prevBestseller',
	    next: '#nextBestseller',
	    // Added settings for responsive
	    responsive: true,
	    width: '100%',
	    height: 'variable',
	    items: {
	        height: 'variable',
	        visible : item_number_products
	    }
	    
	});
    
    
    /***********************************************
	 * Software New 
	 ***********************************************/
    
	jQuery('#product_new').carouFredSel({
	    auto: false,
	    scroll: 1,
	    prev: '#prevProductNew',
	    next: '#nextProductNew',
	    // Added settings for responsive
	    responsive: true,
	    width: '100%',
	    height: 'variable',
	    items: {
	    	width: 220,
	        height: 'variable',
	        visible : item_number_products
	    }
	});
    
	/***********************************************
	 * Product Review 
	 ***********************************************/
	
	jQuery('#product-reviews-list').carouFredSel({
		auto: false,
		scroll: 1,
		//width: "100%",
		height: "auto",
		//items: item_number_reviews,
		prev: '#review_prev',
		next: '#review_next',
		//align: 'center'
		// Added settings for responsive
		responsive: true,
		width: '100%',
		height: 'variable',
		items:  item_number_reviews
	});
	
	/***********************************************
	 * Product Upsell (You may also like) 
	 ***********************************************/

	jQuery('#slide_upsell').carouFredSel({
	    auto: false,
	    scroll: 1,
	    height: "auto",
	    prev: '#upsell_prev',
	    next: '#upsell_next',
	    // Added settings for responsive
	    responsive: true,
	    width: '100%',
	    height: 'variable',
	    items: {
	    	width: 220,
	        height: 'variable',
	        visible : item_number_upsell
	    }
	});

	/***********************************************
	 * Crossells (Cart) 
	 ***********************************************/

	jQuery('.slide_crosssell').carouFredSel({
	    auto: false,
	    scroll: 1,
	    prev: '#crosssell_prev',
	    next: '#crosssell_next',
	    // Added settings for responsive
	    responsive: true,
	    width: '100%',
	    height: 'variable',
	    items: {
	    	width: 220,
	        height: 'variable',
	        visible : item_number_upsell
	    }
	});
    
	
/***********************************************************
 * Existing JS
***********************************************************/

      
  var review = jQuery('#customer-review-wrap');
  jQuery(".rating-links a").click(function() { 
    jQuery(review).addClass("active");  
  });


 
    var div = jQuery('#header-fix');
    var start = jQuery(div).offset().top;
    jQuery('.back-to-top').hide();
    jQuery.event.add(window, "scroll", function() {
        var p = jQuery(window).scrollTop();
        jQuery(div).css('position',((p)>start) ? 'fixed' : 'static');
        jQuery(div).css('top',((p)>start) ? '0px' : '');
        jQuery('#header-fix').css({'background-color':'#fff','z-index':'999999999'});
        if((p) > start){
            jQuery('.back-to-top').show();
        }else{
            jQuery('.back-to-top').hide();
        }
    });
 



/*All Brands*/
 
    var $alphabets = jQuery('.alphabet > a');
    var $contentRows = jQuery('#brands-table ul li');

    $alphabets.click(function () {      
        var $letter = jQuery(this), $text = jQuery(this).text(), $count = 0;

        $alphabets.removeClass("active");
        $letter.addClass("active");

        $contentRows.hide();
        $contentRows.each(function (i) {
            var $cellText = jQuery(this).children('li a').eq(0).text();
            if (RegExp('^' + $text).test($cellText)) {
                $count += 1;
                jQuery(this).fadeIn(400);
            }
        });                   
    });




/*
 *FAQ CMS Page
 */

  jQuery(".cms-faqs .content").hide();
  jQuery(".cms-faqs .heading").click(function()
  {
	jQuery(this).next(".cms-faqs .content").slideToggle(500);
  });


        jQuery('#testimonial_list').carouFredSel({
            auto: false,
            scroll: 10,
            item: 10,
            prev: '#prevTest',
            next: '#nextTest'
        });
    
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
					onMouse: true
				}
    });


	
    jQuery('.widget-banner ul').carouFredSel({

    	responsive	: true,
		scroll : {
        	fx          : "crossfade",
			items			: 1,
			pauseOnHover	: true,
            duration        : 1000
        },
		items		: {
			visible		: 1,
			align		: "center",
			width		: null,
			height		: null
		}
		
    });


  jQuery(".img-aft").hide();
  jQuery(".a-hover").hover(function(){
  jQuery(this).children(".img-def").hide();
  jQuery(this).children(".img-aft").show();
  },function(){
  jQuery(this).children(".img-def").show();
  jQuery(this).children(".img-aft").hide();
}); 

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

//});

}

jQuery(function() {
	init();
	if (getQueryVariable('ovchn')) {
		document.cookie="softwaremedia_ovchn=" + getQueryVariable('ovchn');
	}
});

jQuery(window).resize(function(){
	init();

});

function showPolicyDetails(header_element_id) {
var header_div = document.getElementById(header_element_id);
var details_div_id = header_div.id + "_details";
var details_div = document.getElementById(details_div_id);
if (details_div.style.display == "block") {
details_div.style.display = "none";
header_div.setAttribute("class", "policies_link");
} else {
details_div.style.display = "block";
header_div.setAttribute("class", "policies_link2");
}
}

function getQueryVariable(variable)
{
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == variable){return pair[1];}
       }
       return(false);
}


