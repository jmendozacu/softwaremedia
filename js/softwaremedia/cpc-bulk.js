document.observe("dom:loaded", function() {
	$('cpc_price-checkbox').disable();
	$$('label[for="cpc_price-checkbox"]').each(function(s){ 
		s.update('Please Use New CPC Price');
	});
});