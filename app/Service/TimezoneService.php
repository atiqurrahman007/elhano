<?php

namespace App\Service;

use Carbon\Carbon;

class TimezoneService
{
    /**
     * Parse a date string from Asia/Dhaka local timezone to UTC datetime string.
     *
     * @param string|null $dateStr
     * @param bool $isEndOfDay
     * @return string|null
     */
    public static function parseLocalToUtc($dateStr, $isEndOfDay = false)
    {
        if (empty($dateStr)) {
            return null;
        }

        try {
            $carbon = Carbon::parse($dateStr, 'Asia/Dhaka');
            if ($isEndOfDay && strlen($dateStr) <= 10) {
                $carbon = $carbon->endOfDay();
            }
            return $carbon->setTimezone('UTC')->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
