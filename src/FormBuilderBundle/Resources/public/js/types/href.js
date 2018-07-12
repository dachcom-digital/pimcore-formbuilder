pimcore.registerNS('Formbuilder.comp.types.href');
Formbuilder.comp.types.href = Class.create({

    fieldConfig: null,
    storeData: null,
    href: null,
    hrefType: null,
    locale: null,

    /**
     *
     * @param fieldConfig
     * @param storeData
     */
    initialize: function(fieldConfig, storeData, locale) {
        this.fieldConfig = fieldConfig;
        this.storeData = storeData;
        this.locale = locale;
        this.generateElement();
    },

    /**
     * @returns Ext.panel.Panel
     */
    getHref: function() {

        var items = [
            this.href,
            this.hrefType
        ]

        return item = new Ext.Panel({
            closable: false,
            autoScroll: true,
            items: items

        });
    },

    /**
     * Generate hrefType and href Element
     */
    generateElement: function() {

        this.hrefType = new Ext.form.Hidden({
            'name' : this.generateFieldName('options.href_type')
        }),

        this.data = {
            id: null,
            path: '',
            elementType: '',
            subtype: ''
        };

        this.options = {}
        this.options.width = 400;

        if (this.storeData.path) {

            this.options.value = this.storeData.path;
            this.hrefType.setValue(this.storeData.hrefType);

            Ext.Ajax.request({
                url: '/admin/formbuilder/settings/get-element-by-path',
                params: {
                    path: this.storeData.path,
                    hrefType:  this.storeData.hrefType
                },
                success: function (response) {
                    var res = Ext.decode(response.responseText);
                    this.data.id = res.id;
                    this.data.path = this.storeData.path;
                    this.data.elementType = res.type;
                    this.data.subtype = res.subtype;
                }.bind(this)
            });
        }

        this.options.fieldLabel = this.fieldConfig.label;
        this.options.enableKeyEvents = true;
        this.options.emptyText = t('drop_element_here');
        this.options.fieldCls = 'pimcore_droptarget_input';
        this.options.name = this.generateFieldName(this.fieldConfig.id);

        this.href = new Ext.form.TextField(this.options);

        this.href.on('render', function (el) {

            new Ext.dd.DropZone(el.getEl(), {
                reference: this,
                ddGroup: 'element',
                getTargetFromEvent: function (e) {
                    return this.reference.href.getEl();
                },
                onNodeOver: this.onNodeOver.bind(this),
                onNodeDrop: this.onNodeDrop.bind(this)
            });

            el.getEl().on('contextmenu', this.onContextMenu.bind(this));

        }.bind(this));

        this.href.on('keyup', function (element, event) {
            element.setValue(this.data.path);
        }.bind(this));

    },

    /**
     *
     * @param target
     * @param dd
     * @param e
     * @param data
     * @returns {*}
     */
    onNodeOver: function(target, dd, e, data) {
        var record = data.records[0];

        record = this.getCustomPimcoreDropData(record);
        if (this.dndAllowed(record)) {
            return Ext.dd.DropZone.prototype.dropAllowed;
        }
        else {
            return Ext.dd.DropZone.prototype.dropNotAllowed;
        }
    },

    /**
     *
     * @param target
     * @param dd
     * @param e
     * @param data
     * @returns {boolean}
     */
    onNodeDrop: function (target, dd, e, data) {
        var record = data.records[0];

        record = this.getCustomPimcoreDropData(record);

        if(!this.dndAllowed(record)){
            return false;
        }

        this.data.id = record.data.id;
        this.data.elementType = record.data.elementType;
        this.data.subtype = record.data.type;
        this.data.path = record.data.path;

        this.href.setValue(record.data.path);
        this.hrefType.setValue(record.data.elementType);

        return true;
    },

    /**
     *
     * @param data
     * @returns {boolean}
     */
    dndAllowed: function(data) {

        var i,
            found,
            checkSubType = false,
            checkClass = false,
            type;

        if (this.fieldConfig.config.types) {
            found = false;
            for (i = 0; i < this.fieldConfig.config.types.length; i++) {
                type = this.fieldConfig.config.types[i];
                if (type == data.data.elementType) {
                    found = true;

                    if((typeof this.fieldConfig.config.subtypes !== 'undefined')
                        && this.fieldConfig.config.subtypes[type]
                        && this.fieldConfig.config.subtypes[type].length) {
                        checkSubType = true;
                    }
                    if(data.data.elementType == 'object' && this.fieldConfig.config.classes) {
                        checkClass = true;
                    }
                    break;
                }
            }
            if (!found) {
                return false;
            }
        }

        //subtype check  (folder,page,snippet ... )
        if (checkSubType) {

            found = false;
            var subTypes = this.fieldConfig.config.subtypes[type];
            for (i = 0; i < subTypes.length; i++) {
                if (subTypes[i] == data.data.type) {
                    found = true;
                    break;
                }

            }
            if (!found) {
                return false;
            }
        }

        //object class check
        if (checkClass) {
            found = false;
            for (i = 0; i < this.fieldConfig.config.classes.length; i++) {
                if (this.fieldConfig.config.classes[i] == data.data.className) {
                    found = true;
                    break;
                }
            }
            if (!found) {
                return false;
            }
        }

        return true;
    },

    /**
     *
     * @param e
     */
    onContextMenu: function (e) {

        var menu = new Ext.menu.Menu();

        if(this.data.id) {
            menu.add(new Ext.menu.Item({
                text: t('empty'),
                iconCls: 'pimcore_icon_delete',
                handler: function (item) {
                    item.parentMenu.destroy();
                    this.data = {};
                    this.href.setValue(this.data.path);
                    this.hrefType.setValue(this.data.subtype);

                }.bind(this)
            }));

            menu.add(new Ext.menu.Item({
                text: t('open'),
                iconCls: 'pimcore_icon_open',
                handler: function (item) {
                    item.parentMenu.destroy();
                    if (this.data.elementType == 'document') {
                        pimcore.helpers.openDocument(this.data.id, this.data.subtype);
                    }
                    else if (this.data.elementType == 'asset') {
                        pimcore.helpers.openAsset(this.data.id, this.data.subtype);
                    }
                    else if (this.data.elementType == 'object') {
                        pimcore.helpers.openObject(this.data.id, this.data.subtype);
                    }
                }.bind(this)
            }));

            if (pimcore.elementservice.showLocateInTreeButton('document')) {
                menu.add(new Ext.menu.Item({
                    text: t('show_in_tree'),
                    iconCls: 'pimcore_icon_show_in_tree',
                    handler: function (item) {
                        item.parentMenu.destroy();
                        pimcore.treenodelocator.showInTree(this.data.id, this.data.elementType);
                    }.bind(this)
                }));
            }
        }

        if(menu.items.length > 0) {
            menu.showAt(e.getXY());
        }

        e.stopEvent();
    },

    /**
     * @param data
     * @returns {*}
     */
    getCustomPimcoreDropData : function (data){
        if(typeof(data.grid) != 'undefined' && typeof(data.grid.getCustomPimcoreDropData) == 'function'){
            var record = data.grid.getStore().getAt(data.rowIndex);
            var data = data.grid.getCustomPimcoreDropData(record);
        }
        return data;
    },

    generateFieldName(name) {
        return name + '.' + this.locale;
    }

});