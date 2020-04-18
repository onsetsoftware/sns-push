<?php

namespace SNSPush;

use Aws\ApiGateway\Exception\ApiGatewayException;
use Aws\AwsClientInterface;
use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use InvalidArgumentException;
use SNSPush\ARN\ApplicationARN;
use SNSPush\ARN\ARN;
use SNSPush\ARN\EndpointARN;
use SNSPush\ARN\SubscriptionARN;
use SNSPush\ARN\TopicARN;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\InvalidTypeException;
use SNSPush\Exceptions\MismatchedPlatformException;
use SNSPush\Exceptions\SNSConfigException;
use SNSPush\Exceptions\SNSSendException;
use SNSPush\Messages\IOsMessage;
use SNSPush\Messages\MessageInterface;
use SNSPush\Messages\TopicMessage;
use function explode;
use function json_encode;
use function strpos;

class SNSPush
{
    /**
     * Supported target types.
     */
    public const TYPE_ENDPOINT = 1;
    public const TYPE_TOPIC = 2;
    public const TYPE_APPLICATION = 3;
    public const TYPE_SUBSCRIPTION = 4;

    /**
     * List of endpoint targets supported by this package.
     *
     * @var array
     */
    protected static $types = [
        self::TYPE_ENDPOINT, self::TYPE_TOPIC, self::TYPE_APPLICATION, self::TYPE_SUBSCRIPTION,
    ];

    /**
     * The AWS SNS Client.
     *
     * @var SnsClient
     */
    protected $client;

    /**
     * The AWS SNS configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * SNSPush constructor.
     *
     * @param string[] $config
     *
     * @throws SNSConfigException
     */
    public function __construct(array $config = [], ?AwsClientInterface $client = null)
    {
        // Set configuration data.
        $this->config = array_merge([
            'region' => 'eu-west-1',
            'api_version' => '2010-03-31',
            'scheme' => 'https',
        ], $config);

        // Validate config.
        $this->validateConfig();

        // Initialize the SNS Client.
        $this->client = $client ?? $this->createClient();
    }

    /**
     * Adds a device to an application endpoint in AWS SNS.
     *
     * @throws InvalidArnException
     * @throws InvalidArgumentException
     * @throws SNSSendException
     *
     * @return EndpointARN
     */
    public function addDevice(string $token, string $platform): ?EndpointARN
    {
        $arn = $this->config['platform_applications'][$platform];

        if (is_string($arn)) {
            $arn = ApplicationARN::parse($arn);
        }

        try {
            $result = $this->client->createPlatformEndpoint([
                $arn->getKey() => $arn->toString(),
                'Token' => $token,
            ]);

            return isset($result['EndpointArn']) ? EndpointARN::parse($result['EndpointArn']) : null;
        } catch (SnsException $e) {
            throw new SNSSendException($e->getAwsErrorMessage() ?? $e->getMessage(), $e->getCode(), $e);
        } catch (ApiGatewayException $e) {
            throw new SNSSendException('There was an unknown problem with the AWS SNS API. Code: '.$e->getCode(), $e->getCode(), $e);
        }
    }

    /**
     * Subscribe a device endpoint to an ARN (topic subscription).
     *
     * @param ARN|EndpointARN|string $endpointArn
     * @param ARN|string|TopicARN    $topicArn
     *
     * @throws InvalidArnException
     * @throws InvalidArgumentException
     * @throws SNSSendException
     */
    public function subscribeDeviceToTopic($endpointArn, $topicArn, array $options = []): ?SubscriptionARN
    {
        if (is_string($topicArn)) {
            $topicArn = TopicARN::parse($topicArn);
        }

        if (is_string($endpointArn)) {
            $endpointArn = EndpointARN::parse($endpointArn);
        }

        try {
            $result = $this->client->subscribe([
                'Endpoint' => $endpointArn->toString(),
                'Protocol' => $options['protocol'] ?? 'application',
                $topicArn->getKey() => $topicArn->toString(),
            ]);

            return isset($result['SubscriptionArn']) ? SubscriptionARN::parse($result['SubscriptionArn']) : null;
        } catch (SnsException $e) {
            throw new SNSSendException($e->getAwsErrorMessage() ?? $e->getMessage(), $e->getCode(), $e);
        } catch (ApiGatewayException $e) {
            throw new SNSSendException('There was an unknown problem with the AWS SNS API. Code: '.$e->getCode(), $e->getCode(), $e);
        }
    }

    /**
     * Remove a device endpoint from an ARN (unsubscribe topic).
     *
     * @param string|SubscriptionARN $arn
     *
     * @throws InvalidArnException
     * @throws SNSSendException
     */
    public function removeDeviceFromTopic($arn): void
    {
        if (is_string($arn)) {
            $arn = SubscriptionARN::parse($arn);
        }

        try {
            $this->client->unsubscribe([
                $arn->getKey() => $arn->toString(),
            ]);
        } catch (SnsException $e) {
            throw new SNSSendException($e->getAwsErrorMessage() ?? $e->getMessage(), $e->getCode(), $e);
        } catch (ApiGatewayException $e) {
            throw new SNSSendException('There was an unknown problem with the AWS SNS API. Code: '.$e->getCode(), $e->getCode(), $e);
        }
    }

    /**
     * Removes a device to an application endpoint in AWS SNS.
     *
     * @param ARN|EndpointARN|string $arn
     *
     * @throws InvalidArnException
     * @throws SNSSendException
     */
    public function removeDevice($arn): void
    {
        if (!$arn instanceof EndpointARN) {
            $arn = EndpointARN::parse($arn);
        }

        try {
            $this->client->deleteEndpoint([
                $arn->getRemoveDeviceKey() => $arn->toString(),
            ]);
        } catch (SnsException $e) {
            throw new SNSSendException($e->getAwsErrorMessage() ?? $e->getMessage(), $e->getCode(), $e);
        } catch (ApiGatewayException $e) {
            throw new SNSSendException('There was an unknown problem with the AWS SNS API. Code: '.$e->getCode(), $e->getCode(), $e);
        }
    }

    /**
     * Gets list of all platform applications (ios, android, etc...).
     *
     * @throws SNSSendException
     *
     * @return Result
     */
    public function getPlatformApplications(): ?Result
    {
        try {
            return $this->client->listPlatformApplications();
        } catch (SnsException $e) {
            throw new SNSSendException($e->getAwsErrorMessage() ?? $e->getMessage(), $e->getCode(), $e);
        } catch (ApiGatewayException $e) {
            throw new SNSSendException('There was an unknown problem with the AWS SNS API. Code: '.$e->getCode(), $e->getCode(), $e);
        }
    }

    /**
     * Send push notification to a topic endpoint.
     *
     * @param ARN|string|TopicARN $arn
     * @param MessageInterface[]  $messages
     *
     * @throws InvalidArnException
     * @throws InvalidTypeException
     * @throws SNSSendException
     *
     * @return bool|Result
     */
    public function sendPushNotificationToTopic($arn, TopicMessage $message)
    {
        $arn = $arn instanceof TopicARN ? $arn : TopicARN::parse($arn);

        return $this->sendPushNotification($arn, $message);
    }

    /**
     * Send push notification to a device endpoint.
     *
     * @param ARN|EndpointARN|string $arn
     * @param mixed                  $message
     *
     * @throws InvalidArnException
     * @throws InvalidTypeException
     * @throws SNSSendException
     *
     * @return bool|Result
     */
    public function sendPushNotificationToEndpoint($arn, MessageInterface $message)
    {
        $arn = $arn instanceof EndpointARN ? $arn : EndpointARN::parse($arn);

        return $this->sendPushNotification($arn, $message);
    }

    /**
     * Check if provided endpoint type is supported and valid.
     *
     * @param int $type
     */
    protected static function isValidType($type): bool
    {
        return in_array($type, self::$types, true);
    }

    /**
     * Validate config to ensure required parameters have been supplied.
     *
     * @throws SNSConfigException
     */
    private function validateConfig(): void
    {
        if (empty($this->config['account_id'])) {
            throw new SNSConfigException('Please supply your Amazon "account_id" in the config.');
        }

        if (empty($this->config['access_key'])) {
            throw new SNSConfigException('Please supply your Amazon API "access_key" in the config.');
        }

        if (empty($this->config['secret_key'])) {
            throw new SNSConfigException('Please supply your Amazon API "secret_key" in the config.');
        }

        if (empty($this->config['platform_applications'])) {
            throw new SNSConfigException('Please supply your Amazon SNS "platform_applications" in the config.');
        }
    }

    /**
     * Initialize the AWS SNS Client.
     *
     * @throws InvalidArgumentException
     */
    private function createClient(): SnsClient
    {
        return new SnsClient([
            'region' => $this->config['region'],
            'version' => $this->config['api_version'],
            'scheme' => $this->config['scheme'],
            'credentials' => $this->getCredentials(),
        ]);
    }

    /**
     * Get an instance of the Credentials.
     */
    private function getCredentials(): Credentials
    {
        return new Credentials($this->config['access_key'], $this->config['secret_key']);
    }

    /**
     * Send the push notification.
     *
     * @throws MismatchedPlatformException
     * @throws SNSSendException
     *
     * @return bool|Result
     */
    private function sendPushNotification(ARN $arn, MessageInterface $message)
    {
        if ($arn instanceof EndpointARN) {
            $platform = explode('/', $arn->getTarget())[1];

            if (strpos($platform, $message->platformKey()) === false) {
                throw new MismatchedPlatformException('The endpoint platform does not match the message provided');
            }
            if ($message instanceof IOsMessage && strpos($platform, 'SANDBOX') !== false) {
                $message->devMode();
            }
        }

        $data[$arn->getKey()] = $arn->toString();

        $data['Message'] = json_encode($message->getFormattedData());
        $data['MessageStructure'] = 'json';

        try {
            $result = $this->client->publish($data);

            return $result ?? false;
        } catch (SnsException $e) {
            throw new SNSSendException($e->getAwsErrorMessage() ?? $e->getMessage(), $e->getCode(), $e);
        } catch (ApiGatewayException $e) {
            throw new SNSSendException('There was an unknown problem with the AWS SNS API. Code: '.$e->getCode(), $e->getCode(), $e);
        }
    }
}
