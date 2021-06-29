<?php

namespace FormBuilderBundle\Stream;

interface FileStreamInterface
{
    public function handleUpload(array $options = [], bool $instantChunkCombining = true): array;

    public function combineChunks(array $options = []): array;

    public function handleDelete(string $identifier, bool $checkChunkFolder = false): array;
}
