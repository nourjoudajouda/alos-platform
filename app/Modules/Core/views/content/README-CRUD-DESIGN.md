# تصميم موحد لصفحات القوائم (CRUD Index)

**استخدم هذا الهيكل في كل CRUD جديد بدون تكرار الفلاتر والكروت.**

## الطريقة القياسية

1. **امتداد الـ layout:**  
   استخدم `@extends('core::layouts.crud-index-layout')` بدل `layoutMaster`.

2. **تعريف المتغيرات في أول الملف:**
   - `$crudIndexId` — معرف فريد (مثلاً `tenants`, `roles`, `users`)
   - `$crudIndexTitle` — عنوان الصفحة
   - `$crudIndexFiltersAction` — رابط الـ action لفورم الفلاتر
   - `$crudIndexPerPage` — قيمة "لكل صفحة" الحالية
   - `$crudIndexTableTitle` — عنوان بطاقة الجدول
   - `$crudIndexAddUrl` و `$crudIndexAddLabel` — زر الإضافة
   - `$crudIndexEmptyMessage`, `$crudIndexEmptyLink`, `$crudIndexEmptyLinkText` — رسالة وعرض الفراغ
   - `$crudIndexShowViewToggle` — true/false لعرض أزرار List/Grid
   - `$items` — الـ paginator (مثلاً `$tenants`, `$roles`) لظهور الـ pagination

3. **تعريف الـ sections:**
   - **crud_stats** (اختياري) — صف الكروت الإحصائية. استخدم `@include('core::_partials.crud-stat-card', ['title' => ..., 'value' => ..., 'subtitle' => ..., 'icon' => 'ti tabler-...', 'bgLabel' => 'primary'])` لكل كرت.
   - **crud_filters_hidden_inputs** (اختياري) — حقول hidden إضافية داخل فورم الفلاتر.
   - **crud_offcanvas** (اختياري) — محتوى الفلتر الجانبي (فورم + أزرار). إن وُجد يظهر زر "Filters" وفتح الـ offcanvas.
   - **crud_table_header** — عناوين أعمدة الجدول `<th>...</th>`.
   - **crud_table_body** — `@forelse(...)` مع صفوف الجدول وصف الفراغ `@empty`.
   - **crud_extra_script** (اختياري) — سكربت إضافي (مثلاً submit عند تغيير فلتر في الـ offcanvas).

## الكرت الإحصائي

استخدم الـ partial لكرت واحد:

```blade
@include('core::_partials.crud-stat-card', [
  'title' => __('Total Items'),
  'value' => $totalItems,
  'subtitle' => __('Items'),
  'icon' => 'ti tabler-icon-name',
  'bgLabel' => 'primary'  // primary, success, info, secondary, warning, danger
])
```

## ما يوفره الـ layout تلقائياً

- كروت الإحصاء (إن وُجد قسم `crud_stats`)
- بطاقة Filters: Per Page، زر Filters (إن وُجد `crud_offcanvas`)، Search، أزرار List/Grid (إن `$crudIndexShowViewToggle` true)
- Offcanvas للفلتر الجانبي (إن وُجد `crud_offcanvas`)
- بطاقة الجدول مع عنوان وزر الإضافة وروابط الـ pagination
- رسائل success/error
- سكربت ربط Per Page وزر Filters

## أمثلة

- **Tenants:** `core::content.tenants.index`
- **Roles:** `core::content.roles.index`
- **Permissions:** `core::content.permissions.index`
- **Internal Users:** `identity::content.users.index`

أي CRUD index جديد: انسخ أحد هذه الملفات وعدّل المتغيرات ومحتوى الـ sections فقط.
