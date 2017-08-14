pimcore.registerNS('Formbuilder.comp.type.formFieldConstraint');
Formbuilder.comp.type.formFieldConstraint = Class.create({

    form: null,

    formIsValid: true,

    formHandler: null,

    type: null,

    typeName: null,

    iconClass: null,

    storeData : {},

    initialize: function(formHandler, treeNode, constraint, values) {

        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.iconClass = constraint.icon_class;
        this.type = constraint.id;
        this.typeName = constraint.label;

        this.initData(values);

    },

    getType: function() {
        return this.type;
    },

    getName: function() {
        return this.type;
    },

    getTypeName: function() {
        return this.typeName;
    },

    getIconClass: function() {
        return this.iconClass;
    },

    initData: function(values) {

        this.valid = true;

        if(values) {
            this.storeData = values;
        } else {
            this.storeData = {
                type: this.getType()
            };
        }

        this.renderLayout();

    },

    renderLayout: function() {

        var items = [];

        var item = new Ext.Panel({
            title: t('form_builder_base'),
            closable: false,
            autoScroll: true,
            items: [
                this.getForm()
            ]

        });

        items.push(item);

        this.form = new Ext.form.Panel({
            items: {
                xtype: 'tabpanel',
                tabPosition: 'top',
                region:'center',
                deferredRender: true,
                enableTabScroll: true,
                border: false,
                items: items,
                activeTab: 0
            }
        });

        return this.form;
    },

    isValid: function() {
        return this.formIsValid;
    },

    applyData: function() {

        this.formIsValid = this.form.isValid();
        this.storeData =  this.form.getValues();
        this.storeData.type = this.getType();

    },

    getData: function() {
        return this.storeData;
    },

    getForm: function() {

        var form = this.createBaseForm();
        return form;

    },

    createBaseForm: function() {

        var _ = this,
            form = new Ext.form.Panel({
                bodyStyle: 'padding: 10px;',
                labelWidth: 150,
                defaultType: 'textfield',
            });

        form.add(new Ext.form.Label({
            name: 'label',
            text: 'Nothing to do so far. Just enjoy this fancy constraint.',
            style: {
                padding: '10px 0 0 0',
                width: '100%'
            },
            anchor: '100%'
        }));

        return form;

    }

});