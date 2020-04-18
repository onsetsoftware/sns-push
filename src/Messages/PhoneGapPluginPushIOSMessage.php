<?php

declare(strict_types=1);

namespace SNSPush\Messages;

use function array_merge;

class PhoneGapPluginPushIOSMessage extends IOsMessage
{
    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->filterBlank(
            array_merge($this->coreDataArray(), ['payload' => $this->getPayload()])
        );
    }
}
