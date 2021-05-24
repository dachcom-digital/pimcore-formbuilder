<?php

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DynamicMultiFileAdapterInterface
{
    /**
     * @return string
     */
    public function getForm(): string;

    /**
     * @return string
     */
    public function getJsHandler(): string;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function onUpload(Request $request): Response;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function onDone(Request $request): Response;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function onDelete(Request $request): Response;
}
