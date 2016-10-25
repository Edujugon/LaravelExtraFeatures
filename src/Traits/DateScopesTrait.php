<?php
namespace Edujugon\LaravelExtraFeatures\Traits;


use Carbon\Carbon;


/**
 * Class DateScopeTrait
 *
 * Provide extra features on year, month and day to retrieve data for Laravel Eloquent.
 *
 * OPTIONS:
 *  Adding $dateColumn as model class's property you don't need to pass the $dateColumn parameter to the methods.
 *
 * @package Edujugon\LaravelExtraFeatures\Traits
 */
trait DateScopesTrait {

    /**
     * Get items of passed year
     * If no year, returns Items of current year
     *
     * @param $query
     * @param $dateColumn
     * @param null $year
     * @return mixed
     */
    public function scopeYear($query,$dateColumn = null,$year = null)
    {
        if(!isset($dateColumn) && isset($this->dateColumn)) $dateColumn = $this->dateColumn;

        $year = (!$year) ? Carbon::now()->year : $year;

        return $query->whereYear($dateColumn,$year);
    }

    /**
     * Get items of passed month
     * If no month, take current month
     * If no year, take current year
     *
     * @param $query
     * @param $dateColumn
     * @param null $month
     * @param null $year
     * @return mixed
     */
    public function scopeMonth($query,$dateColumn = null,$month = null,$year = null)
    {
        if(!isset($dateColumn) && isset($this->dateColumn)) $dateColumn = $this->dateColumn;

        $month = (!$month) ? Carbon::now()->month : $month;
        $year = (!$year) ? Carbon::now()->year : $year;

        return $query->whereMonth($dateColumn, $month)
            ->whereYear($dateColumn,$year);
    }


    /**
     * Get Tattoos of passed day.
     * If no day, take current day
     * If no month, take current month
     * If no year, take current year
     *
     * @param $query
     * @param $dateColumn
     * @param null $day
     * @param null $month
     * @param null $year
     * @return mixed
     */
    public function scopeDay($query,$dateColumn = null,$day = null, $month = null, $year = null)
    {
        if(!isset($dateColumn) && isset($this->dateColumn)) $dateColumn = $this->dateColumn;

        $day = (!$day) ? Carbon::now()->day : $day;
        $month = (!$month) ? Carbon::now()->month : $month;
        $year = (!$year) ? Carbon::now()->year : $year;

        return $query->whereDay($dateColumn,$day)
            ->whereMonth($dateColumn,$month)
            ->whereYear($dateColumn,$year);
    }
}