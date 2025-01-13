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

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

interface FlashBagManagerInterface
{
    public function has(string $type): bool;

    public function add(string $type, mixed $message);

    public function get(string $type, array $default = []): array;

    public function flashBagIsAvailable(): bool;

    public function getFlashBag(): ?FlashBagInterface;
}
