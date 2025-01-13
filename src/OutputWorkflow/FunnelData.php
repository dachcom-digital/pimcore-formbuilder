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

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Model\FormStorageData;
use Symfony\Component\HttpFoundation\Request;

class FunnelData
{
    public function __construct(
        protected Request $request,
        protected FormStorageData $formStorageData,
        protected string $funnelId,
        protected string $channelId,
        protected string $storageToken
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFormStorageData(): FormStorageData
    {
        return $this->formStorageData;
    }

    public function getFunnelId(): string
    {
        return $this->funnelId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function getStorageToken(): string
    {
        return $this->storageToken;
    }
}
