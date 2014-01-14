ProductGridHandler = Class.create();
ProductGridHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(AddListingHandlerObj)
    {
        this.addListingHandlerObj = AddListingHandlerObj;
    },

    //----------------------------------

    save_click: function(url)
    {
        var selected = this.getSelectedProducts();
        if (selected) {
            var back = '';
            var isList = '';
            if (url.indexOf('/back/list/') + 1) {
                back = 'list';
            }

            this.addListingHandlerObj.add(selected, false, back, isList);
        }
    },

    //----------------------------------

    save_and_list_click: function(url)
    {
        if (this.getSelectedProducts()) {
            var back = 'view';
            var isList = 'yes';

            this.addListingHandlerObj.add(this.getSelectedProducts(), false, back, isList);
        }
    },

    //----------------------------------

//    set_attribute_filter: function()
//    {
//        ruleForm.submit();
//    },

    setGridId:  function(id)
    {
        this.gridId = id;
    },

    getGridId:  function()
    {
        return this.gridId;
    },

    //----------------------------------

    getSelectedProducts: function()
    {
        var selectedProducts = window[this.getGridId() + '_massactionJsObject'].checkedString;
        if (window.location.href.indexOf('/step/') + 1 && !selectedProducts) {
            var isEmpty = confirm(M2ePro.text.create_empty_listing_message);

            if (isEmpty) {
                return true;
            }

            return false;
        }

        if (!selectedProducts) {
            alert(M2ePro.text.select_items_message);
            return false;
        }
        return selectedProducts;
    }

    //----------------------------------
});