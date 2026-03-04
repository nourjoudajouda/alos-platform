# تنسيق العمل مع Trello — ALOS

رابط البورد: [alos](https://trello.com/b/hCWzerUJ/alos)

## ربط التاسكات (هان ↔ Trello)

| Trello Card | الحالة | الشغل هان (ملفات / خطوات) |
|-------------|--------|---------------------------|
| **ALOS-S0-03** — Create Modular Monolith Structure | ✅ تم | هيكل كامل: `Http/Controllers`, `Http/Requests`, `Models`, `Routes`, `Views`, `Providers/CoreServiceProvider`, `Module.php`؛ تحميل الرووتات والـ views من الـ provider |
| **ALOS-S0-08** — Implement Internal Authentication (Office Users) | ✅ تم | Login (POST + session)، Logout، حماية `/` و `/page-2` بـ `auth`، إزالة Jetstream من الـ navbar |
| (بدون كارد) Seeders | ✅ تم | `AdminSeeder`: admin@alos.local + test@example.com بكلمة مرور `password`، استدعاءه من `DatabaseSeeder` |

## تشغيل الـ Seeders

```bash
php artisan db:seed
# أو فقط أدمن:
php artisan db:seed --class=AdminSeeder
```

**دخول بعد الـ seed:**  
- Email: `admin@alos.local` أو `test@example.com`  
- Password: `password`

## هيكل الموديول (التقسيمة الكاملة)

كل موديول تحت `app/Modules/ModuleName/`:

```
ModuleName/
├── Http/
│   ├── Controllers/
│   │   └── DashboardController.php  (أو أي كونترولر)
│   └── Requests/
├── Models/
├── Routes/
│   └── web.php
├── Views/
│   └── dashboard.blade.php
├── Providers/
│   └── ModuleNameServiceProvider.php   ← يحمّل الرووتات ويسجّل الـ view namespace
└── Module.php
```

- **الرووتات:** كل موديول يحمّل رووتاته من خلال الـ `ServiceProvider` الخاص فيه (مثلاً `CoreServiceProvider`).
- **الـ Views:** مسار المجلد `Views/` (حرف V كبير). الاستخدام: `view('core::dashboard')` → `app/Modules/Core/Views/dashboard.blade.php`
- **تسجيل الموديول:** إضافة `ModuleNameServiceProvider` في `bootstrap/providers.php`.

**Core مثال:** بعد تسجيل الدخول: `/core/dashboard` تعرض صفحة الداشبورد من موديول Core. و `/module-core` ترجع JSON للتحقق من الهيكل.

**نقل Vuexy إلى Core:** كل views الفوكسي (layouts، content، _partials) نُقلت إلى `app/Modules/Core/Views/` مع استبدال كل المسارات إلى `core::`. الكونترولرات تستخدم الآن `view('core::content....')` و `view('core::layouts....')`. يمكنك حذف محتويات `resources/views` القديمة (layouts، content، _partials) بعد التأكد أن التطبيق يعمل؛ أو تركها كنسخة احتياطية.

---

## ملاحظات

- أي تاسك نخلصها هان نحدّث وضعها على التريلو (مثلاً من Doing → QA أو Done).
- لو فتحت كارد جديد على التريلو (مثلاً للـ Seeders) نضيفه فوق ونربطه بالشغل اللي نعمله.

---
*آخر تحديث: بعد تنفيذ ALOS-S0-03، Login، و Seeders.*
