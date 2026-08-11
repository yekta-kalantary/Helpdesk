# Documentation

&rlm;مستندات رسمی پروژه از این مسیر در Git نگهداری می‌شوند. Git مرجع اصلی نسخه‌بندی مستندات است و تغییرات باید همراه با Commit پروژه به‌روزرسانی شوند.

## Structure

```text
docs/
├── README.md
├── product/
│   └── client-task-management-mvp.md
└── research/
    ├── README.md
    ├── rise-crm-product-analysis.md
    ├── rise-crm-revalidation-2026-08-11.md
    ├── worksuite-product-analysis.md
    └── worksuite-revalidation-2026-08-11.md
```

## Product

- [Client Task Management MVP — PRD](product/client-task-management-mvp.md)

## Research & References

- [Research Index & Usage Rules](research/README.md)
- [RISE CRM — Base Product Analysis](research/rise-crm-product-analysis.md)
- [RISE CRM — Live Re-validation — 2026-08-11](research/rise-crm-revalidation-2026-08-11.md)
- [Worksuite — Base Product Analysis](research/worksuite-product-analysis.md)
- [Worksuite — Live Re-validation — 2026-08-11](research/worksuite-revalidation-2026-08-11.md)

&rlm;برای تصمیم Product/Engineering، Base Analysis باید همراه با جدیدترین Re-validation خوانده شود. Scope نهایی همیشه از PRD می‌آید و Featureهای محصولات مرجع به‌صورت خودکار وارد MVP نمی‌شوند.

## Writing & Direction Rules

&rlm;فایل‌ها با UTF-8 ذخیره می‌شوند. برای جلوگیری از به‌هم‌ریختگی متن‌های ترکیبی فارسی/انگلیسی، داخل متن فارسی از HTML direction entities (`&rlm;` / `&lrm;`) استفاده شده است. این entityها در خروجی دیده نمی‌شوند و نباید توسط Formatter یا Script حذف شوند.

&rlm;نام فایل‌ها و پوشه‌ها انگلیسی، lowercase و kebab-case است. متن مستندات می‌تواند فارسی باشد و اصطلاحات فنی انگلیسی مطابق Domain حفظ می‌شوند.

&rlm;در Sync یا Re-validation بعدی، Export خام منبع نباید مستقیم جایگزین نسخه Git شود. Source-backed Delta باید با تاریخ مشخص ثبت و از Engineering Inference تفکیک شود.
