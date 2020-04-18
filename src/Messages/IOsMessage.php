<?php

declare(strict_types=1);

namespace SNSPush\Messages;

use SNSPush\Exceptions\SNSPushException;
use function array_merge;

class IOsMessage extends Message
{
    protected $platformKey = 'APNS';

    public function devMode(): void
    {
        $this->platformKey = 'APNS_SANDBOX';
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        if (array_key_exists('aps', $this->getPayload())) {
            throw new SNSPushException('Your payload data cannot include an entry with the key "aps"');
        }

        return $this->filterBlank(
            array_merge($this->coreDataArray(), $this->getPayload())
        );
    }

    protected function coreDataArray(): array
    {
        return [
            'aps' => [
                'alert' => [
                    'title' => $this->getTitle(),
                    'body' => $this->getBody(),
                ],
                'sound' => $this->getSound(),
                'badge' => $this->getCount(),
                'content-available' => $this->getContentAvailable() ? 1 : null,
            ],
        ];
    }
}
