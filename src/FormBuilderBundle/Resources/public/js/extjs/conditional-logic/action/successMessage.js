pimcore.registerNS('Formbuilder.extjs.conditionalLogic.action');
pimcore.registerNS('Formbuilder.extjs.conditionalLogic.action.successMessage');
Formbuilder.extjs.conditionalLogic.action.successMessage = Class.create(Formbuilder.extjs.conditionalLogic.action.abstract, {

    valueField: null,
    fieldPanel: null,
    nameGenerator: null,

    updateInternalPositionIndex: function (sectionId, index) {
        this.sectionId = sectionId;
        this.index = index;
    },

    generateFieldName: function (sectionId, index, name) {
        return 'cl.' + sectionId + '.action.' + index + '.' + name;
    },

    getItem: function () {

        var _ = this,
            fieldId = Ext.id(),
            successMessageToggleComponent,
            componentConfiguration = {
                identifier: this.fieldConfiguration.identifier,
                sectionId: this.sectionId,
                index: this.index,
                onGenerateFieldName: function (elementType, args, el) {

                    var sectionId = this.sectionId;
                    var index = this.index;

                    if (typeof args === 'object') {
                        sectionId = args[0];
                        index = args[1];
                    }

                    if (typeof el !== 'undefined') {
                        if (elementType === 'localizedValueField') {
                            // inform sub-extjs components
                            var localizedTextFields = el.query('textfield');
                            if (localizedTextFields.length > 0) {
                                Ext.Array.each(localizedTextFields, function (field) {
                                    field.fireEvent('updateIndexName', sectionId, index);
                                });
                            }

                            return;

                        } else {
                            // update extjs component
                            el.name = this.generateFieldName(sectionId, index, elementType);
                            if (elementType === 'type') {
                                this.updateInternalPositionIndex(sectionId, index);
                            }

                            return;
                        }
                    }

                    return this.generateFieldName(sectionId, index, elementType);

                }.bind(this),
                onGenerateTopBar: function () {
                    return this.getTopBar(fieldId);
                }.bind(this)
            };

        successMessageToggleComponent = new Formbuilder.extjs.components.successMessageToggleComponent(fieldId, componentConfiguration, this.data);

        return successMessageToggleComponent.getLayout();
    }
});
