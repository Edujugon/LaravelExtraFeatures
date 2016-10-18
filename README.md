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
php artisan vendor:publish  --tag=ExtraFeaturesonfig
```

The above line will create a file called `extrafeatures.php` under config folder. 

## Feature List

*   [Redirect when No Page Found](https://github.com/edujugon/LaravelExtraFeatures#redirect-no-page-found)
   


### Redirect No Page Found

When user tries to load an unknown url, it's redirected to a specific known url.

* Go to `config/extrafeatures.php` file and update the value for the key `REDIRECT_NO_PAGE_FOUND`.