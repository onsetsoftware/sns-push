<?php

use PHPUnit\Framework\TestCase;
use SNSPush\Messages\AndroidMessage;
use SNSPush\Messages\IOsMessage;
use SNSPush\Messages\MessageInterface;
use SNSPush\Messages\PhoneGapPluginPushIOSMessage;
use SNSPush\Messages\PhoneGapPushPluginAndroidMessage;

/**
 * @internal
 * @coversNothing
 */
class MessageTest extends TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @dataProvider messageProvider
     */
    public function testMessageFormat(MessageInterface $message, array $expected)
    {
        $this->assertEquals($expected, $message->getData());
    }

    public function messageProvider()
    {
        return [
            [
                (new IOsMessage())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setBadge(5)
                    ->setSound('Diamond.caf')
                    ->setContentAvailable(1),
                [
                    'aps' => [
                        'alert' => [
                            'title' => 'Message Title',
                            'body' => 'Message body',
                        ],
                        'badge' => 5,
                        'sound' => 'Diamond.caf',
                        'content-available' => true,
                    ],
                ],
            ],
            [
                (new PhoneGapPluginPushIOSMessage())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setBadge(5)
                    ->setSound('Diamond.caf')
                    ->setContentAvailable(1),
                [
                    'aps' => [
                        'alert' => [
                            'title' => 'Message Title',
                            'body' => 'Message body',
                        ],
                        'badge' => 5,
                        'sound' => 'Diamond.caf',
                        'content-available' => true,
                    ],
                ],
            ],
            [
                (new PhoneGapPushPluginAndroidMessage())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setBadge(5)
                    ->setSound('Diamond')
                    ->setContentAvailable(1),
                [
                    'data' => [
                        'title' => 'Message Title',
                        'message' => 'Message body',
                        'badge' => 5,
                        'sound' => 'Diamond',
                        'content-available' => true,
                    ],
                ],
            ],
            [
                (new AndroidMessage())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setBadge(5)
                    ->setSound('Diamond')
                    ->setContentAvailable(1),
                [
                    'notification' => [
                        'title' => 'Message Title',
                        'body' => 'Message body',
                        'notification_count' => 5,
                        'sound' => 'Diamond',
                    ],
                ],
            ],
            [
                (new PhoneGapPushPluginAndroidMessage())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setUseInboxMode(),
                [
                    'data' => [
                        'title' => 'Message Title',
                        'message' => 'Message body',
                        'style' => 'inbox',
                        'summaryText' => '%n% messages',
                    ],
                ],
            ],
            [
                (new PhoneGapPushPluginAndroidMessage())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setUseInboxMode()
                    ->setInboxModeGroupMessage('You have %n% messages. Please pay attention.'),
                [
                    'data' => [
                        'title' => 'Message Title',
                        'message' => 'Message body',
                        'style' => 'inbox',
                        'summaryText' => 'You have %n% messages. Please pay attention.',
                    ],
                ],
            ],
            [
                (new IosMessage())
                    ->setBadge(5)
                    ->setContentAvailable(1),
                [
                    'aps' => [
                        'badge' => 5,
                        'content-available' => true,
                    ],
                ],
            ],
            [
                (new PhoneGapPushPluginAndroidMessage())
                    ->setBadge(5)
                    ->setContentAvailable(1),
                [
                    'data' => [
                        'badge' => 5,
                        'content-available' => true,
                    ],
                ],
            ],
            [
                (new AndroidMessage())
                    ->setBadge(5)
                    ->setContentAvailable(1),
                [
                    'notification' => [
                        'notification_count' => 5,
                    ],
                ],
            ],
            [
                (new PhoneGapPushPluginAndroidMessage())
                    ->setBadge(0)
                    ->setContentAvailable(),
                [
                    'data' => [
                        'badge' => 0,
                        'content-available' => '1',
                    ],
                ],
            ],
            [
                (new IOsMessage())
                    ->setBadge(0)
                    ->setContentAvailable(),
                [
                    'aps' => [
                        'badge' => 0,
                        'content-available' => 1,
                    ],
                ],
            ],
            [
                (new IosMessage())
                    ->setBadge(5)
                    ->setContentAvailable()
                    ->setPayload([
                        'additional-data' => 123,
                    ]),
                [
                    'aps' => [
                        'badge' => 5,
                        'content-available' => 1,
                    ],
                    'additional-data' => 123,
                ],
            ],
            [
                (new AndroidMessage())
                    ->setBadge(5)
                    ->setContentAvailable()
                    ->setPayload([
                        'additional-data' => 123,
                    ]),
                [
                    'notification' => [
                        'notification_count' => 5,
                    ],
                    'data' => [
                        'additional-data' => 123,
                    ],
                ],
            ],
            [
                (new PhoneGapPushPluginAndroidMessage())
                    ->setBadge(5)
                    ->setContentAvailable()
                    ->setPayload([
                        'additional-data' => 123,
                    ]),
                [
                    'data' => [
                        'badge' => 5,
                        'content-available' => '1',
                        'payload' => ['additional-data' => 123],
                    ],
                ],
            ],
            [
                (new PhoneGapPluginPushIOSMessage())
                    ->setBadge(5)
                    ->setContentAvailable()
                    ->setPayload([
                        'additional-data' => 123,
                    ]),
                [
                    'aps' => [
                        'badge' => 5,
                        'content-available' => 1,
                    ],
                    'payload' => ['additional-data' => 123],
                ],
            ],
        ];
    }
}
