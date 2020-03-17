<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\Collection;

interface OutputWorkflowInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param array $successManagement
     */
    public function setSuccessManagement(array $successManagement);

    /**
     * @return array
     */
    public function getSuccessManagement();

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form);

    /**
     * @return FormInterface
     */
    public function getForm();

    /**
     * @return bool
     */
    public function hasChannels();

    /**
     * @param OutputWorkflowChannelInterface $channel
     *
     * @return bool
     */
    public function hasChannel(OutputWorkflowChannelInterface $channel);

    /**
     * @param OutputWorkflowChannelInterface $channel
     */
    public function addChannel(OutputWorkflowChannelInterface $channel);

    /**
     * @param OutputWorkflowChannelInterface $channel
     */
    public function removeChannel(OutputWorkflowChannelInterface $channel);

    /**
     * @return Collection|OutputWorkflowChannelInterface[]
     */
    public function getChannels();
}
