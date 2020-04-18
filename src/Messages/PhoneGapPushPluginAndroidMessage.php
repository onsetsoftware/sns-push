<?php

declare(strict_types=1);

namespace SNSPush\Messages;

class PhoneGapPushPluginAndroidMessage extends AndroidMessage
{
    /**
     * use android inbox mode.
     *
     * @var bool
     */
    protected $useInboxMode = false;

    /**
     * the android inbox mode group message.
     *
     * substitute %n% for the number of notifications
     *
     * @var string|null
     */
    protected $inboxModeGroupMessage = '%n% messages';

    public function getUseInboxMode(): bool
    {
        return $this->useInboxMode ?? false;
    }

    /**
     * @return static
     */
    public function setUseInboxMode(bool $useInboxMode = true): Message
    {
        $this->useInboxMode = $useInboxMode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInboxModeGroupMessage(): string
    {
        return $this->inboxModeGroupMessage;
    }

    /**
     * @param string|null $inboxModeGroupMessage
     *
     * @return static
     */
    public function setInboxModeGroupMessage(string $inboxModeGroupMessage): Message
    {
        $this->inboxModeGroupMessage = $inboxModeGroupMessage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->filterBlank(
            [
                'data' => array_merge(
                    [
                        'title' => $this->getTitle(),
                        'message' => $this->getBody(),
                        'sound' => $this->getSound(),
                        'badge' => $this->getCount(),
                        'content-available' => $this->getContentAvailable() ? '1' : null,
                        'payload' => $this->getPayload(),
                    ],
                    $this->getUseInboxMode() ? [
                        'style' => 'inbox',
                        'summaryText' => $this->getInboxModeGroupMessage(),
                    ] : []
                ),
            ]
        );
    }
}
