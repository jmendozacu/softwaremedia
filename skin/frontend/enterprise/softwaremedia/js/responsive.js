
function fixheight() {
	var maxHeight = -1;
	jQuery('.products-grid li.item').css('height', '');
	var maxHeight = Math.max.apply(null, jQuery(".products-grid li.item").map(function() {
		return jQuery(this).height();
	}).get());
	jQuery('.products-grid li.item').css('height', maxHeight);
}

/***********************************************************
 * Custom JS for Mobile and Tablets
 ***********************************************************/

//jQuery(document).ready(

rInit = function() {

	var window_width = jQuery(window).width();


	if (window_width < 562) {

		//var imgheight = jQuery('.hot-deals-block li img').offsetHeight();
		//console.log(imgheight);
		jQuery('.hot-deals-block li').css('width', window_width);
		jQuery('.hot-deals-block').css('height', (window_width / 2.26) + 'px');
		jQuery('.hot-deals-block ul').css('height', (window_width / 2.26) + 'px');
		jQuery('.hot-deals-block .caroufredsel_wrapper').css('height', (window_width / 2.26) + 'px');

	} else {
		jQuery('.hot-deals-block li').css('width', '');
		jQuery('.hot-deals-block').css('height', '232px');
		jQuery('.hot-deals-block ul').css('height', '232px');
		jQuery('.hot-deals-block .caroufredsel_wrapper').css('height', '232px');
	}


	if (window_width < 601) {
		var ct = jQuery('.collateral-tabs .tab').length;
		var hieght = jQuery('.collateral-tabs .tab').outerHeight() + 10;
		jQuery('.collateral-tabs .tab-container').css('top', ct * hieght);
	} else {
		jQuery('.collateral-tabs .tab-container').css('top', '');
	}


	if (window_width < 601 && window_width > 200) {

		// Mobile Menu
		function mobileMenuM() {
			jQuery('.mobile-slideout').hide();
			jQuery('.menu-trigger').unbind('click');
			jQuery('.menu-trigger').click(function() {
				jQuery('.mobile-slideout').slideToggle();
			});
		}

		// Footer Accordian
		function footerAccordianM() {
			jQuery('.footer-content h2').unbind('click');
			jQuery('.footer-content h2').click(function() {
				var checkElement = jQuery(this).next('.mobile-accordian');
				// Slide Up
				if (checkElement.is(':visible')) {
					jQuery('.mobile-accordian').slideUp('normal');
					jQuery('.mobile-accordian').removeClass('open');
				}
				// Slide Down
				if (!checkElement.is(':visible')) {
					jQuery('.mobile-accordian').slideUp('normal');
					jQuery('.mobile-accordian').removeClass('open');
					checkElement.slideDown('normal');
					checkElement.addClass('open');
				}
			});
		}

		// Adjust Product Grid
		function productGridM() {
		}

		// Accordian Bottom Product Page
		function productAddMenuM() {
			jQuery('.right-infor h4').unbind('click');
			jQuery('.right-infor h4').click(function() {
				var checkElement = jQuery(this).next('ul');
				// Slide Up
				if (checkElement.is(':visible')) {
					jQuery('.right-infor ul').slideUp('normal');
					jQuery('.right-infor h4').removeClass('open');
				}
				// Slide Down
				if (!checkElement.is(':visible')) {
					jQuery('.right-infor h4').removeClass('open');
					jQuery('.right-infor ul').slideUp('normal');
					checkElement.slideDown('normal');
					jQuery(this).addClass('open');
				}
			});
		}

		function cartHelpM() {
			jQuery('.mobile-checkout-help .help-title').unbind('click');
			jQuery('.mobile-checkout-help .help-title').click(function() {
				//jQuery('.help-title').addClass('open');
				var checkElement = jQuery(this).next('.help-content');
				// Slide Up
				if (checkElement.is(':visible')) {
					jQuery('.help-content').slideUp('normal');
					jQuery('.help-title').removeClass('open');
				}
				// Slide Down
				if (!checkElement.is(':visible')) {
					jQuery('.right-infor h4').removeClass('open');
					jQuery('.help-content').slideUp('normal');
					checkElement.slideDown('normal');
					jQuery(this).addClass('open');
				}
			});
		}

		function accountNavM() {
			jQuery('.sidebar .block-account .block-title').unbind('click');
			jQuery('.sidebar .block-account .block-title').click(function() {
				//jQuery('.help-title').addClass('open');
				var checkElement = jQuery('.sidebar .block-account .block-content ul');
				// Slide Up
				if (checkElement.is(':visible')) {
					jQuery('.sidebar .block-account .block-content ul').slideUp('normal');
					jQuery(this).removeClass('open');
				}
				// Slide Down
				if (!checkElement.is(':visible')) {
					checkElement.show();
					jQuery('.sidebar .block-account .block-content ul').slideDown('normal');
					jQuery(this).addClass('open');
				}
			});
		}

		// MISC General HTML Adjstments
		function generalAdjustmentsM() {

			// Cart Page
			jQuery('#shopping-cart-table tbody tr').each(function() {
				// Copy and move cart table HTML to Mobile cart
				var cart_image = jQuery(this).find('td:eq(0)').html();
				var cart_name = jQuery(this).find('td:eq(1)').html();
				var cart_price = jQuery(this).find('td:eq(2)').html();
				var cart_qty = jQuery(this).find('td:eq(3)').html();
				var cart_sub = jQuery(this).find('td:eq(4)').html();
				var cart_remove = jQuery(this).find('td:eq(5)').html();
				var cart_html = '<div class="mobile-cart-row"><div class="mobile-row-col1"><div class="mobile-padding">' + cart_image + '</div></div><div class="mobile-row-col2"><div class="mobile-padding"><div class="mobile-name">' + cart_name + '</div><div class="mobile-price"><span>Unit Price:</span> ' + cart_price + '</div><div class="mobile-qty"><span>Qty:</span> ' + cart_qty + '</div><div class="mobile-sub-remove"><div class="mobile-sub"><span>Sub Total:</span> ' + cart_sub + '</div><div class="mobile-remove data-table">' + cart_remove + '</div></div></div></div></div>';
				jQuery('.mobile-cart-append .mobile-cart-rows').append(cart_html);
			});

			// Move Cart Footer HTML to Mobile Cart
			var cart_foot = jQuery('#shopping-cart-table tfoot td').html();
			//jQuery('.mobile-cart-subtotal').append(cart_foot+'<div class="clear"></div>');
//    		jQuery('#shopping-cart-table').remove();

			// Account Adjustments
			jQuery('.account-template .col2-left-layout .col-left').insertBefore('.account-template .col2-left-layout .col-main');

		}

		/**************************
		 * Onload Settings
		 ***************************/
		generalAdjustmentsM();
		mobileMenuM();
		productAddMenuM();
		footerAccordianM();
		productGridM();
		cartHelpM();
		accountNavM();

		/**************************
		 * If Rotated
		 ***************************/

	}

	fixheight();


}


jQuery(function() {

	// Category list
	// Clone sidebar to toobar
	if (navigator.userAgent.indexOf('MSIE') == -1 || parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf('MSIE') + 5)) > 8) {
		jQuery('.sidebar').clone().appendTo('.toolbar');
		jQuery('.toolbar .sidebar').removeClass('col-left');
	}

	if (typeof amshopby_start == 'function') {
		amshopby_start();
	}
	;
	jQuery('.toolbar #narrow-by-list dt').addClass('amshopby-collapsed');
	jQuery('.toolbar #narrow-by-list dd ol').hide();

	// product Page
	//jQuery('.product-shop').appendTo('.product-essential');
	//jQuery(".product-shop").detach().insertAfter(".product-img-and-share");
	//jQuery(".product-shop").remove();

	rInit();
	console.log('loaded');
	setTimeout(rInit(), 500);
	setTimeout(rInit(), 1500);
});

jQuery(window).resize(function() {
	//jQuery(".product-shop").detach().insertAfter(".product-img-and-share");
	rInit();
});
