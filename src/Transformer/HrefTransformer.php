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

namespace FormBuilderBundle\Transformer;

use Pimcore\Model\Element\Service;

class HrefTransformer implements OptionsTransformerInterface
{
    public function transform(mixed $values, $optionConfig = null): array
    {
        $transformedValues = [];
        foreach ($values as $locale => $value) {
            $transformedValues[$locale] = [
                'id'   => $value['id'] ?? null,
                'type' => $value['type'] ?? null,
            ];
        }

        return $transformedValues;
    }

    public function reverseTransform(mixed $values, $optionConfig = null): array
    {
        $optionValues = [];
        foreach ($values as $locale => $value) {
            $optionValues[$locale] = [];

            $type = $value['type'];
            $id = $value['id'];

            if (empty($id) || !in_array($type, ['object', 'asset', 'document'])) {
                continue;
            }

            if (is_numeric($id)) {
                $element = Service::getElementById($type, (int) $id);
            } else {
                // legacy
                $element = Service::getElementByPath($type, $id);
            }

            if ($element) {
                $optionValues[$locale] = [
                    'id'      => $element->getId(),
                    'type'    => $type,
                    'subtype' => $element->getType(),
                    'path'    => $element->getFullPath()
                ];
            }
        }

        return $optionValues;
    }
}
