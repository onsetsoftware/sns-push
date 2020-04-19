<?php

use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\MismatchedPlatformException;
use SNSPush\Exceptions\SNSConfigException;
use SNSPush\Exceptions\SNSSendException;
use SNSPush\Messages\IOsMessage;
use SNSPush\SNSPush;
use Tests\Helpers;

/**
 * @internal
 * @coversNothing
 */
class ExceptionTests extends TestCase
{
    /**
     * @var Mock;
     */
    protected $client;

    /**
     * @var SNSPush;
     */
    protected $sns;

    protected function setUp(): void
    {
        $config = [
            'account_id' => '01234567890',
            'access_key' => 'ACCESS_KEY',
            'secret_key' => 'SECRET_KEY',
            'platform_applications' => [
                'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
            ],
        ];

        $this->client = Mockery::mock(SnsClient::class);
        $this->sns = new SNSPush($config, $this->client);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @dataProvider configProvider
     *
     * @param mixed $config
     */
    public function testConfigExceptionThrown($config)
    {
        $this->expectException(SNSConfigException::class);

        $sns = new SNSPush($config);
    }

    public function configProvider()
    {
        return [
            [
                [],
            ],
            [
                [
                    'access_key' => 'ACCESS_KEY',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ],
            ],
            [
                [
                    'account_id' => '01234567890',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ],
            ],
            [
                [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESS_KEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ],
            ],
            [
                [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESS_KEY',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [],
                ],
            ],
            [
                [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESS_KEY',
                    'secret_key' => 'SECRET_KEY',
                ],
            ],
        ];
    }

    public function testSendException()
    {
        $this->expectException(SNSSendException::class);

        $exception = Mockery::mock(SnsException::class);
        $exception->expects()->getAwsErrorMessage()->andReturns('There was an error');

        $endpoint = 'arn:aws:sns:eu-west-1:01234567890:endpoint/APNS/application-ios/a5825a90-d4fc-3116-8c9f-821d81f745a0';
        $this->client->shouldReceive('publish')->andThrow($exception);

        $this->sns->sendPushNotificationToDevice($endpoint, $this->getMessage());
    }

    public function testInvalidPatformException()
    {
        $this->expectException(MismatchedPlatformException::class);

        $endpoint = 'arn:aws:sns:eu-west-1:01234567890:endpoint/APNS/application-ios/a5825a90-d4fc-3116-8c9f-821d81f745a0';

        $this->sns->sendPushNotificationToDevice($endpoint, Helpers::getAndroidMessage());
    }

    /**
     * @dataProvider endpointProvider
     *
     * @param mixed $endpoint
     */
    public function testInvalidTypeException($endpoint)
    {
        $this->expectException(InvalidArnException::class);

        $this->sns->sendPushNotificationToDevice($endpoint, $this->getMessage());
    }

    public function endpointProvider()
    {
        return [
            ['arn:aws:sns:eu-west-1:01234567890'],
            ['sns:eu-west-1:01234567890:endpoint/APNS/application-ios/a5825a90-d4fc-3116-8c9f-821d81f745a0'],
        ];
    }

    public function getMessage()
    {
        return (new IOsMessage())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setSound('Sound')
        ;
    }
}
