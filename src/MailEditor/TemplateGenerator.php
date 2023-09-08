<?php

namespace FormBuilderBundle\MailEditor;

class TemplateGenerator
{
    protected bool $tableStarted = false;

    public function generateWidgetFieldTemplate(array $fields): string
    {
        $template = '';

        foreach ($fields as $field) {

            $fieldType = $field['field_type'] ?? null;
            if ($fieldType === 'submit') {
                continue;
            }

            if (in_array($field['configIdentifier'], ['fb_field_container_fieldset', 'fb_field_container_repeater'], true)) {
                $template .= $this->closeTable();
                $template .= $this->generateContainerFieldTemplate($field);
            } else {
                $template .= $this->openTable();
                $template .= $this->generateFieldTemplate($field);
            }
        }

        $template .= $this->closeTable();

        return $template;
    }

    private function generateFieldTemplate($field): string
    {
        return sprintf('
            <tr>
                <td><fb-field data-render_type="L" data-type="%1$s" data-sub_type="%2$s">%3$s</fb-field></td>
                <td><fb-field data-render_type="V" data-type="%1$s" data-sub_type="%2$s">%3$s</fb-field></td>
            </tr>', $field['type'], $field['subType'], $field['label']);
    }

    private function generateContainerFieldTemplate($field): string
    {
        $containerFields = [];
        foreach ($field['children'] as $containerField) {
            $containerFields[] = $this->generateFieldTemplate($containerField);
        }

        $tableData = sprintf('<table><tbody>%s</tbody></table>', implode('', $containerFields));

        return sprintf('<fb-container-field data-type="%s" data-sub_type="%s">%s</fb-container-field>', $field['type'], $field['subType'], $tableData);

    }

    private function closeTable(): string
    {
        $template = '';
        if ($this->tableStarted === true) {
            $this->tableStarted = false;
            $template .= '</tbody></table>';
        }

        return $template;
    }

    private function openTable(): string
    {
        $template = '';

        if ($this->tableStarted === false) {
            $this->tableStarted = true;
            $template .= '<table><tbody>';
        }

        return $template;
    }

}
