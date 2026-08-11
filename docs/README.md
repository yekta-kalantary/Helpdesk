# Documentation

&rlm;مستندات رسمی پروژه از این مسیر در Git نگهداری می‌شوند. از این به بعد Git مرجع اصلی نسخه‌بندی مستندات است و تغییرات باید همراه با Commit پروژه به‌روزرسانی شوند.

## Structure

```text
docs/
├── README.md
├── product/
│   └── client-task-management-mvp.md
└── research/
    ├── rise-crm-product-analysis.md
    └── worksuite-product-analysis.md
```

## Product

- [Client Task Management MVP — PRD](product/client-task-management-mvp.md)

## Research & References

- [RISE CRM — Product Analysis](research/rise-crm-product-analysis.md)
- [Worksuite — Product Analysis](research/worksuite-product-analysis.md)

## Writing & Direction Rules

&rlm;فایل‌ها با UTF-8 ذخیره می‌شوند. برای جلوگیری از به‌هم‌ریختگی متن‌های ترکیبی فارسی/انگلیسی، داخل متن فارسی از HTML direction entities (`&rlm;` / `&lrm;`) استفاده شده است. این entityها در خروجی دیده نمی‌شوند و نباید توسط Formatter یا Script حذف شوند.

&rlm;نام فایل‌ها و پوشه‌ها انگلیسی، lowercase و kebab-case است. متن مستندات می‌تواند فارسی باشد و اصطلاحات فنی انگلیسی مطابق Domain حفظ می‌شوند.

&rlm;هنگام Sync مجدد از Google Docs، ابتدا Export به Markdown انجام شود، سپس duplicate headingها و جهت متن normalize شود؛ فایل Export خام نباید مستقیم جایگزین نسخه Git شود.
