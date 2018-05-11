<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd98a59f87ceeb6fedb9cb9c3339b9c93
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
        'C' => 
        array (
            'CorsSlim\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/Twilio',
        ),
        'CorsSlim\\' => 
        array (
            0 => __DIR__ . '/..' . '/palanik/corsslim',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Slim' => 
            array (
                0 => __DIR__ . '/..' . '/slim/slim',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd98a59f87ceeb6fedb9cb9c3339b9c93::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd98a59f87ceeb6fedb9cb9c3339b9c93::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitd98a59f87ceeb6fedb9cb9c3339b9c93::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}