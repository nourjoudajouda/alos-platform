<?php

namespace App\Modules\Core;

/**
 * ALOS-S0-03 — Core Module descriptor.
 * كل موديول يحدد: routes path، views path، و view namespace.
 */
final class Module
{
    public const NAME = 'Core';

    public static function routesPath(): string
    {
        return __DIR__ . '/Routes/web.php';
    }

    public static function viewsPath(): string
    {
        return __DIR__ . '/Views';
    }

    /** اسم الـ namespace للـ views: استخدم view('core::اسم-الملف') */
    public static function viewsNamespace(): string
    {
        return 'core';
    }
}
