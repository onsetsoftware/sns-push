<?php

declare(strict_types=1);

namespace SNSPush\Messages;

use function array_merge;

final class TopicMessage implements MessageInterface
{
    /**
     * @var MessageInterface[]
     */
    private $messages;

    /**
     * TopicMessage constructor.
     *
     * @param MessageInterface[] $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function platformKey(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedData(): array
    {
        return array_reduce($this->messages, static function (array $data, MessageInterface $message) {
            return array_merge($data, $message->getFormattedData());
        }, []);
    }
}
