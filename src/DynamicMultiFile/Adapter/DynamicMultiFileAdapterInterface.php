<?php

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DynamicMultiFileAdapterInterface
{
    public function getForm(): string;

    public function getJsHandler(): string;

    public function onUpload(Request $request): Response;

    public function onDone(Request $request): Response;

    public function onDelete(Request $request): Response;
}
