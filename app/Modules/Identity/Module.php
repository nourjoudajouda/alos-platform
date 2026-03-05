<?php

namespace App\Modules\Identity;

/**
 * ALOS-S1-05 — Identity Module: إدارة مستخدمي المكتب (Internal Users).
 */
final class Module
{
    public const NAME = 'Identity';

    public static function routesPath(): string
    {
        return __DIR__ . '/Routes/web.php';
    }

    public static function viewsPath(): string
    {
        return __DIR__ . '/views';
    }

    public static function viewsNamespace(): string
    {
        return 'identity';
    }

    /** أدوار مستخدمي المكتب فقط (للعرض في فورم إنشاء/تعديل مستخدم) */
    public static function internalRoleNames(): array
    {
        return ['admin', 'managing_partner', 'lawyer', 'assistant', 'finance'];
    }
}
