<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\OutputWorkflow\Channel\Trait;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelContext;

trait ChannelContextTrait
{
    protected ChannelContext $channelContext;

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function setChannelContext(ChannelContext $channelContext): void
    {
        $this->channelContext = $channelContext;
    }
}
