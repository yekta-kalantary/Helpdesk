<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# PRD — Hybrid Project Work Hierarchy

## &rlm;00 — وضعیت سند

&rlm;این سند یک Product Requirement رسمی برای توسعه ساختار مدیریت کار در Helpdesk است و در کنار [Client Task Management MVP](./client-task-management-mvp.md) قرار می‌گیرد.

&rlm;Revision فعلی: v1.0 — 2026-08-16.

&rlm;وضعیت تصمیم: Approved Product Direction.

&rlm;این سند Scope نسخه MVP v1.1 موجود را به‌صورت retroactive تغییر نمی‌دهد. قابلیت Work Group یک Product Extension رسمی است که می‌تواند در Iteration بعدی توسعه وارد Scope اجرایی شود.

&rlm;مرجع مفهومی اولیه، الگوی Epic / Story در ابزارهای مدیریت پروژه است؛ با این تفاوت که Domain این محصول عمداً Generic طراحی می‌شود تا به Software Development یا Agile محدود نشود.

---

## &rlm;01 — مسئله

&rlm;پلتفرم باید بتواند پروژه‌هایی با اندازه و ماهیت بسیار متفاوت را مدیریت کند؛ از یک کار کوچک WordPress با یک Task تا یک پروژه چندماهه مانند LMS، SEO، طراحی سایت، برنامه‌نویسی، Digital Marketing یا ترکیبی از چند Service.

&rlm;اگر برای تمام Projectها یک Hierarchy اجباری مانند Initiative → Epic → Story → Task تعریف شود، پروژه‌های کوچک بدون دلیل پیچیده می‌شوند. از طرف دیگر، اگر ساختار فقط Project → Task باقی بماند، Projectهای بزرگ پس از افزایش تعداد Taskها خوانایی و قابلیت مدیریت خود را از دست می‌دهند.

&rlm;نیاز محصول یک ساختار Progressive است: Complexity فقط زمانی وارد Project شود که واقعاً لازم باشد.

---

## &rlm;02 — تصمیم محصول

&rlm;مدل رسمی پیشنهادی Hybrid است:

```text
Client
└── Project
    ├── Work Group (optional — level 1)
    │   ├── Work Group (optional — level 2)
    │   │   ├── Work Group (optional — level 3)
    │   │   │   ├── Work Group (optional — level 4)
    │   │   │   │   ├── Work Group (optional — level 5)
    │   │   │   │   │   └── Task
    │   │   │   │   └── Task
    │   │   │   └── Task
    │   │   └── Task
    │   └── Task
    └── Task
```

&rlm;Task همچنان واحد اصلی Execution باقی می‌ماند.

&rlm;Work Group فقط یک لایه Generic برای سازمان‌دهی Taskها است و نباید به یک مفهوم خاص مانند Epic، Phase، Campaign، Sprint یا Department وابسته شود.

&rlm;هر Project می‌تواند بدون Work Group کار کند. بنابراین ساختار ساده فعلی Project → Task یک مسیر کاملاً معتبر و First-class باقی می‌ماند.

---

## &rlm;03 — اهداف

- &rlm;پشتیبانی از Projectهای کوچک بدون افزودن Ceremony یا ساختار اجباری.
- &rlm;پشتیبانی از Projectهای بزرگ و چندماهه با حداکثر ۵ سطح Work Group.
- &rlm;استفاده از یک Domain Model مشترک برای SEO، WordPress، Software Development، Design، Digital Marketing و سایر Serviceها.
- &rlm;حفظ Task به‌عنوان کوچک‌ترین واحد اجرایی و قابل Assignment.
- &rlm;حفظ Project Membership به‌عنوان مرز اصلی Visibility برای Customer.
- &rlm;جلوگیری از تبدیل محصول به یک Jira Clone یا ابزار Agile-only.
- &rlm;حفظ Backward Compatibility برای تمام Project و Taskهای فعلی.
- &rlm;فراهم‌کردن پایه مناسب برای Project Template و Reporting در آینده بدون وابسته‌کردن Core Domain به آن‌ها.

---

## &rlm;04 — Non-Goals

&rlm;موارد زیر بخشی از این Requirement نیستند:

- Initiative / Epic / Story به‌عنوان Entityهای ثابت Domain.
- Custom Hierarchy Builder.
- بیش از ۵ سطح Work Group.
- Sprint و Story Point.
- Task Dependency.
- Gantt.
- Kanban-specific Workflow.
- Custom Workflow per Work Group.
- Permission مجزا در سطح Work Group.
- Assignee یا Team Owner در سطح Work Group.
- Custom Field per Work Group.
- Project Template و Service Template.
- Cross-project Work Group.
- Portfolio Management.
- Theme به‌عنوان Parent در Hierarchy.
- Milestone به‌عنوان Parent در Hierarchy.

---

## &rlm;05 — اصطلاحات Domain

### Project

&rlm;Workspace اصلی متعلق به یک Client. تمام Task و Work Groupها داخل Project قرار می‌گیرند.

### Work Group

&rlm;یک Container اختیاری و Generic داخل Project برای گروه‌بندی Taskها یا Work Groupهای مرتبط.

&rlm;نام Domain و Database باید Work Group باقی بماند تا محصول به Terminology یک Industry خاص وابسته نشود.

&rlm;در آینده UI می‌تواند بر اساس Project Template یا Context، Label متفاوتی مانند Phase، Epic، Workstream، Campaign یا Section نمایش دهد؛ اما این قابلیت بخشی از Scope فعلی نیست.

### Root Work Group

&rlm;Work Group سطح اول که مستقیماً به Project تعلق دارد و Parent ندارد.

### Child Work Group

&rlm;Work Group سطح ۲ تا ۵ که Parent آن یک Work Group دیگر در همان Project است.

### Root Task

&rlm;Taskای که مستقیماً به Project تعلق دارد و Work Group ندارد.

---

## &rlm;06 — قواعد Hierarchy

- &rlm;&lrm;BR-WG-001&lrm; هر Work Group دقیقاً متعلق به یک Project است.
- &rlm;&lrm;BR-WG-002&lrm; Work Group می‌تواند Parent نداشته باشد یا یک Parent Work Group در همان Project داشته باشد.
- &rlm;&lrm;BR-WG-003&lrm; حداکثر عمق Work Group پنج Level است.
- &rlm;&lrm;BR-WG-004&lrm; Level 1 مستقیماً زیر Project قرار دارد و Level 5 عمیق‌ترین سطح مجاز است.
- &rlm;&lrm;BR-WG-005&lrm; سیستم نباید اجازه ایجاد Child Work Group زیر Level 5 را بدهد.
- &rlm;&lrm;BR-WG-006&lrm; Work Group نمی‌تواند Parent خودش یا Descendant خودش باشد.
- &rlm;&lrm;BR-WG-007&lrm; Parent و Child Work Group باید متعلق به یک Project باشند.
- &rlm;&lrm;BR-WG-008&lrm; Work Group بین Projectها Move نمی‌شود.
- &rlm;&lrm;BR-WG-009&lrm; Task می‌تواند Work Group نداشته باشد.
- &rlm;&lrm;BR-WG-010&lrm; Task می‌تواند به Work Group در هر یک از Levelهای ۱ تا ۵ متصل شود.
- &rlm;&lrm;BR-WG-011&lrm; Work Group نباید Visibility Boundary ایجاد کند؛ Visibility همچنان از Project Membership می‌آید.
- &rlm;&lrm;BR-WG-012&lrm; Work Group نباید Assignee، Priority یا Workflow مستقل از Task داشته باشد.
- &rlm;&lrm;BR-WG-013&lrm; وجود Child Work Group مانع وجود Task مستقیم زیر Parent Work Group نیست.
- &rlm;&lrm;BR-WG-014&lrm; وجود Work Group برای Project اختیاری است و Project بدون Work Group کاملاً معتبر است.

---

## &rlm;07 — Permission Model

### Admin

&rlm;Admin می‌تواند Work Group ایجاد، ویرایش، مرتب، جابه‌جا و غیرفعال کند و Taskها را بین Work Groupهای همان Project منتقل کند.

### Customer

&rlm;Customer می‌تواند ساختار Work Groupهای Projectهایی را که Member آن‌ها است مشاهده کند، اما در این نسخه اجازه ایجاد، تغییر، جابه‌جایی یا غیرفعال‌کردن Work Group را ندارد.

&rlm;Customer همچنان بر اساس Permissionهای Task موجود می‌تواند Task ایجاد کند. Customer-created Task می‌تواند بدون Work Group ایجاد شود؛ قرار دادن یا انتقال Task به Work Group توسط Admin انجام می‌شود.

### قواعد Permission

- &rlm;&lrm;BR-WG-AUTH-001&lrm; فقط Admin ساختار Hierarchy Project را تغییر می‌دهد.
- &rlm;&lrm;BR-WG-AUTH-002&lrm; Customer Member همه Work Groupهای همان Project را می‌بیند؛ Per-Work-Group Visibility وجود ندارد.
- &rlm;&lrm;BR-WG-AUTH-003&lrm; دسترسی به Task با Project Membership تعیین می‌شود، نه Work Group Membership.
- &rlm;&lrm;BR-WG-AUTH-004&lrm; URL یا API مربوط به Work Group باید همان Project-level Authorization را enforce کند.

---

## &rlm;08 — Lifecycle

&rlm;Work Group یک Execution Unit نیست و Workflow مستقل ندارد. برای جلوگیری از پیچیدگی غیرضروری، Statusهایی مانند Todo / In Progress / Waiting روی Work Group تعریف نمی‌شوند.

&rlm;Work Group فقط دو Lifecycle State دارد:

- Active
- Inactive

&rlm;Inactive شدن Work Group برای خارج‌کردن آن از ساختار فعال Project بدون حذف History استفاده می‌شود.

### قواعد Lifecycle

- &rlm;&lrm;BR-WG-LIFE-001&lrm; Work Group از UI Hard Delete نمی‌شود.
- &rlm;&lrm;BR-WG-LIFE-002&lrm; Work Group دارای Child Work Group فعال یا Task فعال نباید مستقیماً Inactive شود.
- &rlm;&lrm;BR-WG-LIFE-003&lrm; Admin باید ابتدا Childها را منتقل/غیرفعال و Taskهای فعال را منتقل یا ببندد.
- &rlm;&lrm;BR-WG-LIFE-004&lrm; Inactive Work Group و History آن برای Admin قابل مشاهده باقی می‌ماند.
- &rlm;&lrm;BR-WG-LIFE-005&lrm; Inactive کردن Work Group نباید Task History یا Activity را حذف کند.

---

## &rlm;09 — Data Requirements

### Work Group

&rlm;هر Work Group حداقل باید این داده‌ها را داشته باشد:

- ID
- Project Reference
- Parent Work Group Reference — nullable
- Title — required
- Description — optional
- Position — required
- Status — Active / Inactive
- Created By
- Created At
- Updated At

&rlm;Depth نباید به‌عنوان Source of Truth مستقل ذخیره شود مگر Technical Design برای Performance آن را به‌صورت Cache/Derived Field توجیه کند. Parent relation منبع اصلی Hierarchy است.

### Task Extension

&rlm;Task یک Work Group Reference اختیاری دریافت می‌کند.

&rlm;Task بدون Work Group همچنان مستقیماً متعلق به Project است.

### Integrity Rules

- &rlm;&lrm;NFR-WG-DATA-001&lrm; Work Group Parent باید متعلق به همان Project باشد.
- &rlm;&lrm;NFR-WG-DATA-002&lrm; Task Work Group باید متعلق به همان Project Task باشد.
- &rlm;&lrm;NFR-WG-DATA-003&lrm; Circular hierarchy باید در Domain و Database-safe logic جلوگیری شود.
- &rlm;&lrm;NFR-WG-DATA-004&lrm; Depth بیشتر از ۵ باید Server-side Reject شود.
- &rlm;&lrm;NFR-WG-DATA-005&lrm; Position برای Ordering پایدار Work Groupها در سطح یک Parent نگهداری می‌شود.

---

## &rlm;10 — Functional Requirements

- &rlm;&lrm;FR-WG-001&lrm; Admin باید بتواند داخل Project فعال Work Group سطح ۱ ایجاد کند.
- &rlm;&lrm;FR-WG-002&lrm; Admin باید بتواند زیر Work Group سطح ۱ تا ۴ Child Work Group ایجاد کند.
- &rlm;&lrm;FR-WG-003&lrm; UI نباید گزینه ایجاد Child زیر Work Group سطح ۵ را نمایش دهد و Backend نیز درخواست آن را Reject کند.
- &rlm;&lrm;FR-WG-004&lrm; Admin باید بتواند Title و Description Work Group را ویرایش کند.
- &rlm;&lrm;FR-WG-005&lrm; Admin باید بتواند Work Group را در همان Parent مرتب کند.
- &rlm;&lrm;FR-WG-006&lrm; Admin باید بتواند Work Group را به Parent دیگری در همان Project منتقل کند، مشروط به اینکه Depth نهایی هیچ Descendant از ۵ بیشتر نشود.
- &rlm;&lrm;FR-WG-007&lrm; Admin باید بتواند Task را بین Root Project و Work Groupهای همان Project منتقل کند.
- &rlm;&lrm;FR-WG-008&lrm; Task Create/Edit Admin باید Work Group اختیاری داشته باشد.
- &rlm;&lrm;FR-WG-009&lrm; Customer Create Task نباید نیازمند انتخاب Work Group باشد.
- &rlm;&lrm;FR-WG-010&lrm; Project Detail باید بتواند Taskها را به‌صورت Hierarchical Grouped View نمایش دهد.
- &rlm;&lrm;FR-WG-011&lrm; Project Detail باید Taskهای بدون Work Group را به‌صورت مشخص نمایش دهد.
- &rlm;&lrm;FR-WG-012&lrm; Task List سراسری باید بتواند Work Group را به‌عنوان Context نمایش دهد بدون اینکه Hierarchy شرط مشاهده Task شود.
- &rlm;&lrm;FR-WG-013&lrm; Search Task باید مستقل از باز یا بسته بودن Work Groupها کار کند.
- &rlm;&lrm;FR-WG-014&lrm; تغییر Work Group Task باید Activity ایجاد کند.
- &rlm;&lrm;FR-WG-015&lrm; ایجاد، تغییر Parent، Rename و Inactive شدن Work Group باید Activity قابل Audit ایجاد کند.

---

## &rlm;11 — Progress و Reporting

&rlm;Progress Work Group نباید به‌صورت دستی ذخیره یا ویرایش شود. اگر UI Progress نمایش دهد، مقدار آن از Taskهای Descendant محاسبه می‌شود.

&rlm;در ساده‌ترین نسخه:

```text
progress = completed descendant tasks / all non-cancelled descendant tasks
```

&rlm;Cancelled Task در مخرج Progress قرار نمی‌گیرد.

&rlm;اگر Work Group هیچ Task مستقیم یا Descendant نداشته باشد، Progress به‌صورت Not Available نمایش داده می‌شود و نه 0% یا 100%.

&rlm;Weight، Estimate، Story Point و Hour-based Progress در این Scope وجود ندارند.

---

## &rlm;12 — Theme و Milestone

### Theme

&rlm;Theme در آینده باید یک Classification مستقل و Cross-project باشد، نه یک Parent در Hierarchy.

&rlm;نمونه Theme:

- Growth
- Conversion
- Performance
- Branding
- Retention

&rlm;یک Task یا Work Group می‌تواند در آینده به Theme مرتبط شود، اما Theme نباید ساختار Project → Work Group → Task را تغییر دهد.

### Milestone

&rlm;Milestone در آینده یک Delivery/Time Marker مستقل است و نباید به‌عنوان Parent برای Task یا Work Group استفاده شود.

&rlm;Taskها یا Work Groupها می‌توانند بعداً به Milestone Reference داشته باشند، اما Milestone بخشی از این Scope نیست.

---

## &rlm;13 — UX Requirements

### Projectهای کوچک

&rlm;اگر Project هیچ Work Group ندارد، UI نباید Empty Hierarchy یا Setup Step اضافی نشان دهد. تجربه باید همان Project → Task فعلی باقی بماند.

### Projectهای ساختاریافته

&rlm;اگر Work Group وجود دارد، Project Detail باید Tree/Grouped View قابل فهم داشته باشد.

### رفتار Tree

- &rlm;Work Groupها Collapse/Expand می‌شوند.
- &rlm;Depth باید از طریق Indentation و Breadcrumb قابل تشخیص باشد.
- &rlm;Task در هر Level قابل مشاهده است.
- &rlm;Taskهای Root باید Section مستقل و واضح داشته باشند.
- &rlm;روی Mobile، Hierarchy نباید باعث Horizontal Scroll اجباری شود.
- &rlm;Breadcrumb Task باید Project و در صورت وجود Path Work Group را نشان دهد.

### Breadcrumb نمونه

```text
LMS Platform
/ MVP
/ Learning
/ Courses
/ Live Classes
/ Recording
/ Fix recording retry policy
```

---

## &rlm;14 — سناریوهای نمونه

### سناریو A — WordPress کوچک

```text
Project: Fix Mobile Navigation
└── Task: Fix search box overlap
```

&rlm;هیچ Work Group لازم نیست.

### سناریو B — طراحی سایت

```text
Project: Corporate Website
├── Work Group: UX/UI
│   ├── Task: Homepage wireframe
│   └── Task: Product page design
├── Work Group: Development
│   ├── Task: Theme setup
│   └── Task: Contact form
└── Work Group: Launch
    └── Task: Production deployment
```

### سناریو C — SEO

```text
Project: SEO Retainer
├── Work Group: Technical SEO
│   ├── Task: Crawl audit
│   └── Task: Fix canonical issues
├── Work Group: Content
│   ├── Work Group: Gold Cluster
│   │   ├── Task: Keyword research
│   │   └── Task: Publish pillar article
│   └── Work Group: Investment Cluster
│       └── Task: Update briefs
└── Work Group: Off-page
    └── Task: Outreach batch 01
```

### سناریو D — Digital Marketing

```text
Project: Product Launch Campaign
├── Work Group: Strategy
├── Work Group: Creative
├── Work Group: Landing Page
├── Work Group: Paid Media
│   ├── Work Group: Google Ads
│   └── Work Group: Meta Ads
└── Work Group: Analytics
```

### سناریو E — LMS بزرگ با ۵ Level

```text
Project: LMS Platform
└── Work Group L1: MVP
    └── Work Group L2: Learning
        └── Work Group L3: Courses
            └── Work Group L4: Live Classes
                └── Work Group L5: Recording
                    ├── Task: Store LiveKit recording
                    ├── Task: Add retry policy
                    └── Task: Add playback permission
```

&rlm;در همین Project ممکن است Task دیگری مستقیماً زیر Project یا هر Level بالاتر قرار بگیرد.

---

## &rlm;15 — Migration و Backward Compatibility

- &rlm;&lrm;MIG-WG-001&lrm; تمام Taskهای موجود پس از Migration باید Work Group Reference خالی داشته باشند.
- &rlm;&lrm;MIG-WG-002&lrm; هیچ Project فعلی برای معتبر ماندن نیاز به Work Group ندارد.
- &rlm;&lrm;MIG-WG-003&lrm; URL، Reference و History Taskهای موجود تغییر نمی‌کند.
- &rlm;&lrm;MIG-WG-004&lrm; Permission و Project Membership موجود بدون تغییر باقی می‌ماند.
- &rlm;&lrm;MIG-WG-005&lrm; اضافه‌شدن Work Group نباید Customer Isolation موجود را تضعیف کند.

---

## &rlm;16 — Acceptance Criteria

- &rlm;&lrm;AC-WG-001&lrm; Project بدون Work Group دقیقاً مانند مدل فعلی Project → Task قابل استفاده است.
- &rlm;&lrm;AC-WG-002&lrm; Admin می‌تواند تا ۵ Level Work Group بسازد.
- &rlm;&lrm;AC-WG-003&lrm; ایجاد Level 6 از UI و API Reject می‌شود.
- &rlm;&lrm;AC-WG-004&lrm; Circular Parent relation قابل ایجاد نیست.
- &rlm;&lrm;AC-WG-005&lrm; Work Group نمی‌تواند Parent متعلق به Project دیگر داشته باشد.
- &rlm;&lrm;AC-WG-006&lrm; Task می‌تواند بدون Work Group یا در هر Level ۱ تا ۵ قرار بگیرد.
- &rlm;&lrm;AC-WG-007&lrm; Task نمی‌تواند به Work Group متعلق به Project دیگر منتقل شود.
- &rlm;&lrm;AC-WG-008&lrm; Customer Member همه Taskهای Project را مستقل از Work Group آن‌ها می‌بیند.
- &rlm;&lrm;AC-WG-009&lrm; Customer نمی‌تواند Work Group ایجاد، ویرایش، جابه‌جا یا غیرفعال کند.
- &rlm;&lrm;AC-WG-010&lrm; جابه‌جایی Parent که باعث Depth بیشتر از ۵ برای هر Descendant شود Reject می‌شود.
- &rlm;&lrm;AC-WG-011&lrm; تغییر Work Group Task در Activity ثبت می‌شود.
- &rlm;&lrm;AC-WG-012&lrm; Inactive کردن Work Group History را حذف نمی‌کند.
- &rlm;&lrm;AC-WG-013&lrm; Project Detail روی Desktop و Mobile Hierarchy را بدون از دست‌رفتن دسترسی به Root Taskها نمایش می‌دهد.

---

## &rlm;17 — سناریوهای پذیرش End-to-End

### E2E-WG-001 — Simple Project

&rlm;Admin Project ایجاد می‌کند و مستقیماً Task می‌سازد. هیچ Work Group ساخته نمی‌شود. Customer Member Project Task را مانند جریان فعلی مشاهده و استفاده می‌کند.

### E2E-WG-002 — Structured Project

&rlm;Admin سه Work Group سطح ۱ می‌سازد و Taskها را بین آن‌ها تقسیم می‌کند. Customer همان Taskها را در ساختار Grouped مشاهده می‌کند و Visibility تغییری نمی‌کند.

### E2E-WG-003 — Maximum Depth

&rlm;Admin زنجیره پنج‌سطحی Work Group ایجاد می‌کند و Task را در Level 5 قرار می‌دهد. سیستم ایجاد Level 6 را Reject می‌کند.

### E2E-WG-004 — Safe Move

&rlm;Admin یک Work Group همراه Descendantهای آن را به Parent جدید منتقل می‌کند. اگر Depth نهایی حداکثر ۵ باشد عملیات موفق است؛ اگر هر Descendant به Level 6 برسد کل عملیات Reject می‌شود.

### E2E-WG-005 — Customer Isolation

&rlm;Customer عضو Project A می‌تواند Work Groupها و Taskهای Project A را ببیند ولی با URL/API مستقیم به Work Group Project B دسترسی ندارد.

### E2E-WG-006 — Task Reorganization

&rlm;Admin Task را از Root Project به یک Work Group و سپس به Work Group دیگری در همان Project منتقل می‌کند. Task Reference، Comment، Attachment و History ثابت باقی می‌مانند و Activity جابه‌جایی ثبت می‌شود.

---

## &rlm;18 — معیار موفقیت

&rlm;این قابلیت زمانی موفق است که:

- &rlm;Projectهای کوچک هیچ Complexity اضافی تجربه نکنند.
- &rlm;Projectهای بزرگ بتوانند تا ۵ Level ساختار قابل مدیریت داشته باشند.
- &rlm;یک Core Domain برای انواع Service شرکت استفاده شود و نیاز به Entity جداگانه برای SEO Epic، Development Epic یا Campaign Phase ایجاد نشود.
- &rlm;Task همچنان نقطه اصلی Assignment، Status، Priority، Conversation و Completion باقی بماند.
- &rlm;Customer Isolation و Project Membership Model فعلی بدون تغییر مفهومی حفظ شود.

---

## &rlm;19 — تصمیمات آینده

&rlm;قابلیت‌های زیر باید در PRDهای مستقل بررسی شوند و نباید به‌صورت ضمنی از این سند نتیجه گرفته شوند:

- Project / Service Templates
- Milestones
- Themes / Tags
- Team Members و Internal Assignment Model
- Task Dependencies
- Recurring Tasks
- Time Tracking
- Kanban / Calendar / Gantt Views
- Workload و Capacity
- Cross-project Portfolio
- Advanced Reporting

&rlm;قاعده توسعه: ابتدا از همین Hierarchy عمومی استفاده شود و فقط زمانی Entity جدید وارد Domain شود که Requirement واقعی با Work Group + Task قابل مدل‌سازی نباشد.
