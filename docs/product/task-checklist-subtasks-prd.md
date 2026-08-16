<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# PRD — Lightweight Task Subtasks

## &rlm;00 — وضعیت سند

&rlm;این سند یک Product Requirement رسمی برای قابلیت Subtask سبک است و به‌عنوان Extension مکمل [Hybrid Project Work Hierarchy](./hybrid-project-work-hierarchy-prd.md) تعریف می‌شود.

&rlm;Revision فعلی: v1.0 — 2026-08-16.

&rlm;وضعیت تصمیم: Approved Product Direction.

&rlm;این قابلیت مفهوم Task را به یک Hierarchy جدید تبدیل نمی‌کند. Task همچنان واحد اصلی Execution، Assignment، Status، Priority، Conversation و Completion است؛ Subtask فقط یک Checklist Item سبک داخل Task است.

---

## &rlm;01 — مسئله

&rlm;بعضی Taskها برای اجرا به چند قدم کوچک نیاز دارند، اما تبدیل هر قدم به Task کامل باعث ایجاد Assignee، Status، Priority، Notification و Conversation اضافی می‌شود و Complexity غیرضروری ایجاد می‌کند.

&rlm;نمونه:

```text
Task: راه‌اندازی صفحه محصول
├── Subtask: بررسی محتوای صفحه
├── Subtask: تنظیم تصاویر
├── Subtask: بررسی فرم خرید
└── Subtask: تست نسخه موبایل
```

&rlm;این قدم‌ها باید قابل ثبت و تیک‌زدن باشند، اما نباید Lifecycle مستقل Task داشته باشند.

---

## &rlm;02 — تصمیم محصول

&rlm;Subtask به‌صورت Checklist Item سبک و فقط یک Level زیر Task تعریف می‌شود:

```text
Client
└── Project
    ├── Work Group (optional, up to 5 levels)
    │   └── Task
    │       ├── Subtask
    │       ├── Subtask
    │       └── Subtask
    └── Task
        ├── Subtask
        └── Subtask
```

&rlm;Subtask نمی‌تواند Child Subtask داشته باشد.

&rlm;Subtask Task مستقل نیست و Reference، Assignee، Priority، Due Date، Workflow Status، Comment، Attachment یا Notification مستقل ندارد.

---

## &rlm;03 — اهداف

- &rlm;شکستن Taskهای نسبتاً بزرگ به قدم‌های کوچک و قابل تیک‌زدن.
- &rlm;حفظ Task به‌عنوان واحد اصلی مدیریت کار.
- &rlm;جلوگیری از ایجاد Taskهای بسیار ریز و شلوغ‌شدن Task List.
- &rlm;قابل استفاده بودن یک مدل واحد برای Development، SEO، WordPress، Design، Content و Digital Marketing.
- &rlm;امکان همکاری Admin و Customer روی Checklist همان Task بدون Permission Model مستقل.
- &rlm;حفظ رفتار ساده در Mobile و Desktop.

---

## &rlm;04 — Non-Goals

&rlm;موارد زیر بخشی از Subtask سبک نیستند:

- Assignee مستقل.
- Priority مستقل.
- Due Date مستقل.
- Status Workflow مانند Todo / In Progress / Waiting Customer / Waiting Admin.
- Comment مستقل.
- Attachment مستقل.
- Notification مستقل.
- Activity Timeline مستقل.
- Human-readable Reference مستقل.
- Subtask Dependency.
- Nested Subtask یا Subtask Level 2.
- Time Tracking.
- Estimate / Story Point.
- Permission مستقل از Task.
- Visibility مستقل از Task.
- تبدیل خودکار Subtask به Task یا Task به Subtask.

---

## &rlm;05 — اصطلاحات Domain

### Task

&rlm;واحد اصلی اجرای کار که تمام قوانین فعلی Assignment، Status، Priority، Due Date، Comment، Attachment، Notification و Completion روی آن اعمال می‌شود.

### Subtask

&rlm;یک Checklist Item سبک که دقیقاً متعلق به یک Task است و فقط برای نمایش یک قدم کوچک داخل همان Task استفاده می‌شود.

### Completed Subtask

&rlm;Subtaskای که `is_completed=true` دارد. Completed بودن Subtask هیچ Status Transition خودکاری روی Task ایجاد نمی‌کند.

---

## &rlm;06 — Business Rules

- &rlm;&lrm;BR-ST-001&lrm; هر Subtask دقیقاً متعلق به یک Task است.
- &rlm;&lrm;BR-ST-002&lrm; Subtask فقط یک Level دارد و نمی‌تواند Parent یا Child Subtask دیگری باشد.
- &rlm;&lrm;BR-ST-003&lrm; Visibility Subtask دقیقاً از Task والد به ارث می‌رسد.
- &rlm;&lrm;BR-ST-004&lrm; هر User که Permission تعامل با Task را دارد می‌تواند Subtaskهای همان Task را مدیریت کند.
- &rlm;&lrm;BR-ST-005&lrm; Admin و Customer Member دارای Access به Task می‌توانند Subtask ایجاد، Rename، Complete، Uncomplete، Reorder و Remove کنند.
- &rlm;&lrm;BR-ST-006&lrm; Complete شدن همه Subtaskها نباید Task را به‌صورت خودکار Completed کند.
- &rlm;&lrm;BR-ST-007&lrm; Completed کردن Task نباید Subtaskهای باز را به‌صورت خودکار Completed کند.
- &rlm;&lrm;BR-ST-008&lrm; وجود Subtask باز مانع Completed کردن Task نیست.
- &rlm;&lrm;BR-ST-009&lrm; Completed/Cancelled Task برای مدیریت Subtask Read-only است؛ Admin برای تغییر Checklist ابتدا Task را Reopen می‌کند.
- &rlm;&lrm;BR-ST-010&lrm; Completed Project برای مدیریت Subtask Read-only است؛ ادامه کار نیازمند Reopen Project است.
- &rlm;&lrm;BR-ST-011&lrm; Subtask از UI Hard Delete نمی‌شود و Remove باید History را حفظ کند.
- &rlm;&lrm;BR-ST-012&lrm; Reopen کردن Task وضعیت Completed/Not Completed Subtaskها را Reset نمی‌کند.
- &rlm;&lrm;BR-ST-013&lrm; جابه‌جایی Task بین Work Groupهای همان Project Subtaskهای آن را بدون تغییر حفظ می‌کند.
- &rlm;&lrm;BR-ST-014&lrm; Subtask هیچ اثر مستقیمی روی Project Membership یا Work Group Visibility ندارد.

---

## &rlm;07 — Permission Model

### User دارای Task Access

&rlm;هر User که طبق Authorization فعلی Task اجازه تعامل با Task را دارد، روی Subtaskهای فعال همان Task نیز اجازه دارد:

- Create
- Rename
- Complete
- Uncomplete
- Reorder
- Remove

### User بدون Task Access

&rlm;User بدون دسترسی به Task نباید بتواند Subtaskهای آن را از UI یا API مشاهده یا تغییر دهد.

### نکته

&rlm;Subtask Permission مستقل ندارد. اضافه‌کردن Permission per Subtask یا Visibility per Subtask خارج از Scope است.

---

## &rlm;08 — Data Requirements

&rlm;هر Subtask حداقل باید داده‌های زیر را داشته باشد:

- ID
- Task Reference
- Title — required
- Is Completed — boolean, default=false
- Position — required
- Created By
- Created At
- Updated At
- Removed At — nullable، برای Logical Removal

&rlm;در صورت نیاز Audit، Actor تغییر Complete/Uncomplete یا Remove از Task Activity موجود قابل رهگیری است و برای Subtask Activity Timeline مستقل ساخته نمی‌شود.

### Integrity Rules

- &rlm;&lrm;NFR-ST-DATA-001&lrm; Title خالی قابل ذخیره نیست.
- &rlm;&lrm;NFR-ST-DATA-002&lrm; Subtask باید به Task معتبر متصل باشد.
- &rlm;&lrm;NFR-ST-DATA-003&lrm; Position باید Ordering پایدار داخل همان Task را پشتیبانی کند.
- &rlm;&lrm;NFR-ST-DATA-004&lrm; Logical Removal نباید رکورد Subtask یا Audit مرتبط را حذف کند.
- &rlm;&lrm;NFR-ST-DATA-005&lrm; Subtask Relation نباید امکان دسترسی Cross-project یا Cross-client ایجاد کند.

---

## &rlm;09 — Functional Requirements

- &rlm;&lrm;FR-ST-001&lrm; User مجاز باید بتواند در Task فعال Subtask جدید با Title ایجاد کند.
- &rlm;&lrm;FR-ST-002&lrm; Subtask جدید با `is_completed=false` ایجاد می‌شود.
- &rlm;&lrm;FR-ST-003&lrm; User مجاز باید بتواند Title Subtask فعال را ویرایش کند.
- &rlm;&lrm;FR-ST-004&lrm; User مجاز باید بتواند Subtask را Complete و Uncomplete کند.
- &rlm;&lrm;FR-ST-005&lrm; User مجاز باید بتواند ترتیب Subtaskهای یک Task را تغییر دهد.
- &rlm;&lrm;FR-ST-006&lrm; User مجاز باید بتواند Subtask را به‌صورت Logical Remove از Checklist فعال خارج کند.
- &rlm;&lrm;FR-ST-007&lrm; Task Detail باید Subtaskهای فعال را با وضعیت Checked/Unchecked نمایش دهد.
- &rlm;&lrm;FR-ST-008&lrm; Task Detail می‌تواند Summary مانند `2/5 completed` نمایش دهد.
- &rlm;&lrm;FR-ST-009&lrm; تکمیل آخرین Subtask نباید Endpoint یا Domain Action مربوط به Complete Task را Trigger کند.
- &rlm;&lrm;FR-ST-010&lrm; Complete/Cancel/Reopen Task نباید مقادیر Subtaskها را به‌صورت خودکار تغییر دهد.
- &rlm;&lrm;FR-ST-011&lrm; عملیات Subtask روی Task/Project بسته باید Reject شود و UI حالت Read-only نشان دهد.
- &rlm;&lrm;FR-ST-012&lrm; عملیات Subtask نباید Notification مستقل تولید کند.
- &rlm;&lrm;FR-ST-013&lrm; Subtask نباید در Task List سراسری به‌عنوان Task مستقل ظاهر شود.
- &rlm;&lrm;FR-ST-014&lrm; Subtask نباید URL/Reference مستقل شبیه Task اصلی نیاز داشته باشد.

---

## &rlm;10 — Activity و Audit

&rlm;Subtask Timeline مستقل ندارد. رویدادهای مهم باید در Context Task قابل Audit باشند بدون اینکه Noise زیادی ایجاد شود.

&rlm;حداقل رویدادهای زیر باید در Task Activity قابل ثبت باشند:

- Subtask Added
- Subtask Renamed
- Subtask Completed
- Subtask Reopened / Uncompleted
- Subtask Removed

&rlm;Reorder ساده Subtask الزام به Activity مستقل ندارد.

&rlm;Activity باید Actor و Subtask ID/Title لازم برای Traceability را نگه دارد ولی نباید Subtask را به Resource مستقل در سطح Task تبدیل کند.

---

## &rlm;11 — Progress و Reporting

&rlm;Subtask می‌تواند فقط Progress داخلی Checklist همان Task را نشان دهد:

```text
checklist_progress = completed active subtasks / all active subtasks
```

&rlm;اگر Task Subtask ندارد، Checklist Progress نمایش داده نمی‌شود.

&rlm;Checklist Progress نباید Status Task را تغییر دهد.

&rlm;Checklist Progress نباید در فرمول Work Group Progress جایگزین Task Completion شود. Work Group Progress همچنان بر اساس Completed بودن Taskها محاسبه می‌شود.

---

## &rlm;12 — UX Requirements

- &rlm;Subtaskها داخل Task Detail نمایش داده می‌شوند و صفحه مستقل ندارند.
- &rlm;اضافه‌کردن Subtask باید سریع و بدون فرم Task کامل باشد.
- &rlm;هر Subtask حداقل Checkbox، Title و Actionهای مجاز را نشان می‌دهد.
- &rlm;Completed Subtask باید از نظر بصری از Subtask باز قابل تشخیص باشد.
- &rlm;Reorder باید بدون تغییر Parent Task انجام شود.
- &rlm;روی Mobile ایجاد، Complete/Uncomplete، Rename و Remove باید قابل انجام باشد.
- &rlm;در Task Read-only، Checkbox و Actionهای ویرایشی غیرفعال یا مخفی می‌شوند.
- &rlm;UI نباید برای Subtask Assignee، Priority، Due Date یا Status Selector نمایش دهد.

---

## &rlm;13 — سناریوهای نمونه

### سناریو A — WordPress

```text
Task: رفع مشکل منوی موبایل
├── Subtask: بررسی Safari
├── Subtask: اصلاح ارتفاع Navigation
├── Subtask: تست Search Box
└── Subtask: تست روی موبایل واقعی
```

### سناریو B — SEO

```text
Task: انتشار مقاله سرمایه‌گذاری برای فرزند
├── Subtask: بررسی Brief
├── Subtask: درج Internal Link
├── Subtask: بهینه‌سازی Title/Meta
└── Subtask: بررسی Schema
```

### سناریو C — Development

```text
Task: پیاده‌سازی Login
├── Subtask: Validation
├── Subtask: Service
├── Subtask: Tests
└── Subtask: API Documentation
```

&rlm;در هر سه مثال، Subtaskها فقط Checklist هستند و هیچ‌کدام Task مستقل محسوب نمی‌شوند.

---

## &rlm;14 — Migration و Backward Compatibility

- &rlm;&lrm;MIG-ST-001&lrm; تمام Taskهای فعلی بدون Subtask کاملاً معتبر باقی می‌مانند.
- &rlm;&lrm;MIG-ST-002&lrm; اضافه‌شدن Subtask هیچ تغییری در Task Reference، Status، Assignment، Comment، Attachment یا Notification فعلی ایجاد نمی‌کند.
- &rlm;&lrm;MIG-ST-003&lrm; Work Group Relation Task بدون تغییر باقی می‌ماند.
- &rlm;&lrm;MIG-ST-004&lrm; Project Membership و Customer Isolation فعلی بدون تغییر مفهومی باقی می‌مانند.

---

## &rlm;15 — Acceptance Criteria

- &rlm;&lrm;AC-ST-001&lrm; Admin دارای Task Access می‌تواند Subtask ایجاد و مدیریت کند.
- &rlm;&lrm;AC-ST-002&lrm; Customer Member دارای Task Access می‌تواند همان عملیات Subtask را انجام دهد.
- &rlm;&lrm;AC-ST-003&lrm; User بدون Task Access Subtask را مشاهده یا تغییر نمی‌دهد.
- &rlm;&lrm;AC-ST-004&lrm; Subtask فقط Title، Completion State و Ordering سبک دارد و Task Workflow مستقل ایجاد نمی‌کند.
- &rlm;&lrm;AC-ST-005&lrm; Subtask نمی‌تواند Child Subtask داشته باشد.
- &rlm;&lrm;AC-ST-006&lrm; Complete شدن تمام Subtaskها Status Task را تغییر نمی‌دهد.
- &rlm;&lrm;AC-ST-007&lrm; Task دارای Subtask باز می‌تواند با Action صریح User Completed شود.
- &rlm;&lrm;AC-ST-008&lrm; Completed شدن Task Subtaskهای باز را Completed نمی‌کند.
- &rlm;&lrm;AC-ST-009&lrm; Reopen Task وضعیت Subtaskها را بدون تغییر حفظ می‌کند.
- &rlm;&lrm;AC-ST-010&lrm; Task/Project بسته اجازه تغییر Checklist را نمی‌دهد.
- &rlm;&lrm;AC-ST-011&lrm; Remove کردن Subtask Hard Delete انجام نمی‌دهد و History قابل Audit باقی می‌ماند.
- &rlm;&lrm;AC-ST-012&lrm; Subtask Notification مستقل ایجاد نمی‌کند.
- &rlm;&lrm;AC-ST-013&lrm; جابه‌جایی Task بین Work Groupها Subtaskها و Completion State آن‌ها را حفظ می‌کند.
- &rlm;&lrm;AC-ST-014&lrm; Subtask Completion روی Work Group Progress فقط از طریق Status خود Task اثر می‌گذارد و اثر مستقیم ندارد.

---

## &rlm;16 — سناریوهای پذیرش End-to-End

### E2E-ST-001 — Shared Checklist

&rlm;Admin داخل Task فعال سه Subtask ایجاد می‌کند. Customer Member همان Task Subtaskها را می‌بیند، یکی را Complete و یکی را Rename می‌کند. تغییرات برای Admin قابل مشاهده است.

### E2E-ST-002 — No Automatic Task Completion

&rlm;Task در Status=In Progress دارای سه Subtask است. User هر سه Subtask را Complete می‌کند. Task همچنان In Progress باقی می‌ماند تا User صراحتاً آن را Completed کند.

### E2E-ST-003 — Parent Completion Independent

&rlm;Task دارای یک Subtask Completed و دو Subtask باز است. User با Permission لازم Task را Completed می‌کند. Task Completed می‌شود ولی دو Subtask باز همان وضعیت قبلی را حفظ می‌کنند و Checklist Read-only می‌شود.

### E2E-ST-004 — Reopen

&rlm;Admin Task Completed را Reopen می‌کند. وضعیت Checked/Unchecked تمام Subtaskها همان مقادیر قبل از Reopen باقی می‌ماند و Checklist دوباره قابل مدیریت می‌شود.

### E2E-ST-005 — Logical Removal

&rlm;Customer Member یک Subtask را Remove می‌کند. Subtask دیگر در Checklist فعال نمایش داده نمی‌شود ولی Hard Delete نمی‌شود و Audit عملیات باقی می‌ماند.

### E2E-ST-006 — Authorization

&rlm;Customer عضو Project A نمی‌تواند با URL/API مستقیم Subtask مربوط به Task در Project B را مشاهده، Complete، Rename یا Remove کند.

---

## &rlm;17 — معیار موفقیت

&rlm;این قابلیت زمانی موفق است که User بتواند بدون ساخت Taskهای ریز، یک Task را به قدم‌های ساده تقسیم کند؛ Admin و Customer مجاز بتوانند روی همان Checklist همکاری کنند؛ و اضافه‌شدن Subtask هیچ Complexity جدیدی در Assignment، Workflow، Notification یا Permission Model ایجاد نکند.

&rlm;قاعده نهایی: **Subtask یک Checklist Item است؛ Task نیست.**