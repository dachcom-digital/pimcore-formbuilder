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

use League\Flysystem\FilesystemOperator;

readonly class ServerFile implements UploadedFileInterface
{
    public function __construct(
        private FilesystemOperator $storage,
        private string $filePath,
        private string $originalName
    ) {
    }

    public function getSize(): int
    {
        return $this->storage->fileSize($this->filePath);
    }

    public function getMimeType(): ?string
    {
        return $this->storage->mimeType($this->filePath);
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getStream(): mixed
    {
        return $this->storage->readStream($this->filePath);
    }
}
