<?php

namespace FormBuilderBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\DBAL\Types\Type;
use FormBuilderBundle\DependencyInjection\CompilerPass\ApiProviderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\ChoiceBuilderPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DataInjectionPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DispatcherPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DynamicMultiFileAdapterPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\DynamicObjectResolverPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\FieldTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\MailEditorWidgetPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OptionsTransformerPass;
use FormBuilderBundle\DependencyInjection\CompilerPass\OutputInputTransformerPass;
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
        $container->addCompilerPass(new OutputInputTransformerPass());
        $container->addCompilerPass(new OutputWorkflowChannelPass());
        $container->addCompilerPass(new OutputWorkflowFunnelLayerPass());
        $container->addCompilerPass(new OutputWorkflowFunnelActionPass());
        $container->addCompilerPass(new DynamicObjectResolverPass());
        $container->addCompilerPass(new RuntimeDataProviderPass());
        $container->addCompilerPass(new DynamicMultiFileAdapterPass());
        $container->addCompilerPass(new ApiProviderPass());
        $container->addCompilerPass(new FieldTransformerPass());
        $container->addCompilerPass(new StorageProviderPass());
        $container->addCompilerPass(new DataInjectionPass());
    }

    public function getInstaller(): Install
    {
        return $this->container->get(Install::class);
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
