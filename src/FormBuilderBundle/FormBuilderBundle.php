<?php

namespace FormBuilderBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\ChoiceBuilderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DispatcherPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\MailEditorWidgetPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OptionsTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputTransformerPass;
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
            '/bundles/formbuilder/js/plugin.js',
            '/bundles/formbuilder/js/resource/Formbuilder.HrefTextField.js',
            '/bundles/formbuilder/js/settings.js',
            '/bundles/formbuilder/js/dataObject.js',
            '/bundles/formbuilder/js/types/keyValueRepeater.js',
            '/bundles/formbuilder/js/types/localizedField.js',
            '/bundles/formbuilder/js/types/href.js',
            '/bundles/formbuilder/js/comp/importer.js',
            '/bundles/formbuilder/js/comp/form.js',
            '/bundles/formbuilder/js/comp/extensions/formMetaData.js',
            '/bundles/formbuilder/js/comp/extensions/formMailEditor.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/builder.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/form.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/condition/abstract.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/condition/elementValue.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/abstract.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/constraintsAdd.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/constraintsRemove.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/toggleElement.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/changeValue.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/triggerEvent.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/toggleClass.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/toggleAvailability.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/mailBehaviour.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/successMessage.js',
            '/bundles/formbuilder/js/comp/formTypeBuilder.js',
            '/bundles/formbuilder/js/comp/formFieldConstraint.js',
            '/bundles/formbuilder/js/comp/formFieldContainer.js',
            '/bundles/formbuilder/js/comp/config_fields/abstract.js',
            '/bundles/formbuilder/js/comp/config_fields/checkbox.js',
            '/bundles/formbuilder/js/comp/config_fields/href.js',
            '/bundles/formbuilder/js/comp/config_fields/key_value_repeater.js',
            '/bundles/formbuilder/js/comp/config_fields/label.js',
            '/bundles/formbuilder/js/comp/config_fields/numberfield.js',
            '/bundles/formbuilder/js/comp/config_fields/options_repeater.js',
            '/bundles/formbuilder/js/comp/config_fields/select.js',
            '/bundles/formbuilder/js/comp/config_fields/tagfield.js',
            '/bundles/formbuilder/js/comp/config_fields/textfield.js',
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
