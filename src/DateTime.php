<?php

namespace Yoga;

class DateTime {

    const MYSQL_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \DateTime
     */
    private $phpDateTime;

    public function __construct($timestamp = null) {
        if (!$timestamp) {
            $timestamp = static::getCurrentTimestamp();
        }
        $this->phpDateTime = (new \DateTime)->setTimestamp($timestamp);
    }

    public function getTimestamp() {
        return $this->phpDateTime->getTimestamp();
    }

    public function getPhpDateTime() {
        return $this->phpDateTime;
    }

    public function format($format, $isServerTimezone = false) {
        if ($isServerTimezone) {
            $dt = clone $this->phpDateTime;
            $dt->setTimezone(new \DateTimeZone(self::$serverTimezone));

        } else {
            $dt = $this->phpDateTime;
        }
        $result = $dt->format($format);
        return substr($result, 0, strlen($result) - 2) . strtolower(substr($result, -2));
    }

    public function formatShort($isSecondsRequired = false, $isServerTimezone = false) {
        $format = 'd-M-Y h:i';
        if ($isSecondsRequired) {
            $format .= ':s';
        }
        $format .= 'A';
        $result = $this->format($format, $isServerTimezone);
        return substr($result, 0, strlen($result) - 2) . strtolower(substr($result, -2));
    }

    public function formatMysql() {
        return $this->format(self::MYSQL_FORMAT);
    }

    /**
     * @var string
     */
    static private $serverTimezone;

    static public function setUtc() {
        self::$serverTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    /**
     * @var int
     */
    static protected $frozenTimestamp;

    static public function freezeTime($timestamp) {
        self::$frozenTimestamp = $timestamp;
    }

    static private function getCurrentTimestamp() {
        return self::$frozenTimestamp ?: time();
    }

    public function getYear() {
        return (int)date('Y', $this->getTimestamp());
    }

    public function getMonth() {
        return (int)date('m', $this->getTimestamp());
    }

    public function getDay() {
        return (int)date('d', $this->getTimestamp());
    }

    public function getDayOfWeek() {
        return date('N', $this->getTimestamp());
    }

    public function getHour() {
        return (int)date('H', $this->getTimestamp());
    }

    public function getMinute() {
        return (int)date('i', $this->getTimestamp());
    }

    public function getSecond() {
        return (int)date('s', $this->getTimestamp());
    }

    public function getWeek() {
        $year = $this->getYear();
        $yearStart = static::createFromElements($year, 1, 1);
        $dayOfWeek = $yearStart->getDayOfWeek();
        $firstWeekStart = $yearStart->addDays(-($dayOfWeek - 1));
        $daysSinceFirstWeekStart = $firstWeekStart->getDiff($this, \Yoga\DateTime\DatePart::DAY);
        return (int)($daysSinceFirstWeekStart / 7) + 1;
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return DateTime
     */
    static public function createFromElements(
        $year,
        $month,
        $day,
        $hour = null,
        $minute = null,
        $second = null
    ) {
        return new static(mktime($hour, $minute, $second, $month, $day, $year));
    }

    /**
     * @param string $s
     * @return DateTime
     */
    static public function createFromMysql($s) {
        if (!$s) {
            return null;
        }
        if (!strpos($s, ' ')) {
            $s .= ' 00:00:00';
        }
        return new static(\DateTime::createFromFormat(self::MYSQL_FORMAT, $s)->getTimestamp());
    }

    /**
     * @param int $numDays
     * @return DateTime
     */
    public function addDays($numDays) {
        return $this->add($numDays, 'days');
    }

    /**
     * @param int $numHours
     * @return DateTime
     */
    public function addHours($numHours) {
        return $this->add($numHours, 'hours');
    }

    /**
     * @param int $numMinutes
     * @return DateTime
     */
    public function addMinutes($numMinutes) {
        return $this->add($numMinutes, 'minutes');
    }

    /**
     * @param int $numMonths
     * @return DateTime
     */
    public function addMonths($numMonths) {
        return $this->add($numMonths, 'months');
    }

    public function trimTime() {
        return static::createFromElements($this->getYear(), $this->getMonth(), $this->getDay());
    }

    /**
     * @param int $num
     * @param string $part
     * @return DateTime
     */
    private function add($num, $part) {
        $dt = \DateTime::createFromFormat('U', $this->getTimestamp());
        $diff = \DateInterval::createFromDateString(abs($num) . ' ' . $part);
        if ($num > 0) {
            $dt = $dt->add($diff);
        } else {
            $dt = $dt->sub($diff);
        }
        return new static($dt->getTimestamp());
    }

    /**
     * @param DateTime $toDt
     * @param \Yoga\DateTime\DatePart|int $datePart
     * @return int
     */
    public function getDiff(DateTime $toDt, $datePart) {
        if ($this->getTimestamp() > $toDt->getTimestamp()) {
            return -$toDt->getDiff($this, $datePart);
        }
        $datePart = \Yoga\DateTime\DatePart::wrap($datePart);

        $year1 = $this->getYear();
        $year2 = $toDt->getYear();
        $month1 = $this->getMonth();
        $month2 = $toDt->getMonth();
        $day1 = $this->getDay();
        $day2 = $toDt->getDay();
        $hour1 = $this->getHour();
        $hour2 = $toDt->getHour();
        $minute1 = $this->getMinute();
        $minute2 = $toDt->getMinute();
        $second1 = $this->getSecond();
        $second2 = $toDt->getSecond();

        $isPastSameDayTimeWithinMinute = ($second1 > $second2);
        $isPastSameDayTimeWithinHour = ($minute1 > $minute2)
            || ($minute1 == $minute2 && $isPastSameDayTimeWithinMinute);
        $isPastSameDayTimeWithinDay = ($hour1 > $hour2)
            || ($hour1 == $hour2 && $isPastSameDayTimeWithinHour);
        $isPastSameDayTimeWithinMonth = ($day1 > $day2)
            || ($day1 == $day2 && $isPastSameDayTimeWithinDay);
        $isPastSameDayTimeWithinYear = ($month1 > $month2)
            || ($month1 == $month2 && $isPastSameDayTimeWithinMonth);

        if (\Yoga\DateTime\DatePart::YEAR == $datePart->getValue()) {
            $result = $year2 - $year1;
            if ($isPastSameDayTimeWithinYear) {
                $result--;
            }
            return $result;
        }
        if (\Yoga\DateTime\DatePart::MONTH == $datePart->getValue()) {
            $result = ($year2 - $year1) * 12 + $month2 - $month1;
            if ($isPastSameDayTimeWithinMonth) {
                $result--;
            }
            return $result;
        }

        $days = 0;
        while ($year1 < $year2) {
            if (
                $month1 <= 2 && $this->isLeapYear($year1)
                || $month1 > 2 && $this->isLeapYear($year1 + 1)
            ) {
                $days += 366;
            } else {
                $days += 365;
            }
            $year1++;
        }
        while ($month1 < $month2) {
            $days += $this->getNumberOfDaysInMonth($year1, $month1);
            $month1++;
        }
        while ($month1 > $month2) {
            $month1--;
            $days -= $this->getNumberOfDaysInMonth($year1, $month1);
        }
        $days += ($day2 - $day1);

        if (\Yoga\DateTime\DatePart::DAY == $datePart->getValue()) {
            $result = $days;
            if ($isPastSameDayTimeWithinDay) {
                $result--;
            }
            return $result;
        }

        $seconds = $days * 86400 + ($hour2 - $hour1) * 3600
            + ($minute2 - $minute1) * 60
            + ($second2 - $second1);
        if (\Yoga\DateTime\DatePart::SECOND == $datePart->getValue()) {
            return $seconds;
        }
        if (\Yoga\DateTime\DatePart::MINUTE == $datePart->getValue()) {
            return (int)($seconds / 60);
        }
        if (\Yoga\DateTime\DatePart::HOUR == $datePart->getValue()) {
            return (int)($seconds / 3600);
        }
    }

    /**
     * @param int $year
     * @return boolean
     */
    static public function isLeapYear($year) {
        return ($year % 4 == 0) and (($year % 400 == 0) or ($year % 100 <> 0));
    }

    /**
     * @param int $year
     * @param int $month
     * @return int
     */
    static public function getNumberOfDaysInMonth($year, $month) {
        switch ($month) {
            case 1:
                return 31;
            case 2:
                if (static::isLeapYear($year)) {
                    return 29;
                } else {
                    return 28;
                }
            case 3:
                return 31;
            case 4:
                return 30;
            case 5:
                return 31;
            case 6:
                return 30;
            case 7:
                return 31;
            case 8:
                return 31;
            case 9:
                return 30;
            case 10:
                return 31;
            case 11:
                return 30;
            case 12:
                return 31;
        }
    }

    static public function getWeekLimits($year, $week) {
        $yearStart = static::createFromElements($year, 1, 1);
        $yearStartDayOfWeek = $yearStart->getDayOfWeek();
        $weekStart = $yearStart->addDays(($week - 1) * 7 - ($yearStartDayOfWeek - 1));
        return (new \Yoga\DateTime\DateRange)
            ->setStart($weekStart)
            ->setFinish($weekStart->addDays(6));
    }

    static public function secondsToWords($seconds, $isFullWords = false) {
        $strings = Strings::service();
        $hours = intval(intval($seconds) / 3600);
        $result = $hours ? $strings->ending($hours, $isFullWords ? 'hour' : 'hr') : '';
        $minutes = bcmod((intval($seconds) / 60), 60);
        if ($hours > 0 || $minutes > 0) {
            if ($result) {
                $result .= ' ';
            }
            if ($isFullWords) {
                $result .= $strings->ending($minutes, 'minute');
            } else {
                $result .= $minutes . ' min';
            }
        }
        $seconds = bcmod(intval($seconds), 60);
        if ($seconds) {
            if ($result) {
                $result .= ' ';
            }
            if ($isFullWords) {
                $result .= $strings->ending($seconds, 'second');
            } else {
                $result .= $seconds . ' sec';
            }
        }
        return $result;
    }

}