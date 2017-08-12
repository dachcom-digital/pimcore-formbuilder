<?php

namespace FormBuilderBundle;

use FormBuilderBundle\DependencyInjection\CompilerPass\FormTypePass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormBuilderBundle extends AbstractPimcoreBundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FormTypePass());
    }

    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return $this->container->get('form_builder.tool.installer');
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
            '/bundles/formbuilder/js/comp/importer.js',
            '/bundles/formbuilder/js/comp/form.js',
            '/bundles/formbuilder/js/comp/formTypeBuilder.js'
        ];
    }

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
