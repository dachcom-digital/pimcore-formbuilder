<?php

namespace FormBuilderBundle\EventListener\Admin;

use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Event\BundleManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BundleManagerEvents::CSS_PATHS          => 'addCssFiles',
            BundleManagerEvents::EDITMODE_CSS_PATHS => 'addEditModeCssFiles',
            BundleManagerEvents::JS_PATHS           => 'addJsFiles',
            BundleManagerEvents::EDITMODE_JS_PATHS  => 'addEditModeJsFiles'
        ];
    }

    public function addCssFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/formbuilder/css/admin.css'
        ]);
    }

    public function addEditModeCssFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/formbuilder/css/admin-editmode.css',
        ]);
    }

    public function addJsFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/formbuilder/js/extjs/plugin.js',
            '/bundles/formbuilder/js/extjs/settings.js',
            '/bundles/formbuilder/js/extjs/types/keyValueRepeater.js',
            '/bundles/formbuilder/js/extjs/types/localizedField.js',
            '/bundles/formbuilder/js/extjs/types/href.js',
            '/bundles/formbuilder/js/extjs/_form/form.js',
            '/bundles/formbuilder/js/extjs/eventObserver.js',
            '/bundles/formbuilder/js/extjs/_form/tab/configPanel.js',
            '/bundles/formbuilder/js/extjs/_form/tab/outputWorkflowPanel.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/outputWorkflowConfigPanel.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/abstractChannel.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/email.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/object.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/api.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/funnelActionDispatcher.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/action/abstractAction.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/action/channelAction.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/action/returnToFormAction.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/action/disabledAction.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/layer/abstractLayer.js',
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/layer/dynamicLayoutLayer.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/abstract.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/checkbox.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/href.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/key_value_repeater.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/label.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/numberfield.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/options_repeater.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/select.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/tagfield.js',
            '/bundles/formbuilder/js/extjs/_form/config-fields/textfield.js',
            '/bundles/formbuilder/js/extjs/_form/data-injection/expression.js',
            '/bundles/formbuilder/js/extjs/extensions/formMetaData.js',
            '/bundles/formbuilder/js/extjs/extensions/formMailEditor.js',
            '/bundles/formbuilder/js/extjs/extensions/formApiMappingEditor.js',
            '/bundles/formbuilder/js/extjs/extensions/formDataMappingEditor/formDataMapper.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor/formObjectTreeMapper.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor/worker/fieldCollectionWorker.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor/worker/relationWorker.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/builder.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/form.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/condition/abstract.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/condition/elementValue.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/condition/outputWorkflow.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/abstract.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/constraintsAdd.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/constraintsRemove.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/toggleElement.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/changeValue.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/triggerEvent.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/toggleClass.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/toggleAvailability.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/switchOutputWorkflow.js',
            '/bundles/formbuilder/js/extjs/conditional-logic/action/successMessage.js',
            '/bundles/formbuilder/js/extjs/components/formTypeBuilderComponent.js',
            '/bundles/formbuilder/js/extjs/components/formFieldConstraintComponent.js',
            '/bundles/formbuilder/js/extjs/components/formFieldContainerComponent.js',
            '/bundles/formbuilder/js/extjs/components/formImporterComponent.js',
            '/bundles/formbuilder/js/extjs/components/successMessageToggleComponent.js',
            '/bundles/formbuilder/js/extjs/components/elements/Formbuilder.HrefTextField.js',
            '/bundles/formbuilder/js/extjs/vendor/dataObject.js',
        ]);
    }

    public function addEditModeJsFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/formbuilder/js/admin/area.js'
        ]);
    }
}
