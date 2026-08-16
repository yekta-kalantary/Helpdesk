<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# PRD — Project Kanban & Custom Task Workflow

## &rlm;00 — وضعیت سند

&rlm;این سند Product Requirement رسمی برای Kanban و Workflow سفارشی Task در سطح Project است و در کنار اسناد زیر قرار می‌گیرد:

- [Client Task Management MVP](./client-task-management-mvp.md)
- [Hybrid Project Work Hierarchy](./hybrid-project-work-hierarchy-prd.md)
- [Lightweight Task Subtasks](./task-checklist-subtasks-prd.md)

&rlm;Revision فعلی: v1.0 — 2026-08-16.

&rlm;وضعیت تصمیم: Approved Product Direction.

&rlm;این سند در Scope توسعه بعدی، مدل Status ثابت Task در PRD اولیه MVP را برای Workflow پروژه‌ای Supersede می‌کند. از زمان اجرای این Extension، Status یک Task دیگر Enum سراسری ثابت نیست و از Workflow همان Project می‌آید.

---

## &rlm;01 — مسئله

&rlm;پلتفرم برای انواع مختلف Project استفاده می‌شود؛ از WordPress و SEO تا Design، Development و Digital Marketing. Workflow مناسب این Projectها یکسان نیست و Statusهای ثابت سراسری مانند Todo / In Progress / Waiting Admin / Waiting Customer / Completed نمی‌توانند نیاز همه Projectها را بدون Complexity اضافی پوشش دهند.

&rlm;نیاز محصول این است که هر Project Workflow خودش را داشته باشد و Taskها در نمای Kanban بر اساس همان Workflow مدیریت شوند.

---

## &rlm;02 — تصمیم محصول

&rlm;هر Project مجموعه Statusهای مستقل خودش را دارد. Admin نام، ترتیب و Done Status پروژه را تعریف می‌کند و Kanban همان Statusها را به‌عنوان Column نمایش می‌دهد.

```text
Project
├── Status 1 [Open]  position=1
├── Status 2 [Open]  position=2
├── Status 3 [Open]  position=3
└── Status 4 [Done]  position=4

Task
└── project_status_id
```

&rlm;هر Project حداقل دو Status فعال دارد و دقیقاً یک Status فعال آن `Done` است.

&rlm;Admin و Customer Member پروژه می‌توانند هر Task قابل مشاهده در همان Project را بین Statusهای فعال جابه‌جا کنند. Assignment محدودکننده تغییر Status نیست.

---

## &rlm;03 — اهداف

- &rlm;تعریف Workflow مستقل برای هر Project.
- &rlm;نمایش Taskها به‌صورت Kanban بر اساس Statusهای همان Project.
- &rlm;پشتیبانی از انواع مختلف Service بدون Statusهای Hard-coded سراسری.
- &rlm;حفظ یک مفهوم سیستمی روشن برای Completion از طریق دقیقاً یک Done Status.
- &rlm;امکان تغییر Status همه Taskهای قابل مشاهده توسط Project Memberها با Audit کامل.
- &rlm;حفظ `completed_at` به‌عنوان Signal سیستمی مستقل از Label دلخواه Status.
- &rlm;حفظ Customer Isolation و Project Membership فعلی.

---

## &rlm;04 — Non-Goals

&rlm;موارد زیر در این Scope نیستند:

- Workflow global مشترک اجباری بین Projectها.
- Status transition matrix یا محدودکردن حرکت از ستون A فقط به B.
- Permission جداگانه per Status.
- WIP Limit.
- Automation Rule بر اساس ورود به Column.
- SLA per Status.
- Status Template / Workflow Template.
- Swimlane سفارشی.
- Task Dependency.
- چند Done Status فعال در یک Project.
- System-level Cancelled Status مستقل از Workflow Project.

---

## &rlm;05 — اصطلاحات Domain

### Project Status

&rlm;یک وضعیت قابل تنظیم متعلق به دقیقاً یک Project که یک Column در Kanban همان Project را تشکیل می‌دهد.

### Open Status

&rlm;هر Project Status فعال که `is_done=false` دارد.

### Done Status

&rlm;دقیقاً یک Project Status فعال که `is_done=true` دارد. قرار گرفتن Task در این Status به معنی Completion سیستمی Task است.

### Kanban

&rlm;نمای اصلی Taskهای Project که Columnهای آن مستقیماً از Project Statusهای فعال و بر اساس Position ساخته می‌شوند.

---

## &rlm;06 — Business Rules — Workflow

- &rlm;&lrm;BR-KAN-001&lrm; هر Project باید حداقل دو Status فعال داشته باشد.
- &rlm;&lrm;BR-KAN-002&lrm; هر Project باید دقیقاً یک Done Status فعال داشته باشد.
- &rlm;&lrm;BR-KAN-003&lrm; حداقل یک Status فعال باید `is_done=false` باشد.
- &rlm;&lrm;BR-KAN-004&lrm; فقط Admin می‌تواند Statusهای Project را ایجاد، Rename، Reorder یا Inactive کند.
- &rlm;&lrm;BR-KAN-005&lrm; فقط Admin می‌تواند تعیین کند کدام Status، Done Status است.
- &rlm;&lrm;BR-KAN-006&lrm; تغییر Done Status باید Atomic باشد؛ سیستم نباید در وضعیت پایدار صفر یا بیش از یک Done Status فعال داشته باشد.
- &rlm;&lrm;BR-KAN-007&lrm; ترتیب Statusها با Position تعیین می‌شود و همان ترتیب Columnهای Kanban است.
- &rlm;&lrm;BR-KAN-008&lrm; Status از UI Hard Delete نمی‌شود.
- &rlm;&lrm;BR-KAN-009&lrm; Status دارای Task نمی‌تواند Inactive شود مگر Taskهای آن در همان Operation یا قبل از آن به Status فعال دیگری منتقل شده باشند.
- &rlm;&lrm;BR-KAN-010&lrm; Done Status فعال نمی‌تواند Inactive شود مگر Status فعال دیگری هم‌زمان به Done تبدیل شود و تمام Invariantها حفظ شوند.
- &rlm;&lrm;BR-KAN-011&lrm; Status یک Project در Project دیگر قابل استفاده نیست.

---

## &rlm;07 — Business Rules — Task Status

- &rlm;&lrm;BR-KAN-TSK-001&lrm; هر Task باید دقیقاً یک Project Status فعال متعلق به Project خودش داشته باشد.
- &rlm;&lrm;BR-KAN-TSK-002&lrm; Admin و Customer Member دارای Project Access می‌توانند Status هر Task قابل مشاهده در همان Project را تغییر دهند.
- &rlm;&lrm;BR-KAN-TSK-003&lrm; Assignee محدودکننده تغییر Status نیست.
- &rlm;&lrm;BR-KAN-TSK-004&lrm; تغییر Status به Done Status باید `completed_at` را ثبت کند.
- &rlm;&lrm;BR-KAN-TSK-005&lrm; تغییر Status از Done Status به هر Open Status باید Task را Reopen و `completed_at` را خالی کند.
- &rlm;&lrm;BR-KAN-TSK-006&lrm; جابه‌جایی بین Open Statusها نباید `completed_at` را تغییر دهد.
- &rlm;&lrm;BR-KAN-TSK-007&lrm; تغییر Status نباید Assignee را به‌صورت خودکار تغییر یا پاک کند.
- &rlm;&lrm;BR-KAN-TSK-008&lrm; تغییر Status نباید Notification/Queue semantics قدیمی مانند Waiting Admin یا Waiting Customer را به‌صورت Hard-coded اجرا کند.
- &rlm;&lrm;BR-KAN-TSK-009&lrm; Task در Done Status برای Content Mutationهای معمول Read-only است، اما User مجاز می‌تواند آن را با تغییر Status به Open دوباره Reopen کند.
- &rlm;&lrm;BR-KAN-TSK-010&lrm; هر Status change باید Activity قابل Audit ایجاد کند.

---

## &rlm;08 — ساخت Task و Initial Status

&rlm;هر User که طبق Permissionهای موجود اجازه ساخت Task در Project را دارد، هنگام Create می‌تواند هر Status فعال همان Project را انتخاب کند؛ شامل Done Status.

- &rlm;&lrm;FR-KAN-CREATE-001&lrm; Create Task باید Project Statusهای فعال همان Project را برای انتخاب نمایش دهد.
- &rlm;&lrm;FR-KAN-CREATE-002&lrm; Admin و Customer مجاز هر دو می‌توانند Status اولیه را انتخاب کنند.
- &rlm;&lrm;FR-KAN-CREATE-003&lrm; Done Status نیز هنگام Create قابل انتخاب است.
- &rlm;&lrm;FR-KAN-CREATE-004&lrm; اگر User Status انتخاب نکند، اولین Open Status بر اساس Position به‌عنوان Default استفاده می‌شود.
- &rlm;&lrm;FR-KAN-CREATE-005&lrm; اگر Task مستقیماً در Done Status ایجاد شود، `completed_at` هنگام Create ثبت می‌شود.
- &rlm;&lrm;FR-KAN-CREATE-006&lrm; Status انتخابی باید Active و متعلق به همان Project باشد.

---

## &rlm;09 — Permission Model

### Admin

&rlm;Admin می‌تواند:

- Workflow Project را ایجاد و مدیریت کند.
- Status بسازد و Rename کند.
- ترتیب Statusها را تغییر دهد.
- Done Status را تعیین کند.
- Status را طبق Business Ruleها Inactive کند.
- هر Task را در Kanban جابه‌جا کند.

### Customer Member

&rlm;Customer Member نمی‌تواند Workflow Project را تغییر دهد، اما می‌تواند:

- Kanban Project را مشاهده کند.
- هر Task قابل مشاهده در Project را بین Statusهای فعال جابه‌جا کند.
- Task مجاز را هنگام Create با هر Status فعال ایجاد کند.
- Task موجود در Done Status را با انتقال به Status باز Reopen کند.

### User بدون Project Access

&rlm;هیچ Status، Column یا Task آن Project را نباید مشاهده یا تغییر دهد.

---

## &rlm;10 — Kanban UX

- &rlm;&lrm;FR-KAN-001&lrm; نمای اصلی Taskها در Project باید Kanban باشد.
- &rlm;&lrm;FR-KAN-002&lrm; هر Status فعال دقیقاً یک Column ایجاد می‌کند.
- &rlm;&lrm;FR-KAN-003&lrm; Columnها بر اساس Position مرتب می‌شوند.
- &rlm;&lrm;FR-KAN-004&lrm; Done Column باید از Metadata خود Status تشخیص داده شود، نه از Name آن.
- &rlm;&lrm;FR-KAN-005&lrm; Drag & Drop Task بین Columnها معادل Status change همان Task است.
- &rlm;&lrm;FR-KAN-006&lrm; Backend باید Status change را مستقل از UI Drag & Drop enforce و validate کند.
- &rlm;&lrm;FR-KAN-007&lrm; Task Card حداقل Reference، Title، Assignee، Priority و Due Date در صورت وجود را نمایش می‌دهد.
- &rlm;&lrm;FR-KAN-008&lrm; Work Group در صورت وجود باید به‌عنوان Context/Filter قابل تشخیص باشد ولی Columnهای Kanban از Work Group ساخته نمی‌شوند.
- &rlm;&lrm;FR-KAN-009&lrm; Kanban باید روی Mobile قابل استفاده باشد؛ Horizontal navigation بین Columnها مجاز است ولی Card operation نباید Desktop-only باشد.
- &rlm;&lrm;FR-KAN-010&lrm; Global Task Search/List می‌تواند برای Search/Reporting باقی بماند؛ Kanban نمای اصلی Taskهای داخل Project است.

---

## &rlm;11 — Data Requirements

### Project Status

&rlm;حداقل داده‌های محصولی:

- ID
- Project Reference
- Name / Title
- Position
- Is Done — boolean
- Is Active — boolean
- Created By
- Created At
- Updated At
- Inactivated At — nullable

### Task

&rlm;Task باید `project_status_id` داشته باشد.

&rlm;`completed_at` همچنان روی Task باقی می‌ماند و از ورود/خروج از Done Status همگام می‌شود.

### Integrity Rules

- &rlm;&lrm;NFR-KAN-DATA-001&lrm; Task Status باید متعلق به همان Project Task باشد.
- &rlm;&lrm;NFR-KAN-DATA-002&lrm; Project نباید کمتر از دو Status فعال داشته باشد.
- &rlm;&lrm;NFR-KAN-DATA-003&lrm; Project نباید صفر یا بیش از یک Done Status فعال داشته باشد.
- &rlm;&lrm;NFR-KAN-DATA-004&lrm; Status change و `completed_at` update باید در یک Transaction انجام شوند.
- &rlm;&lrm;NFR-KAN-DATA-005&lrm; تغییر Done Status باید Transactional باشد.

---

## &rlm;12 — Activity و Audit

&rlm;هر تغییر Status باید در Task Activity ثبت شود.

&rlm;حداقل Metadata لازم:

- Task Reference / ID
- Previous Project Status ID و Name
- New Project Status ID و Name
- Actor User
- Timestamp

&rlm;ایجاد Task نیز باید Initial Status را در Activity/Create metadata قابل تشخیص کند.

&rlm;Rename یا تغییر Done Flag یک Project Status نباید History قبلی Taskها را مبهم کند؛ Activity باید حداقل Snapshot Name وضعیت قبلی/جدید را هنگام تغییر Task نگه دارد.

---

## &rlm;13 — Project Completion

- &rlm;&lrm;BR-KAN-PRJ-001&lrm; Project فقط زمانی Completed می‌شود که همه Taskهای فعال آن در Done Status همان Project باشند.
- &rlm;&lrm;BR-KAN-PRJ-002&lrm; وجود Task در هر Open Status مانع Complete شدن Project است.
- &rlm;&lrm;BR-KAN-PRJ-003&lrm; Reopen کردن یک Task از Done به Open در Project Completed مجاز نیست مگر Project ابتدا طبق قواعد Project Lifecycle Reopen شود.

---

## &rlm;14 — ارتباط با Work Group و Subtask

### Work Group

&rlm;Work Group Hierarchy و Kanban دو Dimension مستقل هستند:

```text
Project
├── Work Group Hierarchy
│   └── Task
└── Project Status Workflow
    └── Kanban Columns
```

&rlm;یک Task می‌تواند در هر Work Group یا Root Project باشد و هم‌زمان دقیقاً یک Project Status داشته باشد.

### Subtask

&rlm;Subtask سبک Status مستقل ندارد و وارد Kanban نمی‌شود. Checklist Progress هیچ اثر خودکاری روی Project Status Task ندارد.

---

## &rlm;15 — Superseded Rules از MVP اولیه

&rlm;پس از اجرای این Extension، موارد زیر از `client-task-management-mvp.md` برای Status Workflow دیگر Source of Truth نیستند:

- Enum ثابت Todo / In Progress / Waiting Admin / Waiting Customer / Completed / Cancelled.
- الزام Customer-created Task به Waiting Admin.
- قواعد Assignee وابسته به Waiting Admin / Waiting Customer / Todo / In Progress.
- محدودیت Customer برای تغییر Status فقط روی Task Assign‌شده به خودش.
- Admin-only Reopen برای Task Completed.
- Admin Queue به‌عنوان Status سیستمی Hard-coded.
- تشخیص Overdue بر اساس نام Statusهای Completed/Cancelled.

&rlm;جایگزین رسمی:

- Status از Project Statusهای همان Project می‌آید.
- Completion فقط با `is_done=true` مشخص می‌شود.
- Customer Member می‌تواند Status هر Task قابل مشاهده در Project را تغییر دهد.
- Assignment از Status Workflow مستقل است.
- Overdue یعنی Due Date گذشته و Task در Done Status نباشد.

&rlm;قواعد Authorization، Project Membership، Isolation، Assignment ownership، Comment، Attachment و Audit که با موارد بالا تعارض ندارند همچنان معتبرند.

---

## &rlm;16 — Migration و Backward Compatibility

- &rlm;&lrm;MIG-KAN-001&lrm; برای هر Project موجود باید حداقل دو Project Status ساخته شود.
- &rlm;&lrm;MIG-KAN-002&lrm; دقیقاً یک Status Migration باید Done باشد.
- &rlm;&lrm;MIG-KAN-003&lrm; Taskهای موجود باید به Status جدید Project خود Map شوند و Task بدون `project_status_id` باقی نماند.
- &rlm;&lrm;MIG-KAN-004&lrm; Mapping Migration باید `completed_at` Taskهای موجود را حفظ یا با Done Status جدید سازگار کند.
- &rlm;&lrm;MIG-KAN-005&lrm; Task Reference، Comment، Attachment، Activity History، Work Group و Subtaskها نباید در Migration از بین بروند.
- &rlm;&lrm;MIG-KAN-006&lrm; Mapping دقیق Statusهای Legacy به Workflow جدید باید در Implementation Plan مشخص و تست شود.

---

## &rlm;17 — Acceptance Criteria

- &rlm;&lrm;AC-KAN-001&lrm; هر Project حداقل دو Status فعال دارد.
- &rlm;&lrm;AC-KAN-002&lrm; هر Project دقیقاً یک Done Status فعال دارد.
- &rlm;&lrm;AC-KAN-003&lrm; Admin می‌تواند Name، Position و Done Status Workflow را مدیریت کند.
- &rlm;&lrm;AC-KAN-004&lrm; Customer نمی‌تواند Workflow Definition را تغییر دهد.
- &rlm;&lrm;AC-KAN-005&lrm; Admin و Customer Member می‌توانند هر Task قابل مشاهده در Project را بین Statusهای فعال جابه‌جا کنند.
- &rlm;&lrm;AC-KAN-006&lrm; Assignment هیچ محدودیتی برای Status change ایجاد نمی‌کند.
- &rlm;&lrm;AC-KAN-007&lrm; ورود به Done `completed_at` را set و خروج از Done آن را unset می‌کند.
- &rlm;&lrm;AC-KAN-008&lrm; هر Status change Activity با Actor و Old/New Status ایجاد می‌کند.
- &rlm;&lrm;AC-KAN-009&lrm; Create Task اجازه انتخاب هر Status فعال شامل Done را می‌دهد.
- &rlm;&lrm;AC-KAN-010&lrm; Create Task بدون Status از اولین Open Status استفاده می‌کند.
- &rlm;&lrm;AC-KAN-011&lrm; Kanban Columnها دقیقاً از Statusهای فعال Project و Position آن‌ها ساخته می‌شوند.
- &rlm;&lrm;AC-KAN-012&lrm; Status متعلق به Project دیگر برای Task Reject می‌شود.
- &rlm;&lrm;AC-KAN-013&lrm; Status دارای Task بدون Migration مقصد Inactive نمی‌شود.
- &rlm;&lrm;AC-KAN-014&lrm; هیچ Operation عادی UI روی Status Hard Delete انجام نمی‌دهد.
- &rlm;&lrm;AC-KAN-015&lrm; Work Group و Subtask بدون تغییر مفهومی با Kanban کار می‌کنند.

---

## &rlm;18 — سناریوهای End-to-End

### E2E-KAN-001 — Workflow Setup

&rlm;Admin Project را با Statusهای Backlog، Doing، Review و Delivered تنظیم می‌کند؛ Delivered را Done قرار می‌دهد و ترتیب Columnها مطابق Position نمایش داده می‌شود.

### E2E-KAN-002 — Customer Move Any Task

&rlm;Customer Member Project یک Task Assign‌شده به User دیگری را از Doing به Review منتقل می‌کند. Operation مجاز است و Activity با Actor همان Customer، Old Status و New Status ثبت می‌شود.

### E2E-KAN-003 — Complete and Reopen

&rlm;Customer Task را از Review به Delivered منتقل می‌کند؛ `completed_at` ثبت می‌شود. سپس Task را از Delivered به Doing منتقل می‌کند؛ `completed_at` خالی می‌شود و Activity هر دو Transition ثبت می‌شود.

### E2E-KAN-004 — Create in Any Status

&rlm;Customer هنگام Create Task، Delivered را انتخاب می‌کند. Task از ابتدا در Done Status ساخته و `completed_at` ثبت می‌شود. Task دیگری بدون انتخاب Status ساخته می‌شود و به اولین Open Status می‌رود.

### E2E-KAN-005 — Workflow Integrity

&rlm;Admin تلاش می‌کند Done Status را بدون Replacement غیرفعال کند یا Workflow را به کمتر از دو Status فعال کاهش دهد. Backend Operation را Reject می‌کند.

### E2E-KAN-006 — Isolation

&rlm;Customer عضو Project A نمی‌تواند Status یا Task مربوط به Project B را مشاهده یا با API مستقیم جابه‌جا کند.

---

## &rlm;19 — معیار موفقیت

&rlm;این قابلیت زمانی موفق است که هر Project بتواند Workflow مختص خودش را بدون تغییر Core Task Domain تعریف کند؛ Taskها در Kanban با Columnهای پروژه مدیریت شوند؛ همه Project Memberهای مجاز بتوانند Status Taskها را جابه‌جا کنند؛ تمام تغییرات قابل Audit باشند؛ و Completion سیستم مستقل از اسم دلخواه Status با یک Done Status یکتا و قابل اتکا باقی بماند.

&rlm;قاعده نهایی: **Workflow متعلق به Project است؛ Kanban نمایش آن Workflow است؛ Task دقیقاً یک Status از همان Project دارد؛ و هر Project دقیقاً یک Done Status دارد.**
