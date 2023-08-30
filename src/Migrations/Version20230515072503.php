<?php

declare(strict_types=1);

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use Pimcore\Model\DataObject;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20230515072503 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function doesSqlMigrations(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var EntityRepository $repository */
        $repository = $em->getRepository(OutputWorkflowChannel::class);

        /** @var OutputWorkflowChannel $channel */
        foreach ($repository->findAll() as $channel) {

            if ($channel->getType() !== 'object') {
                continue;
            }

            $this->write(sprintf('Migrate object output channel %d', $channel->getId()));
            $channel->setConfiguration($this->parseLegacyDynamicObjectResolver($channel->getConfiguration()));
            $em->persist($channel);
        }

        $em->flush();
    }

    public function down(Schema $schema): void
    {
        // nothing to do
    }

    protected function parseLegacyDynamicObjectResolver(array $configuration): array
    {
        if (!array_key_exists('dynamicObjectResolver', $configuration)) {
            return $configuration;
        }

        // already migrated
        if (array_key_exists('dynamicObjectResolverClass', $configuration)) {
            return $configuration;
        }

        $dynamicObjectResolverClass = null;
        if (!empty($configuration['dynamicObjectResolver'])) {
            $resolvingObject = $configuration['resolvingObject'] ?? null;
            if (is_array($resolvingObject)) {
                $object = DataObject::getById($resolvingObject['id']);
                $dynamicObjectResolverClass = $object instanceof DataObject\Concrete ? $object->getClassName() : null;
            }
        }

        if ($dynamicObjectResolverClass === null) {
            unset($configuration['dynamicObjectResolver'], $configuration['resolvingObject']);

            return $configuration;
        }

        $configuration['dynamicObjectResolverClass'] = $dynamicObjectResolverClass;
        unset($configuration['resolvingObject']);

        return $configuration;
    }
}
