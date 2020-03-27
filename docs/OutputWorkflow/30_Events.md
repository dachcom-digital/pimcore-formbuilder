# Events

```yaml

services:
    AppBundle\FormBuilder\OutputWorkflowEventListener:
        tags:
            - { name: kernel.event_subscriber }
```

```php
<?php

namespace AppBundle\FormBuilder;

use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OutputWorkflowEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH  => 'checkSubject',
        ];
    }

    public function checkSubject(ChannelSubjectGuardEvent $event)
    {
        // this could be a data object but also a field collection
        $subject = $event->getSubject();

        if($event->getWorkflowName() === 'my_weird_workflow') {
            $event->shouldFail('My invalid message for a specific channel! Allow further channels to pass!', true);
            return;
        }
    
        if($event->getWorkflowName() === 'my_second_evil_workflow') {
            $event->shouldFail('My invalid message! If this happens, no further channel will be executed!', false);
            return;
        }

        if($event->getChannelType() === 'object') {
            // silently skip channel
            $event->shouldSuspend();
            return;
        }
    }
}
```