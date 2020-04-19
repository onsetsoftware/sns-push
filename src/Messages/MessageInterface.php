<?php

namespace SNSPush\Messages;

/**
 * the message interface.
 */
interface MessageInterface
{
    /**
     * the relevant AWS platform key.
     *
     * @return string "APNS" | "APNS_SANDBOX" | "GCM
     */
    public function platformKey(): string;

    /**
     * builds the message data.
     */
    public function getFormattedData(): array;
}
