<?php

namespace FormBuilderBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\ChoiceBuilderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DispatcherPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DynamicObjectResolverPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\MailEditorWidgetPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OptionsTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputWorkflowChannelPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\RuntimeDataProviderPass;
use FormBuilderBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormBuilderBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    const PACKAGE_NAME = 'dachcom-digital/formbuilder';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->configureDoctrineExtension($container);

        $container->addCompilerPass(new OptionsTransformerPass());
        $container->addCompilerPass(new DispatcherPass());
        $container->addCompilerPass(new ChoiceBuilderPass());
        $container->addCompilerPass(new MailEditorWidgetPass());
        $container->addCompilerPass(new OutputTransformerPass());
        $container->addCompilerPass(new OutputWorkflowChannelPass());
        $container->addCompilerPass(new DynamicObjectResolverPass());
        $container->addCompilerPass(new RuntimeDataProviderPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return $this->container->get(Install::class);
    }

    /**
     * @return string[]
     */
    public function getJsPaths()
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
            '/bundles/formbuilder/js/extjs/conditional-logic/action/mailBehaviour.js',
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

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/formbuilder/css/admin.css'
        ];
    }

    /**
     * @return string[]
     */
    public function getEditmodeJsPaths()
    {
        return [
            '/bundles/formbuilder/js/admin/area.js'
        ];
    }

    /**
     * @return string[]
     */
    public function getEditmodeCssPaths()
    {
        return [
            '/bundles/formbuilder/css/admin-editmode.css',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }

    /**
     * @param ContainerBuilder $container
     */
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

    /**
     * @return string|null
     */
    protected function getNamespaceName()
    {
        return 'FormBuilderBundle\Model';
    }

    /**
     * @return string
     */
    protected function getNameSpacePath()
    {
        return sprintf(
            '%s/Resources/config/doctrine/%s',
            $this->getPath(),
            'model'
        );
    }
}
