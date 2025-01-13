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

namespace FormBuilderBundle\Storage;

use FormBuilderBundle\Model\FormStorageData;
use Symfony\Component\HttpFoundation\Request;

interface StorageProviderInterface
{
    public function store(Request $request, FormStorageData $formStorageData): string;

    public function update(Request $request, string $token, FormStorageData $formStorageData): void;

    public function flush(Request $request, string $token): void;

    public function fetch(Request $request, string $token): ?FormStorageData;
}
