<?php

use Aws\Sns\SnsClient;
use Aws\Sns\Exception\SnsException;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use SNSPush\SNSPush;
use SNSPush\Exceptions\SNSSendException;
use SNSPush\Exceptions\SNSConfigException;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\InvalidTypeException;
use SNSPush\Messages\Message;

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

    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        $config = [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESSKEY',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ];

        $this->client = Mockery::mock(SnsClient::class);
        $this->sns = new SNSPush($config, $this->client);
    }

    /**
     * @dataProvider configProvider
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
                []
            ],
            [
                [
                    'access_key' => 'ACCESSKEY',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ]
            ],
            [
                [
                    'account_id' => '01234567890',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ]
            ],
            [
                [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESSKEY',
                    'platform_applications' => [
                        'ios' => 'arn:aws:sns:eu-west-1:01234567890:app/APNS/application-ios',
                        'android' => 'arn:aws:sns:eu-west-1:01234567890:app/GCM/application-android',
                    ],
                ]
            ],
            [
                [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESSKEY',
                    'secret_key' => 'SECRET_KEY',
                    'platform_applications' => [],
                ]
            ],
            [
                [
                    'account_id' => '01234567890',
                    'access_key' => 'ACCESSKEY',
                    'secret_key' => 'SECRET_KEY',
                ]
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

        $this->sns->sendPushNotificationToEndpoint($endpoint, $this->getMessage());
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testInvalidTypeException($endpoint)
    {
        $this->expectException(InvalidArnException::class);

        $this->sns->sendPushNotificationToEndpoint($endpoint, $this->getMessage());
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
        return (new Message())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setIosSound('Sound.caf')
            ->setAndroidSound('Sound');
    }
}
