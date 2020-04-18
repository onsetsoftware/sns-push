<?php

namespace SNSPush\ARN;

class EndpointARN extends ARN
{
    /**
     * Set the AWS target endpoint.
     *
     * @param mixed $target
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

    /**
     * Get the endpoint key.
     */
    public function getKey(): string
    {
        return 'TargetArn';
    }

    /**
     * Get the endpoint key for removing device.
     */
    public function getRemoveDeviceKey(): string
    {
        return 'EndpointArn';
    }
}
