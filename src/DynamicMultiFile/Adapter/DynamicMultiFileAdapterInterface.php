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

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DynamicMultiFileAdapterInterface
{
    public function getForm(): string;

    public function getJsHandler(): string;

    public function onUpload(Request $request): Response;

    public function onDone(Request $request): Response;

    public function onDelete(Request $request): Response;
}
