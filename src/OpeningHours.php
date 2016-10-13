<?php

namespace Spatie\OpeningHours;

use DateTime;
use DateTimeInterface;
use Spatie\OpeningHours\Exceptions\Exception;
use Spatie\OpeningHours\Exceptions\InvalidDate;
use Spatie\OpeningHours\Exceptions\InvalidDayName;
use Spatie\OpeningHours\Helpers\Arr;

class OpeningHours
{
    /** @var \Spatie\OpeningHours\Day[] */
    protected $openingHours;

    /** @var array */
    protected $exceptions = [];

    /**
     * OpeningHours constructor.
     */
    public function __construct()
    {
        $this->openingHours = Day::mapDays(
            function () {
                return new OpeningHoursForDay();
            }
        );
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public static function create(array $data)
    {
        return (new static())->fill($data);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function isValid(array $data)
    {
        try {
            static::create($data);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fill(array $data)
    {
        list($openingHours, $exceptions) = $this->parseOpeningHoursAndExceptions($data);

        foreach ($openingHours as $day => $openingHoursForThisDay) {
            $this->setOpeningHoursFromStrings($day, $openingHoursForThisDay);
        }

        $this->setExceptionsFromStrings($exceptions);

        return $this;
    }

    /**
     * @return array
     */
    public function forWeek()
    {
        return $this->openingHours;
    }

    /**
     * @param string $day
     * @return OpeningHoursForDay
     */
    public function forDay($day)
    {
        $day = $this->normalizeDayName($day);

        return $this->openingHours[$day];
    }

    /**
     * @param DateTimeInterface $date
     * @return OpeningHoursForDay
     */
    public function forDate(DateTimeInterface $date)
    {
        return isset($this->exceptions[$date->format('Y-m-d')])
            ? $this->exceptions[$date->format('Y-m-d')] : $this->forDay(Day::onDateTime($date));
    }

    /**
     * @return array
     */
    public function exceptions()
    {
        return $this->exceptions;
    }

    /**
     * @param string $day
     * @return bool
     */
    public function isOpenOn($day)
    {
        return count($this->forDay($day)) > 0;
    }

    /**
     * @param string $day
     * @return bool
     */
    public function isClosedOn($day)
    {
        return $this->isOpenOn($day);
    }

    /**
     * @param DateTimeInterface $dateTime
     * @return bool
     */
    public function isOpenAt(DateTimeInterface $dateTime)
    {
        $openingHoursForDay = $this->forDate($dateTime);

        return $openingHoursForDay->isOpenAt(Time::fromDateTime($dateTime));
    }

    /**
     * @param DateTimeInterface $dateTime
     * @return bool
     */
    public function isClosedAt(DateTimeInterface $dateTime)
    {
        return !$this->isOpenAt($dateTime);
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->isOpenAt(new DateTime());
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->isClosedAt(new DateTime());
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseOpeningHoursAndExceptions(array $data)
    {
        $exceptions = Arr::pull($data, 'exceptions', []);
        $openingHours = [];

        foreach ($data as $day => $openingHoursData) {
            $openingHours[$this->normalizeDayName($day)] = $openingHoursData;
        }

        return [$openingHours, $exceptions];
    }

    /**
     * @param string $day
     * @param array  $openingHours
     */
    protected function setOpeningHoursFromStrings($day, array $openingHours)
    {
        $day = $this->normalizeDayName($day);

        $this->openingHours[$day] = OpeningHoursForDay::fromStrings($openingHours);
    }

    /**
     * @param array $exceptions
     */
    protected function setExceptionsFromStrings(array $exceptions)
    {
        $this->exceptions = Arr::map(
            $exceptions,
            function (array $openingHours, $date) {
                $dateTime = DateTime::createFromFormat('Y-m-d', $date);

                if ($dateTime === false || $dateTime->format('Y-m-d') !== $date) {
                    throw InvalidDate::invalidDate($date);
                }

                return OpeningHoursForDay::fromStrings($openingHours);
            }
        );
    }

    /**
     * @param string $day
     * @return string
     * @throws InvalidDayName
     */
    protected function normalizeDayName($day)
    {
        $day = strtolower($day);

        if (!Day::isValid($day)) {
            throw new InvalidDayName();
        }

        return $day;
    }
}
