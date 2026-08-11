<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# Research & References

&rlm;این پوشه مرجع تحقیق محصول برای تصمیم‌های Product و Engineering است. فایل‌های `*-product-analysis.md` تحلیل پایه و پایدار هر محصول هستند؛ فایل‌های `*-version-evolution-*.md` تاریخچه تحلیلی تکامل Domain/Feature را نگه می‌دارند؛ فایل‌های `*-revalidation-YYYY-MM-DD.md` وضعیت فعلی و Deltaهای منابع زنده در همان تاریخ را ثبت می‌کنند.

## &rlm;Rule for future implementation work

&rlm;قبل از طراحی یا کدنویسی Featureهایی که به Client، Project، Task، Permission، Collaboration، Ticket، Notification، File یا Workflow مربوط‌اند، باید به‌ترتیب این منابع خوانده شوند:

1. [Client Task Management MVP — PRD](../product/client-task-management-mvp.md)
2. Base research مربوط به محصول مرجع
3. اگر تصمیم به تاریخچه یا دلیل تکامل یک قابلیت وابسته است، Version Evolution مربوطه
4. جدیدترین Re-validation همان محصول

&rlm;Research مرجع تصمیم است، اما Scope نهایی همیشه از PRD می‌آید. وجود Feature در RISE یا Worksuite به‌تنهایی مجوز ورود آن به MVP نیست.

## RISE CRM

- [Base Product Analysis](rise-crm-product-analysis.md)
- [Version Evolution — 3.0 → 4.0](rise-crm-version-evolution-3-to-4.md)
- [Live Re-validation — 2026-08-11](rise-crm-revalidation-2026-08-11.md)

&rlm;برای Featureهای Project/Task، Client/Contact، Permission و Collaboration، سند Version Evolution کمک می‌کند مشخص شود یک قابلیت Pattern پایدار محصول است یا Feature توسعه‌یافته‌ای که در نسخه‌های بعدی به Full Product اضافه شده است.

## Worksuite

- [Base Product Analysis](worksuite-product-analysis.md)
- [Live Re-validation — 2026-08-11](worksuite-revalidation-2026-08-11.md)

## &rlm;Source hierarchy

&rlm;برای اختلاف بین منابع، ترتیب اعتبار به این شکل است:

1. Official product documentation / release notes
2. Official CodeCanyon item page
3. Official demo behavior when independently observable
4. Engineering inference — باید صریحاً به‌عنوان inference علامت‌گذاری شود

&rlm;قیمت، تعداد فروش، Rating و سایر Market metrics داده‌های ناپایدار هستند و نباید Requirement محصول از آن‌ها استخراج شود.

## &rlm;Maintenance rule

&rlm;Base Analysis فقط وقتی تغییر می‌کند که مفهوم Domain یا Requirement پایه اشتباه/منسوخ شده باشد. تاریخچه Featureها و تغییر مدل محصول در Version Evolution نگه داشته می‌شود. وضعیت فعلی، Featureهای تازه و اصلاحات منبع در Re-validation تاریخ‌دار ثبت می‌شوند. این تفکیک History تحقیق را در Git قابل Audit نگه می‌دارد بدون اینکه Base Research یا Current-state document به Changelog dump تبدیل شوند.
