<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2ae853dab52e709a192ca3625ba8e2c3
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Corsinvest\\ProxmoxVE\\Api\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Corsinvest\\ProxmoxVE\\Api\\' => 
        array (
            0 => __DIR__ . '/..' . '/corsinvest/cv4pve-api-php/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2ae853dab52e709a192ca3625ba8e2c3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2ae853dab52e709a192ca3625ba8e2c3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2ae853dab52e709a192ca3625ba8e2c3::$classMap;

        }, null, ClassLoader::class);
    }
}