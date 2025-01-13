<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionInterface;

class FunnelActionRegistry
{
    protected array $funnelActions = [];

    public function register(string $identifier, mixed $service): void
    {
        if (isset($this->funnelActions[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Funnel action with identifier "%s" already exists', $identifier));
        }

        if (!in_array(FunnelActionInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    FunnelActionInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->funnelActions[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->funnelActions[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): FunnelActionInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" funnel action does not exist.');
        }

        return $this->funnelActions[$identifier];
    }

    public function getAll(): array
    {
        return $this->funnelActions;
    }

    public function getAllIdentifier(): array
    {
        return array_keys($this->funnelActions);
    }
}
