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

namespace FormBuilderBundle\Stream;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Exception\UploadErrorException;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Stream\Upload\LocalFile;
use FormBuilderBundle\Stream\Upload\ServerFile;
use FormBuilderBundle\Validator\Policy\PolicyValidator;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Pimcore\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class FileStream implements FileStreamInterface
{
    private const STORAGE_FILE = 'file';
    private const STORAGE_CHUNK = 'chunk';

    public function __construct(
        protected Configuration $configuration,
        protected RequestStack $requestStack,
        protected FilesystemOperator $formBuilderChunkStorage,
        protected FilesystemOperator $formBuilderFilesStorage,
        protected MimeTypeGuesserInterface $mimeTypeGuesser,
        protected FormDefinitionManager $formDefinitionManager,
        protected PolicyValidator $policyValidator,
    ) {
    }

    public function handleUpload(array $options = [], bool $instantChunkCombining = true): array
    {
        $binaryIdentifier = $options['binary'];
        $fieldReferenceKey = $options['fieldReferenceKey'] ?? null;

        $mainRequest = $this->requestStack->getMainRequest();

        if (!$mainRequest instanceof Request) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'error'      => 'No request given'
            ];
        }

        $type = $mainRequest->headers->get('Content-Type');
        $fieldReference = $fieldReferenceKey !== null ? $mainRequest->request->get($fieldReferenceKey) : null;

        if (empty($type)) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'error'      => 'No files were uploaded.'
            ];
        }

        if (!str_starts_with(strtolower($type), 'multipart/')) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'error'      => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).'
            ];
        }

        $file = $mainRequest->files->get($binaryIdentifier);

        if (!$file instanceof UploadedFile) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'fileName'   => null,
                'error'      => 'no file'
            ];
        }

        $fileName = $file->getClientOriginalName();

        $serverFileSafeName = $this->getSafeFileName($fileName, true);
        $fileSafeName = $this->getSafeFileName($fileName);

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'fileName'   => $fileSafeName,
                'error'      => 'upload error'
            ];
        }

        try {
            $this->assertFieldReference($fieldReference);
        } catch (UploadErrorException $e) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'error'      => $e->getMessage()
            ];
        }

        $uploadRestrictions = $this->getUploadRestrictions($fieldReference);

        $sizeLimit = $uploadRestrictions['sizeLimit'];
        $allowedMimeTypes = $uploadRestrictions['allowedMimeTypes'];

        try {
            $uuid = $this->determinateUuid($mainRequest, $options['uuid'] ?? null);
        } catch (UploadErrorException $e) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage()
            ];
        }

        if ($fileSafeName === '') {
            return [
                'success'    => false,
                'statusCode' => 400,
                'fileName'   => $fileSafeName,
                'error'      => 'File name empty.'
            ];
        }

        try {
            $this->validateUploadedFileSize($file, $sizeLimit, $options, $mainRequest);
        } catch (UploadErrorException $e) {
            return [
                'success'      => false,
                'statusCode'   => $e->getCode(),
                'fileName'     => $fileSafeName,
                'error'        => $e->getMessage(),
                'preventRetry' => $e->getCode() !== 400
            ];
        }

        $totalParts = $mainRequest->request->has($options['totalChunkCount'])
            ? (int) $mainRequest->request->get($options['totalChunkCount'])
            : 1;

        if ($totalParts > 1) {
            // chunked upload
            $partIndex = (int) $mainRequest->request->get($options['chunkIndex']);

            try {
                $this->formBuilderChunkStorage->write(
                    sprintf('%s%s%s', $uuid, DIRECTORY_SEPARATOR, $partIndex),
                    file_get_contents($file->getPathname())
                );
            } catch (FilesystemException $e) {
                return [
                    'success'    => false,
                    'statusCode' => 400,
                    'fileName'   => $fileSafeName,
                    'error'      => $e->getMessage(),
                    'uuid'       => $uuid
                ];
            }

            if ($instantChunkCombining === true && ($partIndex + 1) === $totalParts) {
                return $this->combineChunks(array_merge($options, ['fileName' => $fileSafeName]));
            }

            return [
                'success'  => true,
                'fileName' => $fileSafeName,
                'error'    => null,
                'uuid'     => $uuid
            ];
        }

        try {
            $this->validateUploadedFileMimeType($file, $allowedMimeTypes);
        } catch (UploadErrorException $e) {
            return [
                'success'    => false,
                'statusCode' => $e->getCode(),
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage(),
            ];
        }

        try {
            $this->validateUploadPolicy($file, $fileSafeName, $mainRequest);
        } catch (UploadErrorException $e) {
            return [
                'success'    => false,
                'statusCode' => $e->getCode(),
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage(),
            ];
        }

        try {
            $this->formBuilderFilesStorage->write(
                $uuid . '/' . $serverFileSafeName,
                file_get_contents($file->getPathname())
            );
        } catch (FilesystemException $e) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage(),
                'uuid'       => $uuid
            ];
        }

        return [
            'success'  => true,
            'fileName' => $fileSafeName,
            'uuid'     => $uuid
        ];
    }

    public function combineChunks(array $options = []): array
    {
        $chunkSuccess = true;
        $mainRequest = $this->requestStack->getMainRequest();

        if (!$mainRequest instanceof Request) {
            return [
                'success'    => false,
                'statusCode' => 400,
            ];
        }

        $fileSafeName = $this->getSafeFileName($options['fileName']);

        try {
            $uuid = $this->determinateUuid($mainRequest, $options['uuid'] ?? null);
        } catch (UploadErrorException $e) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage()
            ];
        }

        $fieldReferenceKey = $options['fieldReferenceKey'] ?? null;
        $fieldReference = $fieldReferenceKey !== null ? $mainRequest->request->get($fieldReferenceKey) : null;

        try {
            $this->assertFieldReference($fieldReference);
        } catch (UploadErrorException) {
            return [
                'success'      => false,
                'statusCode'   => 400,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        try {
            $tmpStream = tmpfile();
            $chunkFiles = $this->formBuilderChunkStorage->listContents($uuid)->toArray();

            usort($chunkFiles, static function (StorageAttributes $a, StorageAttributes $b) {
                $pathInfoA = pathinfo($a->path());
                $pathInfoB = pathinfo($b->path());

                return $pathInfoA['filename'] <=> $pathInfoB['filename'];
            });

            foreach ($chunkFiles as $chunkFile) {
                $chunkPathResource = $this->formBuilderChunkStorage->readStream($chunkFile->path());
                stream_copy_to_stream($chunkPathResource, $tmpStream);
                fclose($chunkPathResource);
            }

            $this->formBuilderFilesStorage->writeStream($uuid . '/' . $fileSafeName, $tmpStream);

            // Success
            fclose($tmpStream);

        } catch (\Throwable) {
            $chunkSuccess = false;
        }

        if ($chunkSuccess === false) {

            $this->removeUploadDirectories($uuid);

            return [
                'success'      => false,
                'statusCode'   => 400,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        $this->removeUploadDirectories($uuid, [self::STORAGE_CHUNK]);

        $uploadRestrictions = $this->getUploadRestrictions($fieldReference);
        $sizeLimit = $uploadRestrictions['sizeLimit'];
        $allowedMimeTypes = $uploadRestrictions['allowedMimeTypes'];

        $filePath = sprintf('%s/%s', $uuid, $fileSafeName);

        try {
            $this->validateStorageFileSize($filePath, $sizeLimit);
        } catch (UploadErrorException $e) {

            $this->removeUploadDirectories($uuid);

            return [
                'success'      => false,
                'statusCode'   => $e->getCode(),
                'fileName'     => $fileSafeName,
                'error'        => $e->getMessage(),
                'preventRetry' => $e->getCode() !== 400
            ];
        }

        try {
            $this->validateStorageFileMimeType($filePath, $allowedMimeTypes);
        } catch (UploadErrorException $e) {

            $this->removeUploadDirectories($uuid);

            return [
                'success'    => false,
                'statusCode' => $e->getCode(),
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage(),
            ];
        }

        try {
            $this->validateUploadPolicy($filePath, $fileSafeName, $mainRequest);
        } catch (UploadErrorException $e) {

            $this->removeUploadDirectories($uuid);

            return [
                'success'    => false,
                'statusCode' => $e->getCode(),
                'fileName'   => $fileSafeName,
                'error'      => $e->getMessage(),
            ];
        }

        return [
            'success'  => true,
            'uuid'     => $uuid,
            'fileName' => $fileSafeName,
        ];
    }

    public function handleDelete(string $identifier, bool $checkChunkFolder = false, ?string $fieldReference = null): array
    {
        try {
            $this->assertFieldReference($fieldReference);
        } catch (UploadErrorException $e) {
            return [
                'success'    => false,
                'statusCode' => 400,
                'message'    => $e->getMessage(),
            ];
        }

        $storages = [self::STORAGE_FILE];

        if ($checkChunkFolder === true) {
            $storages[] = self::STORAGE_CHUNK;
        }

        $this->removeUploadDirectories($identifier, $storages);

        return [
            'success' => true,
            'uuid'    => $identifier
        ];
    }

    /**
     * @throws UploadErrorException
     */
    protected function assertFieldReference(?string $fieldReference): void
    {
        if ($this->fieldReferenceEnabled() === false) {
            return;
        }

        $formField = $this->getFieldByReference($fieldReference);

        if ($formField === null) {
            throw new UploadErrorException('Field reference is invalid');
        }
    }

    protected function getUploadRestrictions(?string $fieldReference): array
    {
        $restrictions = [
            'sizeLimit'        => null,
            'allowedMimeTypes' => [],
        ];

        if ($this->fieldReferenceEnabled() === false) {
            return $restrictions;
        }

        $formField = $this->getFieldByReference($fieldReference);
        if (!$formField instanceof FormFieldDefinitionInterface) {
            return $restrictions;
        }

        $formFieldOptions = $formField->getOptions();

        $sizeLimit = $formFieldOptions['max_file_size'] ?? null;
        $allowedMimeTypes = $formFieldOptions['allowed_extensions'] ?? [];

        if (is_numeric($sizeLimit)) {
            $sizeLimit = (int) ($sizeLimit * 1024 * 1024);
        }

        $restrictions['sizeLimit'] = $sizeLimit;
        $restrictions['allowedMimeTypes'] = count($allowedMimeTypes) > 0 ? array_map('strtolower', $allowedMimeTypes) : [];

        return $restrictions;
    }

    protected function getFieldByReference(?string $fieldReference): ?FormFieldDefinitionInterface
    {
        if ($fieldReference === null) {
            return null;
        }

        try {
            [$formId, $fieldName] = explode(':', $fieldReference);
        } catch (\Throwable) {
            return null;
        }

        $form = $this->formDefinitionManager->getById((int) $formId);

        if (!$form instanceof FormDefinitionInterface) {
            return null;
        }

        $field = $form->getField($fieldName);

        if (!$field instanceof FormFieldDefinitionInterface) {
            return null;
        }

        if ($field->getType() !== 'dynamic_multi_file') {
            return null;
        }

        return $field;
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateUploadedFileMimeType(UploadedFile $file, array $allowedMimeTypes): void
    {
        $fileMimeType = null;

        $file->getMimeType();
        $file->getClientMimeType();

        try {
            $fileMimeType = $this->mimeTypeGuesser->guessMimeType($file->getPathname());
        } catch (\Throwable) {
            // fail silently
        }

        $this->validateMimeType($fileMimeType, $allowedMimeTypes);
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateUploadedFileSize(UploadedFile $file, ?int $allowedFilesize, array $options, Request $request): void
    {
        $filesize = $file->getSize();

        $totalFileSizeKey = $options['totalFileSize'] ?? null;
        if ($totalFileSizeKey !== null && $request->request->has($totalFileSizeKey)) {
            $filesize = $request->request->get($totalFileSizeKey);
        }

        $filesize = is_numeric($filesize) ? (int) $filesize : null;

        $this->validateSize($filesize, $allowedFilesize);
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateStorageFileMimeType(string $path, array $allowedMimeTypes): void
    {
        $fileMimeType = null;

        try {
            $fileMimeType = $this->formBuilderFilesStorage->mimeType($path);
        } catch (\Throwable) {
            // fail silently
        }

        $this->validateMimeType($fileMimeType, $allowedMimeTypes);
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateStorageFileSize(string $path, ?int $allowedFilesize): void
    {
        $fileSize = null;

        try {
            $fileSize = $this->formBuilderFilesStorage->fileSize($path);
        } catch (\Throwable) {
            // fail silently
        }

        $this->validateSize($fileSize, $allowedFilesize);
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateMimeType(?string $fileMimeType, array $allowedMimeTypes): void
    {
        if ($this->serverMimeTypeValidationEnabled() === false) {
            return;
        }

        if (count($allowedMimeTypes) === 0) {
            return;
        }

        if ($fileMimeType === null) {
            return;
        }

        if (!in_array($fileMimeType, $allowedMimeTypes, true)) {
            throw new UploadErrorException(
                sprintf(
                    'File has an invalid mime type, it should be one of %s.',
                    implode(', ', $allowedMimeTypes)
                ),
                400
            );
        }
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateUploadPolicy($data, string $filename, Request $request): void
    {
        if ($data instanceof UploadedFile) {
            $file = new LocalFile($data, $filename);
        } else {
            $file = new ServerFile($this->formBuilderFilesStorage, $data, $filename);
        }

        try {
           $this->policyValidator->validateUploadedFile($file, ['request' => $request]);
        } catch (\Throwable $e) {
            throw new UploadErrorException($e->getMessage(), !empty($e->getCode()) ? $e->getCode() : 400, $e);
        }
    }

    /**
     * @throws UploadErrorException
     */
    protected function validateSize(?int $filesize, ?int $allowedFilesize): void
    {
        if ($allowedFilesize === null) {
            return;
        }

        if ($filesize === 0) {
            throw new UploadErrorException('File is empty.', 400);
        }

        if ($filesize > $allowedFilesize) {
            throw new UploadErrorException('File is too large.', 413);
        }
    }

    /**
     * @throws UploadErrorException
     */
    protected function determinateUuid(Request $request, ?string $uuidIdentifier): string
    {
        if ($uuidIdentifier === null) {
            throw new UploadErrorException('Missing uuid identifier', 400);
        }

        $uuid = $request->request->get($uuidIdentifier);

        if ($uuid === null || $uuid === '') {
            throw new UploadErrorException('Missing uuid', 400);
        }

        return preg_replace('/[^A-Za-z0-9_-]/', '-', $uuid);
    }

    protected function removeUploadDirectories(string $location, array $storages = [self::STORAGE_FILE, self::STORAGE_CHUNK]): void
    {
        try {

            if (
                in_array(self::STORAGE_CHUNK, $storages, true) &&
                $this->formBuilderChunkStorage->directoryExists($location)
            ) {
                $this->formBuilderChunkStorage->deleteDirectory($location);
            }

            if (
                in_array(self::STORAGE_FILE, $storages, true) &&
                $this->formBuilderFilesStorage->directoryExists($location)
            ) {
                $this->formBuilderFilesStorage->deleteDirectory($location);
            }

        } catch (FilesystemException) {
            // fail silently
        }
    }

    protected function toBytes(mixed $sizeStr): int|string
    {
        $val = trim($sizeStr);
        if (is_numeric($val)) {
            return $val;
        }

        $last = strtolower($sizeStr[strlen($sizeStr) - 1]);
        $val = (int) substr($val, 0, -1);

        if (!in_array($last, ['g', 'm', 'k'], true)) {
            return $val;
        }

        return $val * 1024;
    }

    protected function getSafeFileName(string $fileName, bool $strong = false): string
    {
        if ($strong === false) {
            return File::getValidFilename($fileName);
        }

        return preg_replace('/[^a-zA-Z0-9]_+/', '', str_replace('.', '_', $fileName));
    }

    protected function fieldReferenceEnabled(): bool
    {
        $security = $this->configuration->getConfig('security');

        return $security['enable_upload_field_reference'] === true;
    }

    protected function serverMimeTypeValidationEnabled(): bool
    {
        $security = $this->configuration->getConfig('security');

        return $security['enable_upload_server_mime_type_validation'] === true;
    }
}
