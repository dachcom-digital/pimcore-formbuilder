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

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\ObjectMappingElementCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker\Validation\ValidationCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FieldCollectionWorkerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fieldCollectionClassKey', TextType::class);
        $builder->add('fieldMapping', ObjectMappingElementCollectionType::class);
        $builder->add('validationData', ValidationCollectionType::class);
    }
}
