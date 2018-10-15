<?php

namespace DachcomBundle\Test\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeServicesPublicPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $prefix = getenv('DACHCOM_BUNDLE_NAME');
        $serviceIds = array_filter($container->getServiceIds(), function (string $id) use ($prefix) {
            return strpos($id, $prefix) === 0;
        });

        foreach ($serviceIds as $serviceId) {
            if ($container->hasAlias($serviceId)) {
                $container->getAlias($serviceId)->setPublic(true);
            }

            $container
                ->findDefinition($serviceId)
                ->setPublic(true);
        }
    }
}
