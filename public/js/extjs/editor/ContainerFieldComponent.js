class ContainerFieldComponent extends AbstractFieldComponent {

    constructor(editor, type, widgetConfiguration, widgets) {

        super(editor, type, widgetConfiguration, widgets);

        editor.ui.registry.addIcon('fb-container-field', '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M19 4a2 2 0 1 1-1.854 2.751L15 6.75c-1.239 0-1.85.61-2.586 2.31l-.3.724c-.42 1.014-.795 1.738-1.246 2.217.406.43.751 1.06 1.12 1.92l.426 1.018c.704 1.626 1.294 2.256 2.428 2.307l.158.004h2.145a2 2 0 1 1 0 1.501L15 18.75l-.219-.004c-1.863-.072-2.821-1.086-3.742-3.208l-.49-1.17c-.513-1.163-.87-1.57-1.44-1.614L9 12.75l-2.146.001a2 2 0 1 1 0-1.501H9c.636 0 1.004-.383 1.548-1.619l.385-.92c.955-2.291 1.913-3.382 3.848-3.457L15 5.25h2.145A2 2 0 0 1 19 4z" fill-rule="evenodd"/></svg>');

        editor.on('init', () => {
            this.setup();
        });

        editor.on('preinit', () => {

            this.win = editor.getWin();
            this.doc = editor.getDoc();

            editor.serializer.addNodeFilter('fb-container-field', (nodes) => {
                nodes.forEach((node) => {
                    if (!!node.attr('contenteditable')) {
                        node.attr('contenteditable', null);
                        node.firstChild.unwrap();
                    }
                });
            });

        });

        editor.on('drop', (evt) => {

            var element, elementData;

            if (!evt.dataTransfer) {
                return;
            }

            if (evt.target.nodeName.toLowerCase() === 'td') {
                return;
            }

            evt.preventDefault();

            element = evt.dataTransfer.getData('element');

            if (!element) {
                return;
            }

            elementData = JSON.parse(element);

            if (elementData.configIdentifier !== 'fb_field_container_fieldset' && elementData.configIdentifier !== 'fb_field_container_repeater') {
                return;
            }

            editor.insertContent(this.createContainerMarkup(elementData.type, elementData.subType));
        });
    }

    setup() {

        const _ = this,
            template = this.doc.createElement('template');

        template.innerHTML = `
        <style>
            :host {
                display: block;
                background-color: rgba(240, 210, 140, .20);
                border-radius: 6px;
            }

            header {
                display: flex;
                padding: 4px 6px;
                margin: 0;
                background-color: rgba(240, 210, 140, .20);
                border-radius: 0;
            }

            header p {
                margin: 0;
                line-height: 24px;
                font-size: 14px;
                color: #B7974C;
            }

            header > svg {
                fill: #B7974C;
                margin-right: 6px;
            }

            span#subtype {
                font-weight: bold;
            }

            button {
                background: rgba(240, 210, 140, .5);
                border: 0;
                outline: 0;
                -webkit-tap-highlight-color: rgba(0,0,0,0);
                -webkit-user-select: none;
                user-select: none;
                font-weight: normal;
                padding: 6px;
                margin: 0 0 0 10px;
                border-radius: 6px;
            }

            button svg {
                fill: #B7974C;
                display: block;
            }

            button:hover {
                background-color: rgba(240, 210, 140, .75);
            }

            .content {
                margin: 0 6px;
                box-sizing: border-box;
                padding-bottom: 2px;
            }
        </style>
        <header>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                <path d="M19 4a2 2 0 1 1-1.854 2.751L15 6.75c-1.239 0-1.85.61-2.586 2.31l-.3.724c-.42 1.014-.795 1.738-1.246 2.217.406.43.751 1.06 1.12 1.92l.426 1.018c.704 1.626 1.294 2.256 2.428 2.307l.158.004h2.145a2 2 0 1 1 0 1.501L15 18.75l-.219-.004c-1.863-.072-2.821-1.086-3.742-3.208l-.49-1.17c-.513-1.163-.87-1.57-1.44-1.614L9 12.75l-2.146.001a2 2 0 1 1 0-1.501H9c.636 0 1.004-.383 1.548-1.619l.385-.92c.955-2.291 1.913-3.382 3.848-3.457L15 5.25h2.145A2 2 0 0 1 19 4z" fill-rule="evenodd"/>
            </svg>
            <p><span id="subtype"></span></p>
            <button type="button" id="btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"><path d="M0 9.502v2.5h2.5l7.373-7.374-2.5-2.5L0 9.502zm11.807-6.807c.26-.26.26-.68 0-.94l-1.56-1.56a.664.664 0 0 0-.94 0l-1.22 1.22 2.5 2.5 1.22-1.22z"/></svg>
            </button>
        </header>
        <div class="content">
            <slot></slot>
        </div>
    `;

        class FbContainerField extends this.win.HTMLElement {
            constructor() {

                super();

                this.setAttribute('contenteditable', false);

                const shadow = this.attachShadow({mode: 'open'});

                this.shadowRoot.appendChild(template.content.cloneNode(true));
            }

            connectedCallback() {
                const cleanupContentEditable = () => {
                    if (this.firstChild.contentEditable !== 'true') {
                        const editableWrapper = document.createElement('div');
                        editableWrapper.setAttribute('contenteditable', true);

                        while (this.firstChild) {
                            editableWrapper.appendChild(this.firstChild)
                        }

                        this.appendChild(editableWrapper);
                    }
                }

                cleanupContentEditable();

                const editConditionalBlock = () => {
                    _.dialog(this);
                    return false;
                }

                this.shadowRoot.getElementById('btn').addEventListener('click', editConditionalBlock);
            }

            attributeChangedCallback(name, oldValue, newValue) {
                if (name === 'data-sub_type') {
                    this.shadowRoot.getElementById('subtype').textContent = newValue;
                }
            }

            static get observedAttributes() {
                return ['data-sub_type'];
            }

        }

        this.win.customElements.define('fb-container-field', FbContainerField);
    }

    dialog(field) {

        let widgetConfig = {},
            widgetConfigBody = {
                type: 'panel',
                items: []
            },
            initialData = {};

        const type = field ? field.dataset.type : null;
        const subType = field && field.dataset.sub_type !== 'null' ? field.dataset.sub_type : null;
        const widgetComponent = this.getWidgetComponent(type, subType);

        if (widgetComponent !== null) {
            widgetConfig = this.getWidgetConfig(widgetComponent.configIdentifier, field ? field.dataset : {});
            widgetConfigBody.items = widgetConfig.items;
            initialData = widgetConfig.initialData
        }

        this.editor.windowManager.open({
            title: 'Container Configuration',
            body: widgetConfigBody,
            initialData: initialData,
            buttons: [
                {
                    type: 'cancel',
                    name: 'closeButton',
                    text: 'Cancel'
                },
                {
                    type: 'submit',
                    name: 'submitButton',
                    text: 'Save',
                    primary: true
                }
            ],
            onSubmit: (dialog) => {

                const data = dialog.getData();

                if (!field) {
                    this.editor.insertContent(this.createContainerMarkup(type, subType, data));
                } else {
                    this.editor.undoManager.transact(() => {
                        Ext.Object.each(data, function (index, value) {
                            field.dataset[index] = value;
                        });
                    });

                    this.editor.nodeChanged();
                }

                dialog.close();
            }
        });
    }

    createContainerMarkup(type, subType, dialogData) {

        let widgetComponent = this.getWidgetComponent(type, subType),
            containerContent = '';

        if (widgetComponent !== null && widgetComponent.hasOwnProperty('children')) {

            if(this.type === 'html') {

                containerContent += '<table><tbody>';
                    Ext.Array.each(widgetComponent.children, function (subField) {

                        containerContent += `
                        <tr>
                            <td><fb-field data-render_type="L" data-type="${subField.type}" data-sub_type="${subField.subType}">${subField.label}</fb-field></td>
                            <td><fb-field data-render_type="V" data-type="${subField.type}" data-sub_type="${subField.subType}">${subField.label}</fb-field></td>
                        </tr>
                        `
                    });

                containerContent += '</tbody></table>';

            } else {

                Ext.Array.each(widgetComponent.children, function (subField) {
                    containerContent += `
                        <fb-field data-render_type="L" data-type="${subField.type}" data-sub_type="${subField.subType}">${subField.label}</fb-field>:
                        <fb-field data-render_type="V" data-type="${subField.type}" data-sub_type="${subField.subType}">${subField.label}</fb-field>
                    <br>
                    `
                });

            }
        }

        return `
                <fb-container-field 
                        data-type="${type}" 
                        data-sub_type="${subType}" 
                >
                    ${containerContent}
                </fb-container-field>
            `;
    }
}
