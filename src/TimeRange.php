<?php

namespace Spatie\OpeningHours;

use Spatie\OpeningHours\Exceptions\InvalidTimeRangeString;

class TimeRange
{
    /**
     * @var Time
     */
    protected $start;

    /**
     * @var Time
     */
    protected $end;

    /**
     * TimeRange constructor.
     * @param Time $start
     * @param Time $end
     */
    protected function __construct(Time $start, Time $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @param string $string
     * @return TimeRange
     * @throws InvalidTimeRangeString
     */
    public static function fromString($string)
    {
        $times = explode('-', $string);

        if (count($times) !== 2) {
            throw InvalidTimeRangeString::forString($string);
        }

        return new self(Time::fromString($times[0]), Time::fromString($times[1]));
    }

    /**
     * @return Time
     */
    public function start()
    {
        return $this->start;
    }

    /**
     * @return Time
     */
    public function end()
    {
        return $this->end;
    }

    /**
     * @return bool
     */
    public function spillsOverToNextDay()
    {
        return $this->end->isBefore($this->start);
    }

    /**
     * @param Time $time
     * @return bool
     */
    public function containsTime(Time $time)
    {
        if ($this->spillsOverToNextDay()) {
            if ($time->isAfter($this->start)) {
                return $time->isAfter($this->end);
            }

            return $time->isBefore($this->end);
        }

        return $time->isSameOrAfter($this->start) && $time->isBefore($this->end);
    }

    /**
     * @param TimeRange $timeRange
     * @return bool
     */
    public function overlaps(TimeRange $timeRange)
    {
        return $this->containsTime($timeRange->start) || $this->containsTime($timeRange->end);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->start}-{$this->end}";
    }
}
