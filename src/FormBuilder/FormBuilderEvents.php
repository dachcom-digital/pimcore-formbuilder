<?php

namespace FormBuilderBundle;

final class FormBuilderEvents
{
    /**
     * The FORM_SUBMIT_SUCCESS event occurs when a frontend form submission was successful.
     *
     */
    const FORM_SUBMIT_SUCCESS = 'form_builder.submit.success';

    /**
     *  The FORM_MAIL_PRE_SUBMIT event occurs before sending an email
     */
    const FORM_MAIL_PRE_SUBMIT = 'form_builder.mail.preSubmit';

}