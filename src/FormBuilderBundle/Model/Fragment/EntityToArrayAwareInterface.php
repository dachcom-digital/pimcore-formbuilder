<?php

namespace FormBuilderBundle\Model\Fragment;

interface EntityToArrayAwareInterface
{
    /**
     * @return array
     */
    public function toArray();
}
