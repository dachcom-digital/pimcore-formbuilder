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

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Stream\File;

interface FormDataInterface
{
    public function getFormDefinition(): FormDefinitionInterface;

    public function getData(): array;

    public function getFieldValue(string $name): mixed;

    public function setFieldValue(string $name, mixed $value);

    public function hasAttachments(): bool;

    /**
     * @return array<int, File>
     */
    public function getAttachments(): array;

    public function addAttachment(File $attachmentFile): void;
}
