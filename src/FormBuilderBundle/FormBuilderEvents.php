<?php

namespace FormBuilderBundle;

final class FormBuilderEvents
{
    /**
     * The FORM_PRE_SET_DATA event is dispatched at the beginning of the Form::setData() method.
     * It contains the form event and also some form builder settings.
     *
     * @see \FormBuilderBundle\Event\Form\PreSetDataEvent
     *
     * https://symfony.com/doc/current/form/events.html#a-the-formevents-pre-set-data-event
     */
    public const FORM_PRE_SET_DATA = 'form_builder.pre_set_data';

    /**
     * The FORM_POST_SET_DATA event is dispatched at the end of the Form::setData() method.
     * This event is mostly here for reading data after having pre-populated the form.
     * It contains the form event and also some form builder settings.
     *
     * @see \FormBuilderBundle\Event\Form\PostSetDataEvent
     *
     * http://symfony.com/doc/current/form/events.html#b-the-formevents-post-set-data-event
     */
    public const FORM_POST_SET_DATA = 'form_builder.post_set_data';

    /**
     * The FORM_PRE_SUBMIT event is dispatched at the end of the Form::setData() method.
     * This event is mostly here for reading data after having pre-populated the form.
     * It contains the form event and also some form builder settings.
     *
     * @see \FormBuilderBundle\Event\Form\PreSubmitEvent
     *
     * https://symfony.com/doc/current/form/events.html#a-the-formevents-pre-submit-event
     */
    public const FORM_PRE_SUBMIT = 'form_builder.pre_submit';

    /**
     * The FORM_SUBMIT_SUCCESS event occurs when a frontend form submission was successful.
     */
    public const FORM_SUBMIT_SUCCESS = 'form_builder.submit.success';

    /**
     * The FORM_MAIL_PRE_SUBMIT event occurs before sending an email.
     *
     * @deprecated
     */
    public const FORM_MAIL_PRE_SUBMIT = 'form_builder.mail.pre_submit';

    /**
     * @see \FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent
     */
    public const OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH = 'form_builder.output_workflow.guard.subject.pre_dispatch';

    /**
     * @see \FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalsEvent
     */
    public const OUTPUT_WORKFLOW_SIGNALS = 'form_builder.output_workflow.signals';
}
