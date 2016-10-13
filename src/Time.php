<?php

namespace Spatie\OpeningHours;

use DateTime;
use DateTimeInterface;
use Spatie\OpeningHours\Exceptions\InvalidTimeString;

class Time
{
    /** @var int */
    protected $hours;
    /**
     * @var int
     */
    protected $minutes;

    /**
     * Time constructor.
     * @param int $hours
     * @param int $minutes
     */
    protected function __construct($hours, $minutes)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
    }

    /**
     * @param string $string
     * @return Time
     * @throws InvalidTimeString
     */
    public static function fromString($string)
    {
        if (!preg_match('/^([0-1][0-9])|(2[0-4]):[0-5][0-9]$/', $string)) {
            throw InvalidTimeString::forString($string);
        }

        list($hours, $minutes) = explode(':', $string);

        return new self($hours, $minutes);
    }

    /**
     * @param DateTimeInterface $dateTime
     * @return Time
     */
    public static function fromDateTime(DateTimeInterface $dateTime)
    {
        return self::fromString($dateTime->format('H:i'));
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function isSame(Time $time)
    {
        return (string)$this === (string)$time;
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function isAfter(Time $time)
    {
        if ($this->isSame($time)) {
            return false;
        }

        if ($this->hours > $time->hours) {
            return true;
        }

        return $this->hours === $time->hours && $this->minutes >= $time->minutes;
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function isBefore(Time $time)
    {
        if ($this->isSame($time)) {
            return false;
        }

        return !$this->isAfter($time);
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function isSameOrAfter(Time $time)
    {
        return $this->isSame($time) || $this->isAfter($time);
    }

    /**
     * @return DateTime
     */
    public function toDateTime()
    {
        return new DateTime("1970-01-01 {$this}:00");
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return str_pad($this->hours, 2, '0', STR_PAD_LEFT).':'.str_pad($this->minutes, 2, '0', STR_PAD_LEFT);
    }
}
