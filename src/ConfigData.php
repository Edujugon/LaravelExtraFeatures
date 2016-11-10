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
            }
        }else
            $config =  include(__DIR__ . '/Config/config.php');

        if(array_key_exists($key,$config))
            return $config[$key];

        return null;
    }
}