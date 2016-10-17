# Laravel Extra Features

This package provides useful features to give extra functionality to your Laravel project.

## Installation

type in console:
```
composer require edujugon/laravelextrafeatures
```

## Laravel 5.*

Register the package service by adding it to the providers array.

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

*   [Redirect when No Page Found](https://github.com/edujugon/LaravelExtraFeatures#redirect-no-page-found)
   


### Redirect No Page Found

When user tries to load an unknown url, it's redirected to a specific known url.