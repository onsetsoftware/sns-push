<?php

declare(strict_types=1);

namespace SNSPush\Messages;

use SNSPush\Exceptions\InvalidTypeException;
use function array_key_exists;
use function implode;

class AndroidMessage extends Message
{
    protected $platformKey = 'GCM';

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var bool
     */
    protected $vibrate;

    /**
     * @var string | null
     */
    private $channelId;

    public function setPriority(string $priority): Message
    {
        $validPriorities = [
            'PRIORITY_MIN' => -2,
            'PRIORITY_LOW' => -1,
            'PRIORITY_DEFAULT' => 0,
            'PRIORITY_HIGH' => 1,
            'PRIORITY_MAX' => 2,
        ];

        if (!array_key_exists($priority, $validPriorities)) {
            throw new InvalidTypeException('The priority level is not in the permitted list: '.implode(', ', $validPriorities));
        }

        $this->priority = $validPriorities[$priority];

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setIcon(?string $icon): Message
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @return static
     */
    public function setVibrate(?bool $vibrate = true)
    {
        $this->vibrate = $vibrate;

        return $this;
    }

    public function getVibrate(): ?bool
    {
        return $this->vibrate;
    }

    /**
     * @return static
     */
    public function setChannelId(?string $channelId)
    {
        $this->channelId = $channelId;

        return $this;
    }

    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->filterBlank(
            [
                'notification' => [
                    'title' => $this->getTitle(),
                    'body' => $this->getBody(),
                    'notification_priority' => $this->getPriority(),
                    'notification_count' => $this->getCount(),
                    'sound' => $this->getSound(),
                    'default_vibrate_timings' => $this->getVibrate(),
                    'icon' => $this->getIcon(),
                    'channel_id' => $this->getChannelId(),
                ],
                'data' => $this->getPayload(),
            ]
        );
    }
}
