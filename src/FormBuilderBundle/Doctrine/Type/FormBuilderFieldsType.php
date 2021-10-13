<?php declare(strict_types=1);

namespace FormBuilderBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;
use FormBuilderBundle\Model\Fragment\SubFieldsAwareInterface;

class FormBuilderFieldsType extends Type
{
    public const FORM_BUILDER_FIELDS = 'form_builder_fields';

    protected FormDefinitionFactoryInterface $formDefinitionFactory;

    public function setFormDefinitionFactory(FormDefinitionFactoryInterface $formDefinitionFactory): void
    {
        $this->formDefinitionFactory = $formDefinitionFactory;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $formFields = [];

        if (!is_array($value)) {
            return null;
        }

        foreach ($value as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $formFields[] = $field->toArray();
            }
        }

        return serialize($formFields);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if ($value === null) {
            return [];
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        set_error_handler(function (int $code, string $message): bool {
            throw ConversionException::conversionFailedUnserialization($this->getName(), $message);
        });

        try {
            $fields = unserialize($value, ['allowed_classes' => false]);
        } finally {
            restore_error_handler();
        }

        if (!is_array($fields)) {
            return [];
        }

        $data = [];
        foreach ($fields as $field) {
            if ($field['type'] === 'container') {
                $formField = $this->formDefinitionFactory->createFormFieldContainerDefinition();
                $this->populateFormField($formField, $field);
                if ($formField instanceof SubFieldsAwareInterface && isset($field['fields']) && is_array($field['fields'])) {
                    $subFields = [];
                    foreach ($field['fields'] as $subField) {
                        $subFormField = $this->formDefinitionFactory->createFormFieldDefinition();
                        $subFields[] = $this->populateFormField($subFormField, $subField);
                    }
                    $formField->setFields($subFields);
                }
            } else {
                $formField = $this->formDefinitionFactory->createFormFieldDefinition();
                $this->populateFormField($formField, $field);
            }

            $data[$field['name']] = $formField;
        }

        return $data;
    }

    public function getName(): string
    {
        return self::FORM_BUILDER_FIELDS;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    protected function populateFormField($formField, array $field): FieldDefinitionInterface
    {
        foreach ($field as $fieldName => $fieldValue) {
            $setter = 'set' . $this->camelize($fieldName);
            if (!is_callable([$formField, $setter])) {
                continue;
            }
            $formField->$setter($fieldValue);
        }

        return $formField;
    }

    protected function camelize(string $input, string $separator = '_'): string
    {
        return ucfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

}