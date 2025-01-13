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

namespace FormBuilderBundle\Session;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class FlashBagManager implements FlashBagManagerInterface
{
    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function has(string $type): bool
    {
        if (!$this->flashBagIsAvailable()) {
            return false;
        }

        return $this->getFlashBag()->has($type);
    }

    public function add(string $type, mixed $message): void
    {
        if (!$this->flashBagIsAvailable()) {
            return;
        }

        $this->getFlashBag()->add($type, $message);
    }

    public function get(string $type, array $default = []): array
    {
        if (!$this->flashBagIsAvailable()) {
            return [];
        }

        return $this->getFlashBag()->get($type, $default);
    }

    public function flashBagIsAvailable(): bool
    {
        try {
            $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return false;
        }

        return true;
    }

    public function getFlashBag(): ?FlashBagInterface
    {
        if (!$this->flashBagIsAvailable()) {
            return null;
        }

        /** @var Session $session */
        $session = $this->requestStack->getSession();

        return $session->getFlashBag();
    }
}
