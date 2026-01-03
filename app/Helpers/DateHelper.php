<?php

namespace App\Helpers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\App;

class DateHelper
{
    /**
     * @param $date
     * @param string $format
     * @return string
     */
    public static function parseDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            if (!$date) {
                return '';
            }

            return Carbon::createFromFormat($format, $date)->diffForHumans();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $date
     * @return string
     */
    public static function formattedDate($date, $isBirthday = false): string
    {
        if (!$date) {
            return '';
        }
        if (App::getLocale() == 'jp') {
            if ($isBirthday) {
                return Carbon::parse($date . '/01')->format('Y年m月');
            }
            return Carbon::parse($date)->format('Y年m月d日');
        }
        return Carbon::parse($date)->toFormattedDateString();
    }

    /**
     * parseOnlyDate
     *
     * @param  string | null $format
     * @return string
     */
    public static function parseOnlyDate($date, ?string $format = 'Y/m/d'): string
    {
        if (!$date) {
            return '';
        }

        return Carbon::parse($date)->format($format);
    }

    /**
     * parseOnlyHI
     *
     * @param  mixed $date
     * @return string
     */
    public static function parseOnlyHI(mixed $date): string
    {
        if (!$date) {
            return '';
        }

        return Carbon::parse($date)->format('H:i');
    }

    /**
     * parseDateBe
     *
     * @param  mixed $date
     * @param  mixed $format
     * @return string
     */
    public static function parseDateBe(mixed $date, string $format = 'Y-m-d H:i:s'): string | null
    {
        try {
            if (!$date) {
                return null;
            }
            return Carbon::parse($date)->format($format);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * parseDateMonth
     *
     * @param  mixed $date
     * @return string
     */
    public static function parseDateMonth(mixed $date): string
    {
        if (!$date) {
            return '';
        }

        return Carbon::parse($date)->format('Y/n/j');
    }

    /**
     * addDay
     *
     * @param  string $startDate
     * @param  int $number
     * @return string
     */
    public static function addDay(string $startDate, int $number)
    {
        return self::parseOnlyDate(Carbon::parse($startDate)->addDays($number));
    }

    /**
     * checkDate
     *
     * @param  string | null $date
     * @param  string $format
     * @return string | null
     */
    public static function checkDate(string | null $date, string $format = 'Y-m-d'): string | null
    {
        if (!strtotime($date)) {
            return null;
        }
        return Carbon::parse($date)->format($format);
    }

    /**
     * checkDateTime
     *
     * @param  string | null $date
     * @param  string | null $time
     * @param  string $format
     * @return string | null
     */
    public static function checkDateTime(string | null $date, string | null $time, string $format = 'Y-m-d H:i:s'): string | null
    {
        if (!strtotime($date)) {
            return null;
        }

        return Carbon::parse($date . (!empty($time) ? ' ' . $time : ''))->format($format);
    }
}
