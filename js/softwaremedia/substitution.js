function addSub(url, invoiceId)
{		
	function subResponse(resp)
	{
		retjson = resp.responseText.evalJSON();
        window.location = retjson.resp;
	}
	var productId = $("sub_" + invoiceId).getValue();
	var options = { 
	    method:"get", 
	    parameters: {
	    	invoiceId: invoiceId,
	    	productId: productId
	    },
	    onSuccess:subResponse 
	}; 
	new Ajax.Request(url,options); 
}