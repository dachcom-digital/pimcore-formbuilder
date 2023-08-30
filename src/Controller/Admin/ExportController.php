<?php

namespace FormBuilderBundle\Controller\Admin;

use Carbon\Carbon;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Tool\ImportExportProcessor;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\Tool\Email;
use FormBuilderBundle\Manager\FormDefinitionManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ExportController extends AdminAbstractController
{
    public const NO_DATA_MESSAGE = 'NO_CSV_DATA_FOUND';

    public function __construct(protected FormDefinitionManager $formDefinitionManager)
    {
    }

    public function importFormAction(Request $request, ImportExportProcessor $importExportProcessor): JsonResponse
    {
        $formId = (int) $request->request->get('formId');
        /** @var UploadedFile $file */
        $file = $request->files->get('formData');
        $data = file_get_contents($file->getPathname());
        $encoding = \Pimcore\Tool\Text::detectEncoding($data);

        if ($encoding) {
            $data = iconv($encoding, 'UTF-8', $data);
        }

        $response = [
            'success' => true,
            'formId'  => $formId,
            'message' => null,
        ];

        try {
            $importExportProcessor->processYamlToFormDefinition($formId, $data);
        } catch (\Throwable $e) {
            $response['success'] = false;
            $response['message'] = sprintf('Error while importing form definition: %s', $e->getMessage());
        }

        return new JsonResponse(json_encode($response, JSON_THROW_ON_ERROR), 200, ['Content-Type' => 'text/plain'], true);
    }

    public function exportFormAction(Request $request, ImportExportProcessor $importExportProcessor): Response
    {
        $formId = $request->get('id');

        if (!is_numeric($formId)) {
            throw new NotFoundHttpException(sprintf('form with id %d not found', $formId));
        }

        try {
            $data = $importExportProcessor->processFormDefinitionToYaml((int) $formId);
        } catch (\Throwable $e) {
            throw new UnprocessableEntityHttpException(sprintf('Error while preparing form definition for export: %s', $e->getMessage()));
        }

        $response = new Response($data);
        $exportName = 'form_export_' . $formId . '.yaml';

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $exportName
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function exportFormEmailsAction(Request $request): Response
    {
        $formId = $request->get('id', 0);
        $filter = $request->get('mailType', 'all');

        if (empty($formId)) {
            throw new NotFoundHttpException('FormBuilder: No valid Form ID for csv export given.');
        }

        $emailLogs = new Email\Log\Listing();
        $emailLogs->addConditionParam('params LIKE :form', ['form' => sprintf('%%%s%%', $this->generateFormIdQuery($formId))]);

        if ($filter !== 'all') {
            $emailLogs->addConditionParam('params LIKE :workflow', ['workflow' => sprintf('%%%s%%', $this->generateOutputWorkflowFilterQuery($formId, (int) $filter))]);
        }

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
                $data[$headerName] = $mailValue[$headerName] ?? null;
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

            if (!is_array($mailParam)) {
                continue;
            }

            $fieldType = null;
            $key = $mailParam['key'];

            if (empty($key) || in_array($key, $forbiddenKeys, true)) {
                continue;
            }

            if ($key === '_form_builder_preset') {
                $displayKeyName = 'preset';
            } elseif ($key === '_form_builder_output_workflow_name') {
                $displayKeyName = 'output_workflow_name';
            } else {
                $displayKeyName = $key;
                $formField = $formDefinition->getField($key);

                if ($formField instanceof FormFieldDefinitionInterface) {
                    $fieldType = $formField->getType();
                    if (!empty($formField->getDisplayName())) {
                        $displayKeyName = $formField->getDisplayName();
                    }
                }
            }

            if (!in_array($displayKeyName, $mailHeader, true)) {
                $mailHeader[] = $displayKeyName;
            }

            $value = null;
            if (is_array($mailParam['data']) && $mailParam['data']['type'] === 'simple') {
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

    private function generateOutputWorkflowFilterQuery(int $formId, int $outputWorkflowId): string
    {
        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return 'UNKNOWN';
        }

        if (!$formDefinition->hasOutputWorkflows()) {
            return 'UNKNOWN';
        }

        $relatedWorkflows = array_values(array_filter(
            $formDefinition->getOutputWorkflows()->toArray(),
            static function (OutputWorkflowInterface $workflow) use ($outputWorkflowId) {
                return $workflow->getId() === $outputWorkflowId;
            }
        ));

        if (count($relatedWorkflows) === 0) {
            return 'UNKNOWN';
        }

        /** @var OutputWorkflowInterface $relatedWorkflow */
        $relatedWorkflow = $relatedWorkflows[0];

        $stdClass = new \stdClass();
        $stdClass->type = 'simple';
        $stdClass->value = $relatedWorkflow->getName();

        return json_encode([
            'key'  => '_form_builder_output_workflow_name',
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

    private function cleanValue(?string $value, ?string $fieldType = null): mixed
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
