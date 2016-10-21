<?php
namespace Edujugon\LaravelExtraFeatures\Traits;


use Carbon\Carbon;


/**
 * Class DateScopeTrait
 *
 * Provide extra features on year, month and day to retrieve data for Laravel Eloquent.
 *
 *
 * @package Edujugon\LaravelExtraFeatures\Traits
 */
trait DateScopesTrait {

    /**
     * Get items of passed year
     * If no year, returns Items of current year
     *
     * @param $query
     * @param $column
     * @param null $year
     * @return mixed
     */
    public function scopeYear($query,$column,$year = null)
    {
        $year = (!$year) ? Carbon::now()->year : $year;

        return $query->whereYear($column,$year);
    }

    /**
     * Get items of passed month
     * If no month, take current month
     * If no year, take current year
     *
     * @param $query
     * @param $column
     * @param null $month
     * @param null $year
     * @return mixed
     */
    public function scopeMonth($query,$column,$month = null,$year = null)
    {
        $month = (!$month) ? Carbon::now()->month : $month;
        $year = (!$year) ? Carbon::now()->year : $year;

        return $query->whereMonth($column, $month)
            ->whereYear($column,$year);
    }


    /**
     * Get Tattoos of passed day.
     * If no day, take current day
     * If no month, take current month
     * If no year, take current year
     *
     * @param $query
     * @param $column
     * @param null $day
     * @param null $month
     * @param null $year
     * @return mixed
     */
    public function scopeDay($query,$column,$day = null, $month = null, $year = null)
    {
        $day = (!$day) ? Carbon::now()->day : $day;
        $month = (!$month) ? Carbon::now()->month : $month;
        $year = (!$year) ? Carbon::now()->year : $year;

        return $query->whereDay($column,$day)
            ->whereMonth($column,$month)
            ->whereYear($column,$year);
    }
}