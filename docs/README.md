# Documentation

‏مستندات رسمی پروژه از این مسیر در Git نگهداری می‌شوند. از این به بعد Git مرجع اصلی نسخه‌بندی مستندات است و تغییرات باید همراه با Commit پروژه به‌روزرسانی شوند.

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

‏فایل‌ها با UTF-8 ذخیره می‌شوند. برای جلوگیری از به‌هم‌ریختگی متن‌های ترکیبی فارسی/انگلیسی، داخل متن فارسی از Unicode direction marks (`RLM`/`LRM`) استفاده شده است. این کاراکترها نامرئی هستند و نباید توسط Formatter یا Script حذف شوند.

‏نام فایل‌ها و پوشه‌ها انگلیسی، lowercase و kebab-case است. متن مستندات می‌تواند فارسی باشد و اصطلاحات فنی انگلیسی مطابق Domain حفظ می‌شوند.

‏هنگام Sync مجدد از Google Docs، ابتدا Export به Markdown انجام شود، سپس duplicate headingها و جهت متن normalize شود؛ فایل Export خام نباید مستقیم جایگزین نسخه Git شود.
