<?php

namespace FormBuilderBundle\Controller\Admin;

use Carbon\Carbon;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use Pimcore\Model\Tool\Email;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use FormBuilderBundle\Manager\FormDefinitionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExportController extends AdminController
{
    public const NO_DATA_MESSAGE = 'NO_CSV_DATA_FOUND';

    protected FormDefinitionManager $formDefinitionManager;

    public function __construct(FormDefinitionManager $formDefinitionManager)
    {
        $this->formDefinitionManager = $formDefinitionManager;
    }

    public function exportFormEmailsAction(Request $request): Response
    {
        $formId = $request->get('id', 0);
        $mailType = $request->get('mailType', 'all');

        if (empty($formId)) {
            throw new NotFoundHttpException('FormBuilder: No valid Form ID for csv export given.');
        }

        $emailLogs = new Email\Log\Listing();
        $emailLogs->addConditionParam('params LIKE \'%' . $this->generateFormIdQuery($formId) . '%\'');

        if ($mailType !== 'all') {
            $emailLogs->addConditionParam('params LIKE \'%' . $this->generateFormTypeQuery($mailType) . '%\'');
        }

        $this->buildCsv($emailLogs->load(), $formId);

        $response = new Response();
        $response->headers->set('Content-Encoding', 'none');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('form_builder_export_%s.csv', $formId)
        ));

        $content = $this->buildCsv($emailLogs->load(), $formId);
        $response->setContent($content);

        return $response;
    }

    private function buildCsv(array $mailData, int $formId): string
    {
        $mailHeader = [
            'form_id',
            'log_id',
            'email_path',
            'email_id',
            'preset',
            'is_copy',
            'output_workflow_name',
            'to',
            'cc',
            'bcc',
            'sent_date',
            'subject'
        ];

        $normalizedMailData = [];
        $rows = [];

        $formDefinition = $this->formDefinitionManager->getById($formId);
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return self::NO_DATA_MESSAGE;
        }

        /** @var Email\Log $log */
        foreach ($mailData as $log) {
            $mailPath = null;
            $mailParams = $this->extractMailParams($log, $formDefinition, $mailHeader);

            try {
                $mailDocument = \Pimcore\Model\Document\Email::getById($log->getDocumentId());
            } catch (\Exception $e) {
                $mailDocument = null;
            }

            if ($mailDocument instanceof \Pimcore\Model\Document\Email) {
                $mailPath = $mailDocument->getFullPath();
            }

            $date = Carbon::createFromTimestamp($log->getSentDate());
            $mailSystemParams = [
                'form_id'    => $formId,
                'log_id'     => $log->getId(),
                'email_path' => $mailPath,
                'email_id'   => $log->getDocumentId(),
                'to'         => $log->getTo(),
                'cc'         => $log->getCc(),
                'bcc'        => $log->getBcc(),
                'sent_date'  => $date->toDateTimeString(),
                'subject'    => $log->getSubject(),
            ];

            if (!empty($mailParams)) {
                $normalizedMailData[] = array_merge($mailSystemParams, $mailParams);
            } else {
                $normalizedMailData[] = $mailSystemParams;
            }
        }

        // pre-fill every row with same data structure (no data = null)
        foreach ($normalizedMailData as $mailValue) {
            $data = [];
            foreach ($mailHeader as $headerName) {
                $data[$headerName] = isset($mailValue[$headerName]) ? $mailValue[$headerName] : null;
            }

            $rows[] = $data;
        }

        if (empty($rows)) {
            return self::NO_DATA_MESSAGE;
        }

        $header = array_keys($rows[0]);

        return $this->generateCsvStructure($header, $rows);
    }

    private function extractMailParams(Email\Log $log, FormDefinitionInterface $formDefinition, array &$mailHeader): array
    {
        $normalizedParams = [];
        $forbiddenKeys = ['body', '_form_builder_id'];

        try {
            $mailParams = json_decode($log->getParams(), true);
        } catch (\Exception $e) {
            return $normalizedParams;
        }

        if (empty($mailParams)) {
            return $normalizedParams;
        }

        foreach ($mailParams as $mailParam) {
            if (!$mailParam instanceof \stdClass) {
                continue;
            }

            $key = $mailParam['key'];
            if (empty($key) || in_array($key, $forbiddenKeys, true)) {
                continue;
            }

            if ($key === '_form_builder_preset') {
                $key = 'preset';
            } elseif ($key === '_form_builder_is_copy') {
                $key = 'is_copy';
            } elseif ($key === '_form_builder_output_workflow_name') {
                $key = 'output_workflow_name';
            }

            $formField = $formDefinition->getField($key);

            $displayKeyName = $key;
            $fieldType = null;
            if ($formField instanceof FormFieldDefinitionInterface) {
                $fieldType = $formField->getType();
                if (!empty($formField->getDisplayName())) {
                    $displayKeyName = $formField->getDisplayName();
                }
            }

            if (!in_array($displayKeyName, $mailHeader, true)) {
                $mailHeader[] = $displayKeyName;
            }

            $value = null;
            if ($mailParam['data'] instanceof \stdClass && $mailParam['data']['type'] === 'simple') {
                $value = $this->cleanValue($mailParam['data']['value'], $fieldType);
            }

            $normalizedParams[$displayKeyName] = $value;
        }

        return $normalizedParams;
    }

    private function generateFormIdQuery(int $formId): string
    {
        $stdClass = new \stdClass();
        $stdClass->type = 'simple';
        $stdClass->value = (int) $formId;

        return json_encode([
            'key'  => '_form_builder_id',
            'data' => $stdClass
        ], JSON_THROW_ON_ERROR);
    }

    private function generateFormTypeQuery(string $mailType): string
    {
        $stdClass = new \stdClass();
        $stdClass->type = 'simple';
        $stdClass->value = $mailType === 'only_main' ? 0 : 1;

        return json_encode([
            'key'  => '_form_builder_is_copy',
            'data' => $stdClass
        ], JSON_THROW_ON_ERROR);
    }

    private function generateCsvStructure(array $header, array $data): string
    {
        $handle = fopen('php://temp', 'r+');
        if (!is_resource($handle)) {
            return '';
        }

        fputcsv($handle, $header, ',', '"');

        foreach ($data as $line) {
            fputcsv($handle, array_values($line), ',', '"');
        }

        rewind($handle);

        $contents = '';
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }

        fclose($handle);

        return $contents;
    }

    private function cleanValue(string $value, ?string $fieldType = null): mixed
    {
        if (in_array($fieldType, ['choice', 'dynamic_choice', 'country'])) {
            $value = preg_split('/(<br>|<br \/>)/', $value);
            $value = is_array($value) ? implode(', ', array_filter($value)) : $value;
        } elseif ($fieldType === 'textarea') {
            $value = preg_split('/(<br>|<br \/>)/', $value);
            $value = is_array($value) ? implode("\n", array_filter($value)) : $value;
            $value = preg_replace("/[\r\n]+/", "\n", $value);
        }

        return $value;
    }
}
