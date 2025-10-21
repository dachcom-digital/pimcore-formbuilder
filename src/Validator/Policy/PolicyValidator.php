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

namespace FormBuilderBundle\Validator\Policy;

use FormBuilderBundle\Stream\Upload\UploadedFileInterface;

final class PolicyValidator
{
    public function __construct(protected ?UploadPolicyValidatorInterface $uploadPolicyValidator = null)
    {
    }

    public function validateUploadedFile(UploadedFileInterface $file, array $context = []): void
    {
        if ($this->uploadPolicyValidator instanceof UploadPolicyValidatorInterface) {
            $this->uploadPolicyValidator->validate($file, $context);
        }
    }
}
