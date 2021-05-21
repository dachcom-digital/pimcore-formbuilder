<?php

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

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
}
