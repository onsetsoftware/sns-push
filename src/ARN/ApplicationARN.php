<?php

namespace SNSPush\ARN;

class ApplicationARN extends ARN
{
    /**
     * Set the AWS target endpoint.
     *
     * @param mixed $target
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
        return 'PlatformApplicationArn';
    }
}
