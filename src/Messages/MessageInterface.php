<?php

namespace SNSPush\Messages;

/**
 * the message interface.
 */
interface MessageInterface
{
    public function platformKey(): string;

    /**
     * builds the message data.
     */
    public function getFormattedData(): array;
}
