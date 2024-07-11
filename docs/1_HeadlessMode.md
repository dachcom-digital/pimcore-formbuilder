# Headless Mode

## AreaBrick
You need to create your own editable, depending on your integration. You may want to use the `FormDialogBuilder` to simplify the area configuration section.

## Form Building
This is an example, how you may want to build FormBuilder forms in headless mode:

```php
$optionBuilder = new FormOptionsResolver();
$optionBuilder->setFormId($formId);
$optionBuilder->setFormTemplate($formTemplate);
$optionBuilder->setFormPreset($formPreset);
$optionBuilder->setOutputWorkflow($formOutputWorkflow);

$form = $this->formAssembler->assembleHeadlessForm($optionBuilder);

 $form->submit($request->request->all());

if (!$form->isValid()) {

    // process validation errors here
    
    return;
}

$response = null;
$submissionEvent = new SubmissionEvent($request, $formRuntimeData, $form, null, false);
$this->eventDispatcher->dispatch($submissionEvent, FormBuilderEvents::FORM_SUBMIT_SUCCESS);

$finishResponse = $this->formSubmissionFinisher->finishWithSuccess($request, $submissionEvent);

if ($finishResponse instanceof RedirectResponse) {
    return [
        'success'  => true,
        'redirect' => $finishResponse->getTargetUrl()
    ];
}

if ($finishResponse instanceof JsonResponse) {
    $response = json_decode($finishResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
}

if ($response === null) {
    throw new \InvalidArgumentException('empty success response');
}

if ($response['success'] === false) {

    if (!array_key_exists('validation_errors', $response)) {
        return $response;
    }

    $validationErrors = $response['validation_errors']
    // process validation errors here
    
    return null;
}

return $response;
```