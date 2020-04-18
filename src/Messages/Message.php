<?php

namespace SNSPush\Messages;

/**
 * message class for constructing SNS message data.
 */
abstract class Message implements MessageInterface
{
    /**
     * the message title.
     *
     * @var string|null
     */
    protected $title;

    /**
     * the message body.
     *
     * @var string|null
     */
    protected $body;

    /**
     * the message badge count.
     *
     * @var int|null
     */
    protected $count;

    /**
     * the notification sound.
     *
     * @var string|null
     */
    protected $sound;

    /**
     * other payload data to be added to the message.
     *
     * @var array|null
     */
    protected $payload;

    /**
     * whether the notification should be silent or not.
     *
     * @var bool|null
     */
    protected $contentAvailable;

    public function platformKey(): string
    {
        return $this->platformKey;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    /**
     * @return static
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * @return static
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @return static
     */
    public function setBadge(int $count)
    {
        $this->count = $count;

        return $this;
    }

    public function getSound(): string
    {
        return $this->sound ?? '';
    }

    /**
     * @return static
     */
    public function setSound(string $sound)
    {
        $this->sound = $sound;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload ?? [];
    }

    /**
     * @param mixed[] $payload
     *
     * @return static
     */
    public function setPayload(array $payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function getContentAvailable(): ?bool
    {
        return $this->contentAvailable;
    }

    /**
     * @return static
     */
    public function setContentAvailable(bool $contentAvailable = true)
    {
        $this->contentAvailable = $contentAvailable;

        return $this;
    }

    public function getFormattedData(): array
    {
        return [
            $this->platformKey => json_encode($this->getData()),
        ];
    }

    abstract public function getData(): array;

    /**
     * recursively removes blank values from an array
     * NB. Should not touch zero or false values.
     *
     * @param array $arr the array to have blank values removed
     *
     * @return array the array minus any blank values
     */
    protected function filterBlank(array $arr): array
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->filterBlank($value);
            }
        }

        return array_filter($arr, static function ($var) {
            return !($var === null || $var === '' || (is_array($var) && empty($var)));
        });
    }
}
