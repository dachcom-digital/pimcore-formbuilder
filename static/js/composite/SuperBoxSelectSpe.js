Ext.namespace('Ext.ux.form');

Ext.ux.form.SuperBoxSelectSpe = Ext.extend(Ext.ux.form.SuperBoxSelect, {

//    initComponent: function($super) {
//        $super();
//    },

    getValue : function() {
        var ret = [];
        this.items.each(function(item){
            ret.push(item.value);
        });
        return ret;
    },
    /**
     * Sets the value of the SuperBoxSelect component.
     * @methodOf Ext.ux.form.SuperBoxSelect
     * @name setValue
     * @param {String|Array} value An array of item values, or a String value containing a delimited list of item values. (The list should be delimited with the {@link #Ext.ux.form.SuperBoxSelect-valueDelimiter)
     */
    setValue : function(value){
        if(!this.rendered){
            this.value = value.join(this.valueDelimiter);
            return;
        }
        this.removeAllItems().resetStore();
        this.remoteLookup = [];
        this.addValue(value);

    }
    


});
Ext.reg('superboxselectspe', Ext.ux.form.SuperBoxSelectSpe);