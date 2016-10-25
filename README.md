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

**Traits**

*   [DateScopesTrait](https://github.com/edujugon/LaravelExtraFeatures#datescopestrait)
   


### Redirect No Page Found

When user tries to load an unknown url, it's redirected to a specific known url.

* Go to `config/extrafeatures.php` file and update the value for the key `REDIRECT_NO_PAGE_FOUND`.


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
    public function scopeYear($query,$column,$year = null)
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
    public function scopeMonth($query,$column,$month = null,$year = null)
```

```php
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
```