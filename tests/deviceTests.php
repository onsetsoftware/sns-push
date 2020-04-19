<?php

use Aws\Result;
use Aws\Sns\SnsClient;
use PHPUnit\Framework\TestCase;
use SNSPush\Messages\AndroidMessage;
use SNSPush\Messages\IOsMessage;
use SNSPush\Messages\MessageInterface;
use SNSPush\Messages\TopicMessage;
use SNSPush\SNSPush;
use Tests\Config;

/**
 * @internal
 * @coversNothing
 */
class DeviceTest extends TestCase
{
    /**
     * @var SnsClient;
     */
    protected $client;

    /**
     * @var SNSPush;
     */
    protected $sns;

    protected function setUp(): void
    {
        $config = Config::data();

        $this->client = Mockery::mock(SnsClient::class);
        $this->sns = new SNSPush($config, $this->client);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @dataProvider messageProvider
     */
    public function testSendMessageToDevice(MessageInterface $message, string $endpoint, array $expectedPayload)
    {
        $messageId = 'c03c7f56-c583-55f4-b521-2d24537a3337';
        $this->client->expects()->publish($expectedPayload)->andReturns(new Result(['MessageId' => $messageId]));

        $result = $this->sns->sendPushNotificationToDevice($endpoint, $message);

        $this->assertEquals($messageId, $result->get('MessageId'));
    }

    /**
     * @dataProvider topicMessageProvider
     */
    public function testSendMessageToTopic(TopicMessage $message, string $endpoint, array $expectedPayload)
    {
        $messageId = 'c03c7f56-c583-55f4-b521-2d24537a4437';
        $this->client->expects()->publish($expectedPayload)->andReturns(new Result(['MessageId' => $messageId]));

        $result = $this->sns->sendPushNotificationToTopic($endpoint, $message);

        $this->assertEquals($messageId, $result->get('MessageId'));
    }

    public function messageProvider()
    {
        $iosEndpoint = 'arn:aws:sns:eu-west-1:01234567890:endpoint/APNS/application-ios/a5825a90-d4fc-3116-8c9f-821d81f745a0';
        $androidEndpoint = 'arn:aws:sns:eu-west-1:061712452045:endpoint/GCM/onsetupdates-dev-android/70298154-4c4b-3a62-8d4a-39b4a59635f3';

        return [
            [
                $this->getAndroidMessage(),
                $androidEndpoint,
                [
                    'TargetArn' => $androidEndpoint,
                    'Message' => '{"GCM":"{\"notification\":{\"title\":\"Message Title\",\"body\":\"Message body\",\"notification_count\":5,\"sound\":\"Sound\"}}"}',
                    'MessageStructure' => 'json',
                ],
            ],
            [
                $this->getIosMessage(),
                $iosEndpoint,
                [
                    'TargetArn' => $iosEndpoint,
                    'Message' => '{"APNS":"{\"aps\":{\"alert\":{\"title\":\"Message Title\",\"body\":\"Message body\"},\"sound\":\"Sound.caf\",\"badge\":5}}"}',
                    'MessageStructure' => 'json',
                ],
            ],
        ];
    }

    public function topicMessageProvider()
    {
        $topicEndpoint = 'arn:aws:sns:eu-west-1:01234567890:test-topic';

        return [
            [
                $this->getTopicMessage(),
                $topicEndpoint,
                [
                    'TopicArn' => $topicEndpoint,
                    'Message' => '{"APNS":"{\"aps\":{\"alert\":{\"title\":\"Message Title\",\"body\":\"Message body\"},\"sound\":\"Sound.caf\",\"badge\":5}}","GCM":"{\"notification\":{\"title\":\"Message Title\",\"body\":\"Message body\",\"notification_count\":5,\"sound\":\"Sound\"}}"}',
                    'MessageStructure' => 'json',
                ],
            ],
        ];
    }

    public function getAndroidMessage(): AndroidMessage
    {
        return (new AndroidMessage())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setSound('Sound')
        ;
    }

    public function getIosMessage(): IOsMessage
    {
        return (new IOsMessage())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setSound('Sound.caf')
            ;
    }

    private function getTopicMessage(): TopicMessage
    {
        return new TopicMessage([$this->getIosMessage(), $this->getAndroidMessage()]);
    }
}
