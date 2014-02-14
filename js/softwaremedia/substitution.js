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

//Override rowClick to check for SELECT inputs
serializerController.prototype['rowClick'] = function(grid, event) {
		//alert(Event.element(event).tagName);
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName == 'INPUT';
         var isEditable = Event.element(event).tagName == 'OPTION';
         var isSelect = Event.element(event).tagName == 'SELECT';        //=
          Event.element(event).tagName == 'SELECT';
        if(trElement){
            var checkbox = Element.select(trElement, 'input');
            if(checkbox[0] && !checkbox[0].disabled){
                var checked = (isInput || isSelect || isEditable) ? checkbox[0].checked : !checkbox[0].checked;
                this.grid.setCheckboxChecked(checkbox[0], checked);
            }
        }
        this.getOldCallback('row_click')(grid, event);
    };