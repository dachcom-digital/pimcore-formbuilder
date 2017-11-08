pimcore.registerNS('Formbuilder.comp.conditionalLogic.builder');
Formbuilder.comp.conditionalLogic.builder = Class.create({

    formBuilder: null,

    panel: null,

    tabPanel: null,

    sectionId: 0,

    conditionalData: [],

    initialize: function (conditionalData, formBuilder) {
        var _ = this;
        this.formBuilder = formBuilder;
        this.conditionalData = conditionalData;
        this.panel = new Ext.form.FieldSet({
            title: t('form_builder_conditional_logic'),
            collapsible: false,
            autoHeight: true,
            width: '100%',
            style: 'margin-top: 20px;',
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: ['->', {
                    xtype: 'button',
                    text: t('add'),
                    iconCls: 'pimcore_icon_add',
                    handler: _.addConditionalSection.bind(_),
                    tooltip: {
                        title: '',
                        text: t('form_builder_add_metadata')
                    }
                }]
            }]
        });

        if (this.conditionalData.cl && this.conditionalData.cl.length > 0) {
            Ext.Array.each(this.conditionalData.cl, function (group) {
                this.addConditionalSection(group);
            }.bind(this));
        }
    },

    getLayout: function () {
        return this.panel;
    },

    addConditionalSection: function (data) {

        var clFieldClass = new Formbuilder.comp.conditionalLogic.form(data, this.sectionId, this.formBuilder),
            layout = clFieldClass.getLayout();

        var conditionFieldSet = new Ext.form.FieldSet({
            hideLabel: false,
            title: t('formbuilder_conditional_section'),
            style: 'padding-bottom:5px; margin-bottom:30px; background: rgba(117, 139, 181, 0.2);',
            items: [layout]
        });

        conditionFieldSet.add([{
            xtype: 'button',
            text: t('formbuilder_delete_conditional_section'),
            iconCls: 'pimcore_icon_delete',
            handler: function (conditionFieldSet, el) {
                this.panel.remove(conditionFieldSet);
                this.panel.items.items[1].items.each(function (item, index) {
                    item.fireEvent('updateSectionId', index);
                });
            }.bind(this, conditionFieldSet)
        }]);

        this.sectionId++;
        this.panel.add(conditionFieldSet);
    }
});