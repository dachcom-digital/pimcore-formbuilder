<?php

namespace FormBuilderBundle;

use FormBuilderBundle\DependencyInjection\CompilerPass\OptionsTransformerPass;
use FormBuilderBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormBuilderBundle extends AbstractPimcoreBundle
{
    const BUNDLE_VERSION = '2.0.3';

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new OptionsTransformerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return self::BUNDLE_VERSION;
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
            '/bundles/formbuilder/js/settings.js',
            '/bundles/formbuilder/js/dataObject.js',
            '/bundles/formbuilder/js/types/keyValueRepeater.js',
            '/bundles/formbuilder/js/types/href.js',
            '/bundles/formbuilder/js/comp/importer.js',
            '/bundles/formbuilder/js/comp/form.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/builder.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/form.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/condition/abstract.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/condition/value.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/abstract.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/changeConstraints.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/toggle.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/value.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/event.js',
            '/bundles/formbuilder/js/comp/conditionalLogic/action/class.js',
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
}
