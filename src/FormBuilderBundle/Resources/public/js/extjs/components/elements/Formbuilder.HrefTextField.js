Ext.define('FormBuilder.HrefTextField', {

    extend: 'Ext.form.TextField',

    href: null,
    hrefLocale: null,
    customProperties: {},

    /**
     * @param locale
     */
    setHrefLocale: function (locale) {
        this.hrefLocale = locale;
    },

    /**
     * @returns {string|null}
     */
    getHrefLocale: function () {
        return this.hrefLocale;
    },

    /**
     * @param href
     */
    setHrefObject: function (href) {
        this.href = href;
        this.lastValue = null;
        this.setValue(this.href.hasOwnProperty('path') ? this.href.path : null);
    },

    /**
     * @returns {string|null}
     */
    getValue: function () {
        return this.href;
    },

    getSubmitData: function () {
        var data = {};
        data[this.getName()] = this.href;
        return data;
    }
});