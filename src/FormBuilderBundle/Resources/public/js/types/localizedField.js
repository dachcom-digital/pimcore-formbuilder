pimcore.registerNS('Formbuilder.comp.types.localizedField');
Formbuilder.comp.types.localizedField = Class.create({

    field: null,

    nestedFieldGenerator: null,

    /**
     * @param nestedFieldGenerator
     */
    initialize: function (nestedFieldGenerator) {
        this.nestedFieldGenerator = nestedFieldGenerator;
        this.generateElement();
    },

    /**
     * @returns {null}
     */
    getField: function () {
        return this.field;
    },

    generateElement: function () {

        var tabs = [];
        Ext.each(pimcore.settings.websiteLanguages, function (locale) {
            tabs.push({
                title: pimcore.available_languages[locale],
                iconCls: 'pimcore_icon_language_' + locale.toLowerCase(),
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