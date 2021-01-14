<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\Collection;
use FormBuilderBundle\Model\Fragment\SubFieldsAwareInterface;

interface FormDefinitionInterface extends SubFieldsAwareInterface
{
    const ALLOWED_FORM_KEYS = [
        'action',
        'method',
        'enctype',
        'noValidate',
        'useAjax',
        'attributes'
    ];

    /**
     * @return null|int
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
     * @param string $groupName
     */
    public function setGroup(string $groupName);

    /**
     * @return null|string
     */
    public function getGroup();

    /**
     * @param \DateTime $date
     */
    public function setCreationDate(\DateTime $date);

    /**
     * @return \DateTime
     */
    public function getCreationDate();

    /**
     * @param \DateTime $date
     */
    public function setModificationDate(\DateTime $date);

    /**
     * @return \DateTime
     */
    public function getModificationDate();

    /**
     * @param int $userId
     */
    public function setModifiedBy(int $userId);

    /**
     * @return int
     */
    public function getModifiedBy();

    /**
     * @param int $userId
     */
    public function setCreatedBy(int $userId);

    /**
     * @return int
     */
    public function getCreatedBy();

    /**
     * @param array $mailLayout
     */
    public function setMailLayout($mailLayout = null);

    /**
     * @return null|array
     */
    public function getMailLayout();

    /**
     * @return bool
     */
    public function hasOutputWorkflows();

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     *
     * @return bool
     */
    public function hasOutputWorkflow(OutputWorkflowInterface $outputWorkflow);

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     */
    public function addOutputWorkflow(OutputWorkflowInterface $outputWorkflow);

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     */
    public function removeOutputWorkflow(OutputWorkflowInterface $outputWorkflow);

    /**
     * @return Collection|OutputWorkflowInterface[]
     */
    public function getOutputWorkflows();

    /**
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param array $data
     */
    public function setConditionalLogic(array $data);

    /**
     * @return array
     */
    public function getConditionalLogic();

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $optional
     *
     * @throws \Exception
     */
    public function addDynamicField(string $name, string $type, array $options = [], array $optional = []);

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function removeDynamicField(string $name);

    /**
     * @param string $name
     * @param bool   $deep
     *
     * @return null|FormFieldDefinitionInterface
     */
    public function getField(string $name, bool $deep = false);

    /**
     * @param string $name
     * @param bool   $deep
     *
     * @return null|string
     */
    public function getFieldType(string $name, bool $deep = false);

    /**
     * @param string $name
     *
     * @return null|FormFieldContainerDefinitionInterface
     *
     * @throws \Exception
     *
     * @internal
     */
    public function getFieldContainer(string $name);

    /**
     * @param string $type
     *
     * @return FormFieldDefinitionInterface[]
     *
     * @internal
     */
    public function getFieldsByType(string $type);
}
