<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# Jira Cloud — Live Re-validation — 2026-08-13

## &rlm;هدف

&rlm;این سند Snapshot وضعیت Jira Cloud / Jira Software در تاریخ **2026-08-13** است و Deltaها و Caveatهای ناپایداری را ثبت می‌کند که نباید Base Analysis را به Changelog تبدیل کنند. Scope همچنان فقط Jira Cloud / Jira Software است و Jira Service Management خارج از Scope باقی می‌ماند.

## &rlm;01 — Terminology rollout

### &rlm;وضعیت تأییدشده

&rlm;Atlassian در Jira Cloud Terminology را از `Project` به `Space` و از `Issue` به `Work item` تغییر داده است. Documentation فعلی صفحات اصلی Space و Work Item را با واژه‌های جدید نمایش می‌دهد.

&rlm;این rollout هنوز در همه Contractها یکدست نیست. Atlassian در مستند Board Filter/JQL صریحاً اعلام می‌کند که بعضی ورودی‌های JQL با Terminology جدید ممکن است هنوز کار نکنند و در این حالت باید Legacy term استفاده شود. Queryهای موجود نیز تغییر نمی‌کنند.

### &rlm;اثر روی Research

- &rlm;در Base Analysis از `Space`، `Work item` و `Work type` به‌عنوان واژگان فعلی استفاده می‌شود.
- &rlm;در Search/API/JQL، Legacy aliasها باید تا زمانی که Atlassian rollout را کامل نکرده قابل تشخیص باشند.
- &rlm;این تغییر نام نباید به‌عنوان دلیل Rename کردن Domain Entityهای Helpdesk تلقی شود.

### &rlm;منابع

- https://support.atlassian.com/jira-software-cloud/docs/what-is-a-jira-software-project/
- https://support.atlassian.com/jira-software-cloud/docs/what-is-a-work-item/
- https://support.atlassian.com/jira-software-cloud/docs/example-jql-queries-for-board-filters/

---

## &rlm;02 — Team-managed و Company-managed همچنان دو مدل متفاوت‌اند

### &rlm;وضعیت تأییدشده

&rlm;Team-managed spaces همچنان بر Configuration محلی و کنترل تیم تمرکز دارند. Company-managed spaces برای Configuration استاندارد و اشتراکی مثل Schemeها مناسب‌اند. بسیاری از قابلیت‌های Administration و Board behavior بین این دو مدل مسیر تنظیم متفاوت دارند.

### &rlm;اثر روی Research

&rlm;هر Requirement آینده‌ای که از Jira استخراج می‌شود باید مشخص کند آیا Pattern موردنظر local/project-scoped است یا shared/admin-scoped. اضافه‌کردن Shared Scheme به Helpdesk نباید صرفاً از وجود Company-managed spaces نتیجه‌گیری شود.

### &rlm;منبع

- https://support.atlassian.com/jira-software-cloud/docs/what-is-a-jira-software-project/

---

## &rlm;03 — Field Scheme rollout و limits سال 2026

### &rlm;وضعیت تأییدشده

&rlm;Atlassian در حال جایگزینی Field Configuration و Field Configuration Scheme با تجربه یکپارچه‌تر `Field schemes` است. Rollout progressive است؛ بنابراین بعضی Siteها ممکن است هنوز تجربه قدیمی را ببینند.

&rlm;در مستند Work Type Scheme، Atlassian محدودیت‌های 2026 را برای Reliability/Performance ثبت کرده است: Field configuration تا **700 fields** و Work Type Scheme تا **150 work types** محدود می‌شود.

### &rlm;اثر روی Research

- &rlm;Base Pattern پایدار این است که Field definition، availability/requirement و context/options concernهای جدا هستند.
- &rlm;نام دقیق UI/Scheme implementation ناپایدار است و نباید در Domain طراحی Helpdesk کپی شود.
- &rlm;اعداد limit صرفاً Current-state operational detail هستند و Requirement محصول ما محسوب نمی‌شوند.

### &rlm;منابع

- https://support.atlassian.com/jira-cloud-administration/docs/what-are-field-schemes/
- https://support.atlassian.com/jira-cloud-administration/docs/what-are-issue-type-schemes/

---

## &rlm;04 — Permission و Work-item Security caveat

### &rlm;وضعیت تأییدشده

&rlm;Permission Scheme capabilityهای داخل Space را کنترل می‌کند؛ Work Item Security Scheme visibility رکورد خاص را محدود می‌کند. Atlassian همچنین اعلام کرده Space permission schemes، Space roles و Work Item Security Schemes در Free Jira site در دسترس نیستند.

### &rlm;اثر روی Research

&rlm;جداسازی Capability و Record Visibility یک Pattern پایدار است؛ اما Feature parity و edition behavior Jira نباید Requirement مستقیم Helpdesk شود.

### &rlm;منابع

- https://support.atlassian.com/jira-cloud-administration/docs/what-are-permission-schemes-in-jira/
- https://support.atlassian.com/jira-cloud-administration/docs/configure-issue-security-schemes/

---

## &rlm;05 — Advanced Plans edition boundary

### &rlm;وضعیت تأییدشده

&rlm;Advanced planning / Plans همچنان Feature مربوط به Jira Cloud **Premium و Enterprise** است. این لایه برای Planning چندSpace/چندTeam و Release tracking گسترده‌تر استفاده می‌شود و با Timeline داخل یک Space یک سطح پیچیدگی ندارد.

### &rlm;اثر روی Research

&rlm;برای Helpdesk، Timeline ساده و Project-local باید از Portfolio/Advanced Planning جدا دیده شود. وجود Plans در Jira دلیل ساخت Multi-project planning در نسخه‌های نزدیک نیست.

### &rlm;منابع

- https://support.atlassian.com/jira-software-cloud/docs/track-releases-from-your-timeline/
- https://support.atlassian.com/jira-software-cloud/docs/change-estimation-units-in-your-plan/

---

## &rlm;06 — Components scope

### &rlm;وضعیت تأییدشده

&rlm;Jira Components در Documentation فعلی فقط برای **Company-managed spaces** معرفی شده‌اند. Component می‌تواند Feature/Department/Workstream را گروه‌بندی کند، Owner داشته باشد و Auto-assignment را پشتیبانی کند.

### &rlm;اثر روی Research

&rlm;Component یک taxonomy ساده سراسری نیست؛ در Jira به Space و configuration model وابسته است. برای Helpdesk، Label یا Category تا قبل از وجود Workstream ownership واقعی ساده‌تر و مناسب‌تر است.

### &rlm;منبع

- https://support.atlassian.com/jira-software-cloud/docs/what-are-jira-components/

---

## &rlm;07 — Forms به Work Item تبدیل می‌شوند

### &rlm;وضعیت تأییدشده

&rlm;Forms در Jira Cloud برای Work Intake استفاده می‌شوند و Submission یک Work Item در همان Space ایجاد می‌کند. Form هنگام ساخت به Work Type متصل می‌شود. Space admin نقش اصلی در ایجاد/ویرایش Form دارد.

### &rlm;اثر روی Research

&rlm;اگر Helpdesk بعداً Client Request Form اضافه کند، بهتر است Form یک Intake layer باشد که به Task/Work Item موجود map می‌شود، نه یک Domain موازی برای Request مگر اینکه Product واقعاً به JSM-like request model نیاز داشته باشد.

### &rlm;منابع

- https://support.atlassian.com/jira-software-cloud/docs/what-are-forms-and-what-can-they-do/
- https://support.atlassian.com/jira-software-cloud/docs/create-a-form/

---

## &rlm;08 — Board completion semantics

### &rlm;وضعیت تأییدشده

&rlm;Board Column و Status همچنان مفاهیم جدا هستند. چند Status می‌توانند به یک Column map شوند و Jira در Board behavior، Work Itemهای Right-most column را Complete در نظر می‌گیرد. Mapping اشتباه Statusها می‌تواند روی Sprint completion و report interpretation اثر بگذارد.

### &rlm;اثر روی Research

&rlm;این یک Pattern مهم برای Helpdesk است: Completion باید Domain semantic صریح داشته باشد و UI column نباید تنها منبع حقیقت lifecycle باشد.

### &rlm;منبع

- https://support.atlassian.com/jira-software-cloud/docs/configure-columns/

---

## &rlm;09 — Stable patterns در مقابل volatile details

### &rlm;Patternهای پایدار

- Work Item مستقل از Board است.
- Status مستقل از Board Column است.
- Search/Filter می‌تواند چند Consumer مثل Board/Report/Dashboard داشته باشد.
- Permission capability مستقل از record visibility است.
- Sprint مستقل از Release است.
- Parent/Child و Link/Dependency داده‌های Relationship هستند.
- Automation از Trigger/Condition/Action تشکیل می‌شود.
- Form یک Intake layer قابل map به Work Item است.

### &rlm;جزئیات ناپایدار که باید در Re-validation بمانند

- Terminology rollout و Legacy JQL names
- Plan/Edition availability
- UI navigation و نام Schemeها
- Product limits مثل تعداد Field/Work Type
- AI/Rovo entry points
- Market/pricing metrics

---

## &rlm;10 — نتیجه Re-validation

&rlm;Base Analysis در `jira-cloud-product-analysis.md` با وضعیت فعلی Jira Cloud سازگار است. هیچ Feature از Jira به MVP اضافه نمی‌شود. مهم‌ترین نکته برای نسخه‌های بعدی این است که از Jira **separation of concerns** و data relationships را قرض بگیریم، نه تعداد تنظیمات یا ceremonyهای آن را.
