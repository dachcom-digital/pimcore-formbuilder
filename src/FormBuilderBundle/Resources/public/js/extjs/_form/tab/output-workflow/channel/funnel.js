pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.funnel');
Formbuilder.extjs.formPanel.outputWorkflow.channel.funnel = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    panel: null,
    funnelLayerPanel: null,
    funnelLayerDataClass: null,

    getLayout: function () {

        this.funnelLayerPanel = null;
        this.funnelLayerDataClass = null;

        this.panel = new Ext.form.FormPanel({
            title: false,
            border: false,
            defaults: {},
            items: this.getConfigFields()
        });

        return this.panel;
    },

    getVirtualFunnelActionDefinitions: function () {

        // do not allow virtual funnel action definitions
        // in this channel, we have real ones!

        return [];
    },

    getConfigFields: function () {

        var funnelLayerValue = this.data !== null && this.data.hasOwnProperty('type') ? this.data.type : null,
            funnelLayerCombo,
            funnelLayerStore;

        funnelLayerCombo = new Ext.form.ComboBox({
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.layers'),
            name: 'funnelLayer',
            submitValue: false,
            width: 350,
            value: null,
            displayField: 'label',
            valueField: 'key',
            mode: 'local',
            queryMode: 'local',
            labelAlign: 'left',
            triggerAction: 'all',
            editable: false,
            summaryDisplay: true,
            emptyText: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.no_layer'),
            allowBlank: false,
            disabled: true,
            listeners: {
                render: function (combo) {
                    combo.getStore().load();
                }.bind(this),
                change: function (combo, value) {
                    var record = combo.getStore().findRecord('key', value);
                    this.generateFunnelLayerPanel(value, record ? record.get('configuration') : null);
                }.bind(this)
            }
        });

        funnelLayerStore = new Ext.data.Store({
            autoLoad: false,
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: '/admin/formbuilder/output-workflow/funnel-layer/get-layers',
                fields: ['label', 'key', 'configuration'],
                reader: {
                    type: 'json',
                    rootProperty: 'funnelLayers'
                }
            },
            listeners: {
                load: function (store, records) {

                    if (records.length > 0) {
                        funnelLayerCombo.setDisabled(false);
                    }

                    funnelLayerCombo.setValue(funnelLayerValue);

                }.bind(this)
            }
        });

        funnelLayerCombo.setStore(funnelLayerStore);

        return [funnelLayerCombo];
    },

    generateFunnelLayerPanel: function (funnelLayerType, funnelLayerOptions) {

        var element, items;

        if (this.funnelLayerPanel !== null) {
            this.panel.remove(this.funnelLayerPanel);
        }

        this.funnelLayerDataClass = funnelLayerType === null ? null : this.createFunnelLayerDataClass(funnelLayerType, funnelLayerOptions);

        items = this.funnelLayerDataClass !== null
            ? this.funnelLayerDataClass.getConfigItems()
            : [{
                xtype: 'tbtext',
                style: 'padding: 10px 10px 10px 0',
                text: 'No configuration for "' + funnelLayerType + '" found.',
            }];

        element = new Ext.Panel({
            style: 'margin-top: 10px',
            autoHeight: true,
            border: false,
            items: items
        });

        this.funnelLayerPanel = element;

        this.panel.add(this.funnelLayerPanel);

        this.populateFunnelActions(funnelLayerOptions === null ? [] : funnelLayerOptions.funnelActionDefinitions, true);
    },

    createFunnelLayerDataClass: function (funnelLayerType, funnelLayerOptions) {

        var funnelLayerDataClass,
            funnelLayerConfig = this.assertFunnelLayerDataClassConfiguration(funnelLayerType);

        if (typeof Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer !== 'object') {
            return null;
        }

        if (typeof Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer[funnelLayerType] === 'undefined') {
            return null;
        }

        funnelLayerDataClass = new Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer[funnelLayerType](
            funnelLayerType,
            funnelLayerOptions,
            funnelLayerConfig,
            this.getWorkflowId(),
            this.getName(),
        );

        return funnelLayerDataClass;
    },

    assertFunnelLayerDataClassConfiguration: function (funnelLayerType) {

        if (this.data === null) {
            return null;
        }

        if (funnelLayerType !== this.data.type) {
            this.data = null;

            return null;
        }

        return this.data.hasOwnProperty('configuration') ? this.data.configuration : null;
    },

    isValid: function () {

        if (this.funnelLayerDataClass === null) {
            return false;
        }

        return this.panel.form.isValid();
    },

    getValues: function () {

        var configuration,
            formValues = {};

        if (this.funnelLayerDataClass === null) {
            return null;
        }

        configuration = DataObjectParser.transpose(this.panel.getForm().getValues());
        configuration = configuration.data();

        formValues['type'] = this.funnelLayerDataClass.getType();
        formValues['configuration'] = configuration;

        return formValues;
    },
});