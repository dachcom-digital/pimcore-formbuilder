Ext.define('FormBuilder.HrefTextField', {

    extend: 'Ext.form.TextField',
    href: null,

    setHrefObject: function (href) {
        this.href = href;
        this.setValue(this.href.path);
    },

    getSubmitData: function () {
        var data = {};
        data[this.getName()] = this.href;
        return data;
    }
});