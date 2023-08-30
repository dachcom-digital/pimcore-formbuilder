pimcore.registerNS('Formbuilder.extjs.types.localizedField');
Formbuilder.extjs.types.localizedField = Class.create({

    field: null,
    nestedFieldGenerator: null,
    addDefaultField: false,

    /**
     * @param nestedFieldGenerator
     * @param addDefaultField
     */
    initialize: function (nestedFieldGenerator, addDefaultField) {
        this.nestedFieldGenerator = nestedFieldGenerator;
        this.addDefaultField = addDefaultField === true;
        this.generateElement();
    },

    /**
     * @returns {null}
     */
    getField: function () {
        return this.field;
    },

    generateElement: function () {

        var tabs = [],
            pimcoreLocales = Ext.isArray(pimcore.settings.websiteLanguages) ? pimcore.settings.websiteLanguages : [],
            locales = this.addDefaultField ? Ext.Array.merge(['default'], pimcoreLocales) : pimcoreLocales;

        Ext.each(locales, function (locale) {
            tabs.push({
                title: locale === 'default' ? t('default') : pimcore.available_languages[locale],
                iconCls: locale === 'default' ? 'pimcore_icon_white_flag' : 'pimcore_icon_language_' + locale.toLowerCase(),
                layout: 'form',
                items: this.nestedFieldGenerator(locale)
            });
        }.bind(this));

        this.field = new Ext.form.FieldSet({
            cls: 'form_builder_type_localized_field',
            layout: 'hbox',
            flex: 1,
            hideLabel: false,
            items: [{
                xtype: 'tabpanel',
                activeTab: 0,
                width: '100%',
                defaults: {
                    autoHeight: true,
                    bodyStyle: 'padding:10px;'
                },
                items: tabs
            }]
        });
    }
});