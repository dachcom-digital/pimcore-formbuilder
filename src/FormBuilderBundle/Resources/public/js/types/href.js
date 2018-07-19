pimcore.registerNS('Formbuilder.comp.types.href');
Formbuilder.comp.types.href = Class.create({

    fieldConfig: null,
    storeData: null,
    href: null,
    locale: null,

    /**
     *
     * @param fieldConfig
     * @param storeData
     */
    initialize: function (fieldConfig, storeData, locale) {
        this.fieldConfig = fieldConfig;
        this.storeData = storeData;
        this.locale = locale;
        this.generateElement();
    },

    /**
     * @returns Ext.panel.Panel
     */
    getHref: function () {
        return this.href;
    },

    /**
     * Generate href Element
     */
    generateElement: function () {

        this.data = {
            id: this.storeData ? this.storeData.id : null,
            path: this.storeData ? this.storeData.path : null,
            type: this.storeData ? this.storeData.type : null,
            subtype: this.storeData ? this.storeData.subtype : null
        };

        var options = {}
        options.width = 400;
        options.fieldLabel = this.fieldConfig.label;
        options.enableKeyEvents = true;
        options.emptyText = t('drop_element_here');
        options.fieldCls = 'pimcore_droptarget_input';
        options.name = this.generateFieldName(this.fieldConfig.id);

        this.href = Ext.create('FormBuilder.HrefTextField', options);

        if (this.storeData && this.storeData.id) {
            this.href.setHrefObject(this.storeData);
        }

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
            element.setHrefObject(this.data);
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
    onNodeOver: function (target, dd, e, data) {
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

        if (!this.dndAllowed(record)) {
            return false;
        }

        this.data.id = record.data.id;
        this.data.type = record.data.elementType;
        this.data.subtype = record.data.type;
        this.data.path = record.data.path;

        this.href.setHrefObject(this.data);

        return true;
    },

    /**
     *
     * @param data
     * @returns {boolean}
     */
    dndAllowed: function (data) {

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

                    if ((typeof this.fieldConfig.config.subtypes !== 'undefined')
                        && this.fieldConfig.config.subtypes[type]
                        && this.fieldConfig.config.subtypes[type].length) {
                        checkSubType = true;
                    }
                    if (data.data.elementType == 'object' && this.fieldConfig.config.classes) {
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

        if (this.data.id) {
            menu.add(new Ext.menu.Item({
                text: t('empty'),
                iconCls: 'pimcore_icon_delete',
                handler: function (item) {
                    item.parentMenu.destroy();
                    this.data = {};
                    this.href.setHrefObject(this.data);
                }.bind(this)
            }));

            menu.add(new Ext.menu.Item({
                text: t('open'),
                iconCls: 'pimcore_icon_open',
                handler: function (item) {
                    item.parentMenu.destroy();
                    if (this.data.type == 'document') {
                        pimcore.helpers.openDocument(this.data.id, this.data.subtype);
                    }
                    else if (this.data.type == 'asset') {
                        pimcore.helpers.openAsset(this.data.id, this.data.subtype);
                    }
                    else if (this.data.type == 'object') {
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
                        pimcore.treenodelocator.showInTree(this.data.id, this.data.type);
                    }.bind(this)
                }));
            }
        }

        if (menu.items.length > 0) {
            menu.showAt(e.getXY());
        }

        e.stopEvent();
    },

    /**
     * @param data
     * @returns {*}
     */
    getCustomPimcoreDropData: function (data) {
        if (typeof(data.grid) != 'undefined' && typeof(data.grid.getCustomPimcoreDropData) == 'function') {
            var record = data.grid.getStore().getAt(data.rowIndex);
            var data = data.grid.getCustomPimcoreDropData(record);
        }
        return data;
    },

    generateFieldName(name) {
        return name + '.' + this.locale;
    }

});