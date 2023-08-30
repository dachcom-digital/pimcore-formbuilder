pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.dynamicLayout');
Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.dynamicLayout = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.abstractLayer, {

    getConfigItems: function () {

        var localizedField = new Formbuilder.extjs.types.localizedField(function (locale) {
            return this.getLayoutField(locale);
        }.bind(this), true);

        return [
            localizedField.getField()
        ];
    },

    getLayoutField: function (locale) {

        var hrefValue = this.getLocalizedValue('layout', locale),
            fieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.dynamic_layout.layout'),
                id: locale + '.layout',
                config: {
                    types: ['document'],
                    subtypes: {document: ['snippet']}
                }
            }, hrefField = new Formbuilder.extjs.types.href(fieldConfig, hrefValue, null);

        return hrefField.getHref();
    },

    getLocalizedValue: function (key, locale) {

        if (this.funnelLayerConfig === null) {
            return null;
        }

        if (!this.funnelLayerConfig.hasOwnProperty(locale)) {
            return null;
        }

        if (this.funnelLayerConfig[locale].hasOwnProperty(key)) {
            return this.funnelLayerConfig[locale][key];
        }

        return null;
    }
});