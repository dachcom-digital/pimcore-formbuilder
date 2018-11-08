<?php

namespace FormBuilderBundle;

use FormBuilderBundle\DependencyInjection\CompilerPass\ChoiceBuilderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DispatcherPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OptionsTransformerPass;
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
        $container->addCompilerPass(new OptionsTransformerPass());
        $container->addCompilerPass(new DispatcherPass());
        $container->addCompilerPass(new ChoiceBuilderPass());
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
            '/bundles/formbuilder/js/comp/formFieldConstraint.js'
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
     * @inheritDoc
     */
    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
