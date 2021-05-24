# Dynamic Multi File | Custom Adapter
Creating a custom adapter is quite easy. 

There are 5 steps to go:

- Evaluate your library
- Register Services & adjust form builder
- Create Form Type
- Create Adapter
- Create Js Handler

***

## I. Register Services

```yaml
services:

    _defaults:
        autowire: true
        autoconfigure: true
        public: true

    AppBundle\DynamicMultiFile\Adapter\MyUploaderAdapter:
        tags:
            - { name: form_builder.dynamic_multi_file.adapter }

    AppBundle\Form\Type\DynamicMultiFile\MyUploaderType:
        public: false
        tags:
            - { name: form.type }

```

## II. Override default handler path

```javascript
$('form.formbuilder.ajax-form').formBuilderAjaxManager({
    // tell form builder core extension where to lazy load your handler from!
    dynamicMultiFileDefaultHandlerPath: '/bundles/app/js/dynamic-multi-file-adapter/lib',
    dynamicMultiFileHandlerOptions: {
        // these options will be passed to your new handler
        // you can define any options here.
        // libPath is just an recommendation so your handler will load the lib only if any upload field is available to render.
        libPath: 'https://cdnjs.cloudflare.com/ajax/libs/my-3rd-party-lib.min.js',
    },
});
```

## III. Register Services

### FormType

```php
<?php

namespace AppBundle\Form\Type\DynamicMultiFile;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyUploaderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
         $view->vars = array_merge_recursive($view->vars, [
            'attr' => [
                'data-field-id'       => $view->parent->vars['id'],
                'data-engine-options' => json_encode([
                    'multiple'           => isset($options['multiple']) ? $options['multiple'] : false,
                    'max_file_size'      => is_numeric($options['max_file_size']) && $options['max_file_size'] > 0 ? $options['max_file_size'] : null,
                    'item_limit'         => is_numeric($options['item_limit']) && $options['item_limit'] > 0 ? $options['item_limit'] : null,
                    'allowed_extensions' => is_array($options['allowed_extensions']) ? $options['allowed_extensions'] : null,
                ]),
                'class'               => join(' ', [
                    'dynamic-multi-file',
                    sprintf('element-%s', $view->vars['name'])
                ])
            ]   
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // these options are required to support!
        
        $resolver->setDefaults([
            'max_file_size'        => null,
            'allowed_extensions'   => [],
            'item_limit'           => null,
            'submit_as_attachment' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_builder_dynamicmultifile_my_uploader';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }
}
```

### Adapter

```php
<?php

namespace AppBundle\DynamicMultiFile\Adapter;

use AppBundle\Form\Type\DynamicMultiFile\MyUploaderType;
use FormBuilderBundle\DynamicMultiFile\Adapter\DynamicMultiFileAdapterInterface;
use FormBuilderBundle\Stream\FileStreamInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyUploaderAdapter implements DynamicMultiFileAdapterInterface
{
    protected FileStreamInterface $fileStream;

    /**
     * @param FileStreamInterface $fileStream
     */
    public function __construct(FileStreamInterface $fileStream)
    {
        $this->fileStream = $fileStream;
    }

    /**
     * {@inheritDoc}
     */
    public function getForm(): string
    {
        return MyUploaderType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getJsHandler(): string
    {
        return 'my-uploader';
    }

    /**
     * {@inheritDoc}
     */
    public function onUpload(Request $request): Response
    {
        // second argument needs to be false, if you also invoke the onDone action!
        
        $result = $this->fileStream->handleUpload([
            'binary'          => 'REQUEST_PARAM', // replace REQUEST_PARAM with the mapped request parameter, provided in your JS library
            'uuid'            => 'REQUEST_PARAM',
            'chunkIndex'      => 'REQUEST_PARAM',
            'totalChunkCount' => 'REQUEST_PARAM',
            'totalFileSize'   => 'REQUEST_PARAM',
        ], true);

        return new JsonResponse($result);
    }

    /**
     * {@inheritDoc}
     */
    public function onDone(Request $request): Response
    {
        // if you need a dedicated endpoint for a chunked upload completion event,
        // use this action (which is mostly not required)
        
        return new JsonResponse([
            'success' => false,
            'message' => 'not implemented'
        ], 403);
    }

    /**
     * {@inheritDoc}
     */
    public function onDelete(Request $request): Response
    {
        // $identifier = mostly the upload uuid
        $identifier = $request->attributes->get('identifier');
        
        // some libraries will trigger this action after user cancels an active (chunked) file upload
        $checkChunkFolder = $request->request->get('uploadStatus') === 'canceled';

        $result = $this->fileStream->handleDelete($identifier, $checkChunkFolder);

        return new JsonResponse($result);
    }
}
```

## IV. Twig Form Theme

```twig
{% block form_builder_dynamicmultifile_my_uploader_widget %}
    {% apply spaceless %}
        <div {{ block('attributes') }}>
            <div class="my-uploader-container">{# your adapter markup #}</div>
        </div>
    {% endapply %}
{% endblock %}
```

## V. JavaScript Handler

```javascript
// AppBundle/Resources/public/js/dynamic-multi-file-adapter/lib/jquery.fb.dmf.my-uploader.js

;(function ($, window) {

    'use strict';

    function FormBuilderDynamicMultiFileMyUploader() {
        return this;
    }

    $.extend(FormBuilderDynamicMultiFileMyUploader.prototype, {

        init: function ($form, $dmfFields, dataUrls, options) {

            if (this.initialized === true) {
                return;
            }

            this.$form = $form;
            this.$dmfFields = $dmfFields;
            this.dataUrls = dataUrls;
            this.options = options;
            this.initialized = true;

            this.prepareLibrary();
        },

        prepareLibrary: function () {

            if (window.myUploaderLibrary !== undefined) {
                this.prepareForm();
                return;
            }

            if (typeof this.options.libPath === 'undefined') {
                return;
            }

            $.getScript(this.options.libPath, function (data, textStatus, jqxhr) {
                if (jqxhr.status === 200) {
                    this.prepareForm();
                }
            }.bind(this));

        },

        prepareForm: function () {
            this.addListener();
            this.$dmfFields.each(this.setupMyUploadElement.bind(this));
        },

        setupMyUploadElement: function (index, el) {

            var _ = this,
                $el = $(el),
                $element = $el.find('.my-uploader-container'),
                fieldId = $el.data('field-id'),
                storageFieldId = fieldId + '_data',
                $storageField = this.$form.find('input[type="hidden"][id="' + storageFieldId + '"]'),
                config = $el.data('engine-options'),
                myUploadConfiguration;

            // this is just an example configuration, 
            // check the corresponding docs provided by your js lib
            
            myUploadConfiguration = {
                addUrl: _.getDataUrl('file_add'),
                deleteUrl: _.getDataUrl('file_delete'),
                doneUrl: _.getDataUrl('file_chunk_done'),
                onAdd: function(response) {
                    this.addToStorageField($storageField, {
                        id: response.uuid,
                        fileName: response.fileName
                    });
                },
                onRemove: function(response) {
                    this.removeFromStorageField($storageField, {
                        id: response.uuid
                    });
                }
            };

            // allow 3rd party hooks
            this.$form.trigger('formbuilder.dynamic_multi_file.init', [$el, this.options, myUploadConfiguration]);

            $element.myUploaderLibrary(myUploadConfiguration);
        },

        addToStorageField: function ($storage, newData) {

            var data = typeof $storage.val() === 'string' && $storage.val() !== ''
                ? $.parseJSON($storage.val())
                : [];

            data.push(newData);

            $storage.val(JSON.stringify(data));
        },

        removeFromStorageField: function ($storage, newData) {

            var position,
                data = typeof $storage.val() === 'string' && $storage.val() !== ''
                    ? $.parseJSON($storage.val())
                    : [];

            position = $.map(data, function (block) {
                return block.id
            }).indexOf(newData.id);

            if (position === -1) {
                return;
            }

            data.splice(position, 1)

            $storage.val(JSON.stringify(data));
        },

        addListener: function () {

            this.$form.on({
                // add events for repeater fields!
                'formbuilder.layout.post.add': function (ev, layout) {

                    var $el = $(layout),
                        $instances;

                    $instances = $el.find('[data-dynamic-multi-file-instance]');
                    if ($instances.length === 0) {
                        return;
                    }

                    $instances.each(this.setupMyUploadElement.bind(this));

                }.bind(this),
                'formbuilder.layout.pre.remove': function (ev, layout) {

                    var $el = $(layout),
                        $uploadFields;

                    $uploadFields = $el.find('[data-dynamic-multi-file-instance]');
                    if ($uploadFields.length === 0) {
                        return;
                    }

                    $uploadFields.each(function (index, el) {

                        var $el = $(el),
                            config = $el.data('engine-options');

                        // check if field has an active upload process.
                        // if so, throw an error which will be catched via alert:

                        // throw new Error('Cannot remove this block.');
                    });
                }
            });
        },

        getDataUrl: function (section) {
            return this.dataUrls[section];
        }
    });

    // window instance requires to be called "formBuilderDynamicMultiFileHandler"
    window.formBuilderDynamicMultiFileHandler = new FormBuilderDynamicMultiFileMyUploader();

})(jQuery, window);
```