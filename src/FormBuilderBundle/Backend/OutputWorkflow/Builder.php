<?php

namespace FormBuilderBundle\Backend\OutputWorkflow;

use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Pimcore\Translation\Translator;
use Symfony\Component\Serializer\SerializerInterface;

class Builder
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var OutputWorkflowChannelRegistry
     */
    protected $outputWorkflowChannelRegistry;

    /**
     * @param SerializerInterface           $serializer
     * @param Translator                    $translator
     * @param OutputWorkflowChannelRegistry $outputWorkflowChannelRegistry
     */
    public function __construct(
        SerializerInterface $serializer,
        Translator $translator,
        OutputWorkflowChannelRegistry $outputWorkflowChannelRegistry
    ) {
        $this->serializer = $serializer;
        $this->translator = $translator;
        $this->outputWorkflowChannelRegistry = $outputWorkflowChannelRegistry;
    }

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     *
     * @return array
     *
     * @throws \Exception
     */
    public function generateExtJsForm(OutputWorkflowInterface $outputWorkflow)
    {
        $data = [
            'id'   => $outputWorkflow->getId(),
            'name' => $outputWorkflow->getName(),
            'meta' => []
        ];

        $data['output_workflow_channels'] = $this->serializer->normalize($outputWorkflow->getChannels(), 'array', ['groups' => ['ExtJs']]);
        $data['output_workflow_channels_store'] = $this->generateAvailableWorkflowChannelsList();
        $data['output_workflow_success_management'] = $outputWorkflow->getSuccessManagement();

        return $data;
    }

    /**
     * @return array
     */
    public function generateAvailableWorkflowChannelsList()
    {
        $data = [];
        foreach ($this->outputWorkflowChannelRegistry->getAllIdentifier() as $availableChannel) {
            $data[] = [
                'identifier' => $availableChannel,
                'label'      => $this->translate(sprintf('form_builder.output_workflow.channel.%s', strtolower($availableChannel))),
                'icon_class' => sprintf('form_builder_output_workflow_channel_%s', strtolower($availableChannel))
            ];
        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function translate($value)
    {
        if (empty($value)) {
            return $value;
        }

        return $this->translator->trans($value, [], 'admin');
    }
}
