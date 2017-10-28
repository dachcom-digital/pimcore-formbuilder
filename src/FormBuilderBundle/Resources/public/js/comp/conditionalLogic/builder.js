pimcore.registerNS('Formbuilder.comp.conditionalLogic.builder');
Formbuilder.comp.conditionalLogic.builder = Class.create({

    panel: null,

    tabPanel: null,

    initialize: function (formData) {
        var _ = this;
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
                        title:'',
                        text: t('form_builder_add_metadata')
                    }
                }]
            }]
        });
    },

    getLayout: function () {
        return this.panel;
    },

    addConditionalSection: function () {

        var clFieldClass = new Formbuilder.comp.conditionalLogic.form(),
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
            style: '',
            handler: function(conditionFieldSet, el) {
                this.panel.remove(conditionFieldSet);
                this.panel.updateLayout();
            }.bind(this, conditionFieldSet)
        }]);

        this.panel.add(conditionFieldSet);
        this.panel.updateLayout();

    }
});