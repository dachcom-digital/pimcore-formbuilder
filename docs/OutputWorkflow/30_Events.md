# Events

```yaml

services:
    App\FormBuilder\OutputWorkflowEventListener:
        tags:
            - { name: kernel.event_subscriber }
```

```php
<?php

namespace App\FormBuilder;

use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OutputWorkflowEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH  => ['addChannelContextData', 'checkSubject'],
        ];
    }

    public function addChannelContextData(ChannelSubjectGuardEvent $event): void
    {
        // this could be a data object but also a field collection
        $subject = $event->getSubject();

        if (
            // if it's a special channel, add some context data to fetch it again in the next channel!
            $event->getWorkflowName() === 'my_workflow' && 
            $event->getChannelType() === 'mail' && 
            $event->hasChannelContext()
        ) {
            $event
                ->getChannelContext()
                ->addContextData('special_key', 'my_special_data');
        }
    }
    
    public function checkSubject(ChannelSubjectGuardEvent $event): void
    {
        // this could be a data object but also a field collection
        $subject = $event->getSubject();

        if ($event->getWorkflowName() === 'my_workflow') {
            $event->shouldFail('My invalid message for a specific channel! Allow further channels to pass!', true);
            
            return;
        }
    
        if ($event->getWorkflowName() === 'my_second_workflow') {
            $event->shouldFail('My invalid message! If this happens, no further channel will be executed!', false);
            
            return;
        }

        if ($event->getChannelType() === 'object') {
            // silently skip channel
            $event->shouldSuspend();
            
            return;
        }
    }
}
```