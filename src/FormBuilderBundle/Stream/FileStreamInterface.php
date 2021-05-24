<?php

namespace FormBuilderBundle\Stream;

interface FileStreamInterface
{
    /**
     * @param array $options
     * @param bool  $instantChunkCombining
     *
     * @return array
     */
    public function handleUpload(array $options = [], bool $instantChunkCombining = true);

    /**
     * @param array $options
     *
     * @return array
     */
    public function combineChunks(array $options = []);

    /**
     * @param string $identifier
     * @param bool   $checkChunkFolder
     *
     * @return array
     */
    public function handleDelete($identifier, bool $checkChunkFolder = false);
}
