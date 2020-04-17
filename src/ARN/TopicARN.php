<?php

namespace SNSPush\ARN;

class TopicARN extends ARN
{
    /**
     * Set the AWS target endpoint.
     *
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get the endpoint key.
     */
    public function getKey(): string
    {
        return 'TopicArn';
    }
}
