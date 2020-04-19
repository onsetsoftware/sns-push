<?php

declare(strict_types=1);

namespace Tests;

use SNSPush\Messages\AndroidMessage;
use SNSPush\Messages\IOsMessage;
use SNSPush\Messages\TopicMessage;

class Helpers
{
    public static function getAndroidMessage(): AndroidMessage
    {
        return (new AndroidMessage())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setSound('Sound')
            ;
    }

    public static function getIosMessage(): IOsMessage
    {
        return (new IOsMessage())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setSound('Sound.caf')
            ;
    }

    private static function getTopicMessage(): TopicMessage
    {
        return new TopicMessage([self::getIosMessage(), self::getAndroidMessage()]);
    }
}
