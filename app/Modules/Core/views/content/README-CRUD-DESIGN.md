# تصميم موحد لصفحات القوائم (CRUD Index)

لكل CRUD جديد نريد **نفس تصميم Tenants**:

## الهيكل

1. **أربعة كروت إحصائية** في الأعلى (`row g-4 mb-4`)، كل كرت: عنوان صغير، رقم، أيقونة في `avatar-initial rounded bg-label-*`.
2. **بطاقة Filters** (`card mb-4`):
   - هيدر: "Filters"
   - Body: Per Page (select)، زر Filters (يفتح offcanvas)، فاصل، Search (input + زر)، أزرار عرض List/Grid.
3. **Offcanvas** لفلتر جانبي (اختياري)، نفس اتجاه RTL/LTR.
4. **بطاقة الجدول** مع كلاس `crud-table`:
   - هيدر: عنوان + زر "Add …"
   - جدول مع أزرار Actions: عرض (أزرق)، تعديل (برتقالي)، حذف (أحمر).
   - Footer للـ pagination إن وجد.

## الاستخدام

- في الـ view أضف:
  ```blade
  @section('page-style')
  @include('core::_partials.crud-table-styles')
  @endsection
  ```
- على بطاقة الجدول استخدم كلاس: `card crud-table`.
- انسخ الهيكل من `tenants/index.blade.php` أو `roles/index.blade.php` أو `permissions/index.blade.php` وعدّل المحتوى فقط.

## الملف المشترك

- `_partials/crud-table-styles.blade.php`: أنماط أزرار الجدول (عرض / تعديل / حذف) لتجنب التكرار.
