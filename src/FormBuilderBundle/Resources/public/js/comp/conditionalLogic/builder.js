pimcore.registerNS('Formbuilder.comp.conditionalLogic.builder');
Formbuilder.comp.conditionalLogic.builder = Class.create({

    formBuilder: null,

    panel: null,

    tabPanel: null,

    sectionId: 0,

    conditionalData: [],

    conditionalStore: [],

    initialize: function (conditionalData, conditionalStore, formBuilder) {
        this.formBuilder = formBuilder;
        // namespace in extJs is .cl
        this.conditionalData = typeof conditionalData.cl === 'object' ? conditionalData.cl : conditionalData;
        this.conditionalStore = conditionalStore;

        this.panel = new Ext.form.FieldSet({
            title: t('form_builder_conditional_logic'),
            cls: 'form-builder-conditional-logic-field-set',
            collapsible: false,
            autoHeight: true,
            width: '100%',
            style: 'margin-top: 20px;',
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: ['->', {
                    xtype: 'button',
                    disabled: !this.formBuilder.hasFields('field'),
                    text: t('add'),
                    iconCls: 'pimcore_icon_add',
                    handler: this.addConditionalSection.bind(this),
                    tooltip: {
                        title: '',
                        text: t('form_builder_add_metadata')
                    }
                }]
            }]
        });

        if (this.conditionalData.length > 0) {
            Ext.Array.each(this.conditionalData, function (group) {
                this.addConditionalSection(group);
            }.bind(this));
        }
    },

    getLayout: function () {
        return this.panel;
    },

    addConditionalSection: function (data) {

        var clFieldClass = new Formbuilder.comp.conditionalLogic.form(data, this.sectionId, this.conditionalStore, this.formBuilder),
            layout = clFieldClass.getLayout(),
            conditionFieldSet = new Ext.form.FieldSet({
                hideLabel: false,
                cls: 'form_builder_conditional_section',
                title: t('form_builder_conditional_section'),
                style: 'padding-bottom:5px; margin-bottom:30px; background: rgba(117, 139, 181, 0.2);',
                items: [layout]
            });

        conditionFieldSet.add([{
            xtype: 'button',
            text: t('form_builder_delete_conditional_section'),
            iconCls: 'pimcore_icon_delete',
            handler: function (conditionFieldSet) {
                var sectionFieldSets;
                this.panel.remove(conditionFieldSet);
                sectionFieldSets = this.panel.query('fieldset[cls~="form_builder_conditional_section"]');
                this.sectionId = sectionFieldSets.length;
                Ext.Array.each(sectionFieldSets, function (fieldSet, sIndex) {
                    var sectionFieldContainer = fieldSet.query('fieldcontainer[cls~="form_builder_conditional_section_container"]');
                    Ext.Array.each(sectionFieldContainer, function (container, cIndex) {
                        container.fireEvent('updateSectionId', sIndex);
                    });
                })
            }.bind(this, conditionFieldSet)
        }]);

        this.sectionId++;
        this.panel.add(conditionFieldSet);
    }
});