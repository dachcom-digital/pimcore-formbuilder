<?php

namespace FormBuilderBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\DBAL\Types\Type;
use FormBuilderBundle\DependencyInjection\CompilerPass\ApiProviderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\ChoiceBuilderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DispatcherPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DynamicMultiFileAdapterPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DynamicObjectResolverPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\FieldTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\MailEditorWidgetPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OptionsTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputWorkflowChannelPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputWorkflowFunnelActionPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputWorkflowFunnelLayerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\RuntimeDataProviderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\StorageProviderPass;
use FormBuilderBundle\Doctrine\Type\FormBuilderFieldsType;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;
use FormBuilderBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormBuilderBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'dachcom-digital/formbuilder';

    public function boot(): void
    {
        $this->addDBALTypes();
    }

    private function addDBALTypes(): void
    {
        if (Type::hasType('form_builder_fields')) {
            return;
        }

        Type::addType('form_builder_fields', FormBuilderFieldsType::class);

        /** @var FormBuilderFieldsType $formBuilderFieldsType */
        $formBuilderFieldsType = Type::getType('form_builder_fields');
        $formBuilderFieldsType->setFormDefinitionFactory($this->container->get(FormDefinitionFactoryInterface::class));
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->configureDoctrineExtension($container);

        $container->addCompilerPass(new OptionsTransformerPass());
        $container->addCompilerPass(new DispatcherPass());
        $container->addCompilerPass(new ChoiceBuilderPass());
        $container->addCompilerPass(new MailEditorWidgetPass());
        $container->addCompilerPass(new OutputTransformerPass());
        $container->addCompilerPass(new OutputWorkflowChannelPass());
        $container->addCompilerPass(new OutputWorkflowFunnelLayerPass());
        $container->addCompilerPass(new OutputWorkflowFunnelActionPass());
        $container->addCompilerPass(new DynamicObjectResolverPass());
        $container->addCompilerPass(new RuntimeDataProviderPass());
        $container->addCompilerPass(new DynamicMultiFileAdapterPass());
        $container->addCompilerPass(new ApiProviderPass());
        $container->addCompilerPass(new FieldTransformerPass());
        $container->addCompilerPass(new StorageProviderPass());
    }

    public function getInstaller(): Install
    {
        return $this->container->get(Install::class);
    }

    public function getJsPaths(): array
    {
        return [
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
            '/bundles/formbuilder/js/extjs/_form/tab/output-workflow/channel/funnel/layer/simpleLayoutLayer.js',
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
            '/bundles/formbuilder/js/extjs/extensions/formMetaData.js',
            '/bundles/formbuilder/js/extjs/extensions/formMailEditor.js',
            '/bundles/formbuilder/js/extjs/extensions/formApiMappingEditor.js',
            '/bundles/formbuilder/js/extjs/extensions/formDataMappingEditor/formDataMapper.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor/formObjectTreeMapper.js',
            '/bundles/formbuilder/js/extjs/extensions/formObjectMappingEditor/worker/fieldCollectionWorker.js',
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
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/formbuilder/css/admin.css'
        ];
    }

    public function getEditmodeJsPaths(): array
    {
        return [
            '/bundles/formbuilder/js/admin/area.js'
        ];
    }

    public function getEditmodeCssPaths(): array
    {
        return [
            '/bundles/formbuilder/css/admin-editmode.css',
        ];
    }

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }

    protected function configureDoctrineExtension(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                [$this->getNameSpacePath() => $this->getNamespaceName()],
                ['form_builder.persistence.doctrine.manager'],
                'form_builder.persistence.doctrine.enabled'
            )
        );
    }

    protected function getNamespaceName(): string
    {
        return 'FormBuilderBundle\Model';
    }

    protected function getNameSpacePath(): string
    {
        return sprintf(
            '%s/Resources/config/doctrine/%s',
            $this->getPath(),
            'model'
        );
    }
}
