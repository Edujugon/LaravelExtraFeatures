# Laravel Extra Features

This package provides useful features to give extra functionality to your Laravel project.

## Installation

type in console:
```
composer require edujugon/laravel-extra-features
```

## Laravel 5.*

Register the package service by adding it to the providers array.

> IMPORTANT! Ensure you put that line at the end of the providers list.

```
'providers' => array(
    ...
    Edujugon\LaravelExtraFeatures\Providers\LaravelExtraFeaturesServiceProvider::class
)
```

Publish the package's configuration file to the application's own config directory

```
php artisan vendor:publish  --tag=ExtraFeaturesConfig
```

The above line will create a file called `extrafeatures.php` under config folder. 

## Feature List

**Functionality**

*   [Redirect when No Page Found](https://github.com/edujugon/LaravelExtraFeatures#redirect-no-page-found)
*   [Carbon Locale](https://github.com/edujugon/LaravelExtraFeatures#carbon-locale)

**Traits**

*   [DateScopesTrait](https://github.com/edujugon/LaravelExtraFeatures#datescopestrait)
   


### Redirect No Page Found

When user tries to load an unknown url, it's redirected to a specific known url.

> !IMPORTANT, This feature won't work for local environment. That's an intended behaviour no to hide any redirect/request error.

* Go to `config/extrafeatures.php` file and update the value for the key `REDIRECT_NO_PAGE_FOUND`.

### Carbon Locale

Carbon language is set based on the laravel's app locale. By default it is enabled. You may disable changing `CARBON_LOCALE` value to `false`in `config/extrafeatures.php` file.


### DateScopesTrait

Provide extra features on year, month and day to retrieve data for Laravel Eloquent.

> Adding $dateColumn as model class's property you don't need to pass the $dateColumn parameter to the methods.

```php
    /**
     * Get items of passed year
     * If no year, returns Items of current year
     *
     * @param $query
     * @param $column
     * @param null $year
     * @return mixed
     */
    public function scopeYear($query,$dateColumn = null,$year = null)
```

```php
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
    public function scopeMonth($query,$dateColumn = null,$month = null,$year = null)
```

```php
    /**
     * Get items of passed day.
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
    public function scopeDay($query,$dateColumn = null,$day = null, $month = null, $year = null)
```