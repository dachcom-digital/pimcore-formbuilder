<?php

namespace FormBuilderBundle\Stream;

interface FileStreamInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getInitialFiles();

    /**
     * Get the name of the uploaded file.
     *
     * @return string
     */
    public function getUploadName();

    /**
     * Get the real name of the uploaded file.
     *
     * @return bool|mixed|string
     */
    public function getRealFileName();

    /**
     * @return array
     */
    public function combineChunks();

    /**
     * @return array
     */
    public function handleUpload();

    /**
     * @param string $uuid
     *
     * @return array
     */
    public function handleDelete($uuid);
}
