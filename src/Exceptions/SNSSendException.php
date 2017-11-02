<?php

namespace SNSPush\Exceptions;

use Aws\ResultInterface;
use Aws\Sns\Exception\SnsException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SNSSendException extends SNSPushException
{
    public function __construct(string $message, int $code = 0, SnsException $e = null)
    {
        parent::__construct($message, $code, $e);
    }

    /**
     * Get the sent HTTP request if any.
     *
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->getPrevious()->getRequest();
    }

    /**
     * Get the request ID of the error. This value is only present if a
     * response was received and is not present in the event of a networking
     * error.
     *
     * @return string|null Returns null if no response was received
     */
    public function getRequestId()
    {
        return $this->getPrevious()->getAwsRequestId();
    }

    /**
     * Get the received HTTP response if any.
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->getPrevious()->getResponse();
    }

    /**
     * Get the result of the exception if available
     *
     * @return ResultInterface|null
     */
    public function getResult()
    {
        return $this->getPrevious()->getResult();
    }

    /**
     * Returns true if this is a connection error.
     *
     * @return bool
     */
    public function isConnectionError()
    {
        return $this->getPrevious()->isConnectionError();
    }

    /**
     * Get the AWS error type.
     *
     * @return string|null Returns null if no response was received
     */
    public function getErrorType()
    {
        return $this->getPrevious()->getAwsErrorType();
    }

    /**
     * Get the AWS error code.
     *
     * @return string|null Returns null if no response was received
     */
    public function getErrorCode()
    {
        return $this->getPrevious()->getAwsErrorCode();
    }

    /**
     * If available, gets the HTTP status code of the corresponding response
     *
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->getPrevious()->getStatusCode();
    }
}
