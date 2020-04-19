<?php

namespace SNSPush;

use InvalidArgumentException;

class Region
{
    /**
     * Supported AWS regions.
     */
    public const REGION_EU = 'eu'; // Europe
    public const REGION_US = 'us'; // US
    public const REGION_CA = 'ca'; // Canada
    public const REGION_AP = 'ap'; // Asia Pacific
    public const REGION_SA = 'sa'; // South America

    /**
     * List of AWS regions supported by this package.
     *
     * @var array
     */
    protected static $regions = [
        self::REGION_US, self::REGION_EU, self::REGION_AP, self::REGION_CA, self::REGION_SA,
    ];

    /**
     * The name of the region.
     *
     * @var string
     */
    protected $name;

    /**
     * The area of the region.
     *
     * @var string
     */
    protected $area;

    /**
     * The number associated with the region.
     *
     * @var int
     */
    protected $number;

    /**
     * Region constructor.
     *
     * @param $name
     * @param $area
     * @param $number
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, string $area, int $number)
    {
        $this->setName($name);
        $this->setArea($area);
        $this->setNumber($number);
    }

    /**
     * Allow object to be converted to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Get the name(area) of the region.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name(area) of the region.
     *
     * @throws InvalidArgumentException
     */
    public function setName(string $name): void
    {
        if (!in_array($name, self::$regions, true)) {
            throw new InvalidArgumentException('This region is not supported.');
        }

        $this->name = $name;
    }

    /**
     * Get the area of the region.
     *
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set the area of the region.
     */
    public function setArea(string $area): void
    {
        $this->area = $area;
    }

    /**
     * Get the number associated with the region.
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Set the number for the region.
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * Get a string of the region.
     */
    public function toString(): string
    {
        return $this->getName().'-'.$this->getArea().'-'.$this->getNumber();
    }

    /**
     * Parse the region into parts.
     *
     * @param $string
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public static function parse($string): Region
    {
        $parts = explode('-', $string);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException('The region is malformed or invalid.');
        }

        return new static($parts[0], $parts[1], $parts[2]);
    }
}
