<?php

namespace Edujugon\LaravelExtraFeatures;


class ConfigData
{

    public static function getValue($key)
    {
        if(function_exists('config_path'))
        {
            if(file_exists(config_path('extrafeatures.php')))
            {
                $config = include(config_path('extrafeatures.php'));
                return $config[$key];
            }
        }

        $config =  include(__DIR__ . '/Config/config.php');
        return $config[$key];
    }
}