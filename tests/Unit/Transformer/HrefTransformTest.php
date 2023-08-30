<?php

namespace DachcomBundle\Test\Unit\Transformer;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use FormBuilderBundle\Transformer\HrefTransformer;
use Pimcore\Tests\Util\TestHelper;

class HrefTransformTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var HrefTransformer $hrefTransformer */
        $hrefTransformer = $optionsTransformerRegistry->get(HrefTransformer::class);

        $document1 = TestHelper::createEmptyDocumentPage();
        $document2 = TestHelper::createEmptyDocumentPage();

        $data = [
            'en' => [
                'id'      => $document1->getId(),
                'type'    => 'document',
                'subtype' => $document1->getType(),
                'path'    => $document1->getFullPath()
            ],
            'de' => [
                'id'      => $document2->getId(),
                'type'    => 'document',
                'subtype' => $document2->getType(),
                'path'    => $document2->getFullPath()
            ]
        ];

        $expectedTransformedValues = [
            'en' => [
                'id'   => $document1->getId(),
                'type' => 'document'
            ],
            'de' => [
                'id'   => $document2->getId(),
                'type' => 'document'
            ]
        ];

        $transformedValues = $hrefTransformer->transform($data);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testReverseTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var HrefTransformer $hrefTransformer */
        $hrefTransformer = $optionsTransformerRegistry->get(HrefTransformer::class);

        $document1 = TestHelper::createEmptyDocumentPage();
        $document2 = TestHelper::createEmptyDocumentPage();

        $data = [
            'en' => [
                'id'   => $document1->getId(),
                'type' => 'document'
            ],
            'de' => [
                'id'   => $document2->getId(),
                'type' => 'document'
            ]
        ];

        $expectedTransformedValues = [
            'en' => [
                'id'      => $document1->getId(),
                'type'    => 'document',
                'subtype' => $document1->getType(),
                'path'    => $document1->getFullPath()
            ],
            'de' => [
                'id'      => $document2->getId(),
                'type'    => 'document',
                'subtype' => $document2->getType(),
                'path'    => $document2->getFullPath()
            ]
        ];

        $transformedValues = $hrefTransformer->reverseTransform($data);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }
}
