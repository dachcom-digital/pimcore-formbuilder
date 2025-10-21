<?php

declare(strict_types=1);

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

namespace FormBuilderBundle\Stream\Upload;

interface UploadedFileInterface
{
    public function getSize(): int;

    public function getMimeType(): ?string;

    public function getOriginalName(): string;

    /**
     * @return resource
     */
    public function getStream(): mixed;
}
