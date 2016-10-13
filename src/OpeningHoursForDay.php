<?php

namespace Spatie\OpeningHours;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Spatie\OpeningHours\Exceptions\OverlappingTimeRanges;
use Spatie\OpeningHours\Helpers\Arr;

class OpeningHoursForDay implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array */
    protected $openingHours = [];

    /**
     * @param array $strings
     * @return static
     */
    public static function fromStrings(array $strings)
    {
        $openingHoursForDay = new static();

        $timeRanges = Arr::map(
            $strings,
            function ($string) {
                return TimeRange::fromString($string);
            }
        );

        $openingHoursForDay->guardAgainstTimeRangeOverlaps($timeRanges);

        $openingHoursForDay->openingHours = $timeRanges;

        return $openingHoursForDay;
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function isOpenAt(Time $time)
    {
        foreach ($this->openingHours as $timeRange) {
            if ($timeRange->containsTime($time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->openingHours[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->openingHours[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception();
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->openingHours[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->openingHours);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->openingHours);
    }

    /**
     * @param array $openingHours
     * @throws OverlappingTimeRanges
     */
    protected function guardAgainstTimeRangeOverlaps(array $openingHours)
    {
        foreach (Arr::createUniquePairs($openingHours) as $timeRanges) {
            if ($timeRanges[0]->overlaps($timeRanges[1])) {
                throw OverlappingTimeRanges::forRanges($timeRanges[0], $timeRanges[1]);
            }
        }
    }
}
