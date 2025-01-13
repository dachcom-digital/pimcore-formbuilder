# Channel Context

The `ChannelContext` object stores data within a full workflow livecycle.
If you need to pass data from one channel to another, this object comes in handy!

All core Channels (Email, Object, API) receives the `FormBuilderBundle\OutputWorkflow\Channel\ChannelContext` object

## Channel Context and Signal Storage
The Channel Context is also available in Signal Storage (`OutputWorkflowSignalsEvent->getContextItem('channelContext)`). 
This can be helpful, if you need to clean up data after an exception occurs or the workflow has been completely processed.

## Channel Context in Custom Channel
To receive the channel context in a custom channel, you need to implement the `ChannelContextAwareInterface`:

> [!IMPORTANT]  
> If your channel also dispatches a `ChannelSubjectGuardEvent`, 
> don't forget to pass the channel context to it to pass the context to upcoming channels!

If your channel implements the `ChannelContextAwareInterface`, the `OutputWorkflowDispatcher` automatically will inject the active `ChannelContext` object.

```php
<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelContextAwareInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Trait\ChannelContextTrait;

class EmailOutputChannel implements ChannelInterface, ChannelContextAwareInterface
{
    use ChannelContextTrait;
}
```