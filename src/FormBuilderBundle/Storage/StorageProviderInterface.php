<?php

namespace FormBuilderBundle\Storage;

use FormBuilderBundle\Model\FormStorageData;
use Symfony\Component\HttpFoundation\Request;

interface StorageProviderInterface
{
    public function store(Request $request, FormStorageData $formStorageData): string;

    public function update(Request $request, string $token, FormStorageData $formStorageData): void;

    public function flush(Request $request, string $token): void;

    public function fetch(Request $request, string $token): ?FormStorageData;
}
