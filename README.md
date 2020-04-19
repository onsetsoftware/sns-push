SNS Push (for AWS SNS API)
======

> This package provides helper methods to send mobile push notifications with the Amazon (AWS) SNS API.

[![Packagist](https://img.shields.io/badge/onsetsoftware-sns--push-brightgreen.svg)](https://packagist.org/packages/onsetsoftware/sns-push)

SNS Push is a simple SNS SDK wrapper with a collection of methods to aid in interacting with the AWS SNS API. It works directly with Laravel or can be used as a standalone PHP package.

# Prerequisites

 Supports  | Version
:----------|:----------
 PHP       | 7.2
 Platforms | ios/android

# Installing

You need to use Composer to install SNS Push into your project:

```
composer require onsetsoftware/sns-push
```

## Other PHP Framework (not Laravel) Setup

You should include the Composer `autoload.php` file if not already loaded:

```php
require __DIR__ . '/vendor/autoload.php';
 ```

Instantiate the SNSPush class with the following required config values:
- account_id
- access_key
- secret_key
- platform_applications

Also configurable:
- region [default: eu-west-1]
- api_version [default: 2010-03-31]
- scheme [default: https]

```php
<?php

use SNSPush\SNSPush;

$sns = new SNSPush([
    'account_id' => '<aws-account-id>', // Required
    'access_key' => '<aws-iam-user-access-key>', // Required
    'secret_key' => '<aws-iam-user-secret-key>', // Required
    'scheme' => 'http', // Defaults to https
    'platform_applications' => [ // application endpoints - Required
        'ios' => '<application-endpoint-arn>',
        'android' => '<application-endpoint-arn>'
    ]
]);
```

## Laravel Service Provider

If you are a Laravel user, you can make use of the included service provider. Just add `SNSPushServiceProvider` in your `config/app.php`:

```php
<?php
[
    //...
    'providers' => [
        /*
         * Package Service Providers...
         */
        SNSPush\SNSPushServiceProvider::class,
    ]
];
```

Add 'sns' config keys to the `config/services.php`

```php
<?php
[
    //...
    'sns' => [
        'account_id' => env('SNS_ACCOUNT_ID', ''),
        'access_key' => env('SNS_ACCESS_KEY', ''),
        'secret_key' => env('SNS_SECRET_KEY', ''),
        'scheme' => env('SNS_SCHEME', 'https'),
        'region' => env('SNS_REGION', 'eu-west-1'),
        'platform_applications' => [
            'ios' => '<application-endpoint-arn>',
            'android' => '<application-endpoint-arn>'
        ]
    ]
];
```

## Add Device to Application

Add a device to a platform application (ios/android) by passing the device token and application key to `addDevice()`.

```php
<?php
/**
 * @param string $token the raw device token
 * @param string $platform ( ios | android )  
 *                         
 * @return ARN the ARN endpoint for the device
 */
$sns->addDevice('<device-token>', '<platform-id>');
```

## Remove Device from Application

Remove a device from AWS SNS by passing the Endpoint ARN to `removeDevice()`.

```php
<?php

$sns->removeDevice('<endpoint-arn>');
```

## Subscribe Device to Topic

Subscribe a device to a Topic by passing the Endpoint Arn and Topic Arn to `subscribeDeviceToTopic()`.

```php
<?php
/**
 * @return SubscriptionARN
 */
$sns->subscribeDeviceToTopic('<device-endpoint-arn>', '<topic-arn>');
```

## Remove Device from Topic

Remove a device from a Topic by passing the Subscription Arn to `removeDeviceFromTopic()`.

```php
<?php

$sns->removeDeviceFromTopic('<subscription-arn>');
```

## Sending Push Notifications

SNS Push supports sending notifications to both Topic Endpoint or directly to an Endpoint ARN (Device).

### Messages

Messages must implement `SNSPush\Messages\MessageInterface`. There are a number of utility classes which format push notifications correctly for the various endpoint types.

```php
<?php

use SNSPush\Messages\IOsMessage;

$message = new IOsMessage();

$message->setTitle('Message Title')
        ->setBody('Message body')
        ->setBadge(5)
        ->setSound('sound.caf')
        ->setPayload(
          [
              'custom-key' => 'value',
          ]
      );
```

#### Phonegap Plugin Push
The package includes two classes to help format messages for use with the Phonegap Plugin Push Cordova package.

```php
PhoneGapPluginPushIOSMessage::class;
PhoneGapPluginPushAndroidMessage::class;
``` 

For the full api, please consult the source of each of the message types

### Send to Device

Simply pass an object implementing `SNSPush\Messages\MessageInterface`, along with the endpoint ARN. The Endpoint platform must match the message type.

```php
<?php

$sns->sendPushNotificationToDevice(
    '<endpoint-arn>',
    $message
);
```

### Send to Topic

First you should form your `SNSPush\Messages\TopicMessage` by passing an array of the Message objects for the enpoints you need to address. Then pass the `TopicMessage` to the `sendPushNotificationToTopic` method.

```php
<?php

$iosMessage = new IOsMessage();
$androidMessage = new AndroidMessage();

$message = new TopicMessage([$iosMessage, $androidMessage]);

/**
 * @param TopicARN $arm
 * @param TopicMessage $message 
 */
$sns->send->sendPushNotificationToTopic(
    '<topic-arn>',
    $message
);
```

## Thanks

This package builds on the work done by [ReduGroup](https://github.com/ReduGroup/sns-push).

## Licence

[MIT License](https://github.com/ReduGroup/sns-push/blob/master/LICENSE.md) © On Set Software Ltd
