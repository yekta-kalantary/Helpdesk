<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# Jira Cloud — Product Analysis

## &rlm;00 — هدف، Scope و روش تحلیل

&rlm;این سند Jira Cloud / Jira Software را به‌عنوان سومین Product Reference در کنار RISE CRM و Worksuite تحلیل می‌کند. هدف، استخراج Patternهای پایدار برای نسخه‌های بعدی Helpdesk است؛ نه کپی Jira و نه تغییر Scope فعلی MVP.

&rlm;داخل Scope: Software spaces، Work items، Work types، Workflow، Boards، Backlog، Sprint/Kanban، Fields، JQL/Search، Permission/Security، Collaboration، Dependencies، Timeline/Plans، Components، Versions/Releases، Automation، Forms، Reports/Dashboards و Development visibility.

&rlm;خارج از Scope: Jira Service Management، Customer Portal، Request/Queue/SLA، Incident/Problem/Change، Confluence Knowledge Base و Jira Product Discovery به‌عنوان محصول مستقل.

&rlm;در هر Domain سه چیز از هم جدا می‌شوند: **Observed capability**، **Extracted requirement** و **Product implication**. وجود Feature در Jira مجوز ورود آن به MVP یا Roadmap نیست؛ PRD منبع نهایی Scope است.

### &rlm;Actorهای اصلی

&rlm;Jira Administrator، Space Administrator، Board Administrator، Team Member، Assignee، Reporter/Creator، Watcher و Stakeholder/Form Submitter.

---

## &rlm;01 — Terminology و Core Model

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Jira Cloud در حال مهاجرت واژگان از `Project` به `Space` و از `Issue` به `Work item` است. Space مجموعه Work Itemهای مرتبط است و Space Key بخشی از شناسه انسانی Work Item می‌شود، مثل `ABC-123`. Work Item واحد اصلی Tracking است و می‌تواند Task، Bug، Story یا Work Type دیگر باشد. بخشی از JQL/API هنوز Legacy terminology را حفظ می‌کند و Atlassian اعلام کرده Queryهای موجود بدون تغییر می‌مانند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-CORE-001&lrm; Work Item باید Business Identifier انسانی و پایدار داشته باشد که از Primary Key داخلی مستقل باشد.
- &rlm;&lrm;FR-JIRA-CORE-002&lrm; Work Type باید از Work Item جدا مدل شود.
- &rlm;&lrm;FR-JIRA-CORE-003&lrm; Renameهای UI نباید Contractهای Search/API را بدون Migration strategy بشکنند.
- &rlm;&lrm;FR-JIRA-CORE-004&lrm; Parent/child relation باید داده صریح باشد، نه صرفاً Label.

### &rlm;دلالت برای Helpdesk

&rlm;Task Reference قابل ارجاع و مستقل از Database ID برای نسخه‌های بعدی ارزش معماری بالایی دارد. Terminology نمایشی نیز نباید مستقیماً نام Contract یا Table را تعیین کند.

---

## &rlm;02 — Space و Configuration Model

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Jira Software دو مدل اصلی Space دارد: **Team-managed** با تنظیمات محلی و استقلال بیشتر تیم، و **Company-managed** با Schemeها و Configuration اشتراکی و کنترل مرکزی Jira Admin. Space می‌تواند Work Types، Workflows، Fields، Permissions، Boards و Components خود را داشته باشد.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-SPACE-001&lrm; Space/Project باید مرز اصلی Scope برای Work و Configuration باشد.
- &rlm;&lrm;FR-JIRA-SPACE-002&lrm; Local configuration و shared configuration دو concern جدا هستند.
- &rlm;&lrm;FR-JIRA-SPACE-003&lrm; Shared scheme باید blast radius تغییرات را مشخص و قابل Audit کند.
- &rlm;&lrm;FR-JIRA-SPACE-004&lrm; Role داخل Space باید از Global permission قابل تفکیک باشد.

### &rlm;دلالت برای Helpdesk

&rlm;در آینده ابتدا Project-local configuration منطقی‌تر است. Shared Scheme فقط وقتی ارزش دارد که چند Project واقعاً Workflow/Field/Permission مشترک بخواهند.

---

## &rlm;03 — Work Item، Hierarchy و Fields

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Work Item دارای Work Type، Summary/Title، Description، Assignee، Priority، Labels، Due Date و Fieldهای استاندارد یا Custom است. Work Item می‌تواند Subtask داشته باشد و Parent برای hierarchy استفاده می‌شود. Atlassian در JQL جدید `Parent` را جایگزین Epic-link legacy کرده است.

&rlm;Work Typeها می‌توانند در Work Type Scheme گروه‌بندی شوند. Field Schemeها مشخص می‌کنند چه Fieldهایی برای چه Work Type/Space در دسترس، Required یا Optional باشند. Field Context نیز Default Value و Optionهای Field را کنترل می‌کند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-WORK-001&lrm; Work Type باید قابل Query و قابل توسعه باشد بدون تغییر مداوم Schema پایه Work Item.
- &rlm;&lrm;FR-JIRA-WORK-002&lrm; Subtask باید lifecycle/assignment مستقل و Context والد داشته باشد.
- &rlm;&lrm;FR-JIRA-FIELD-001&lrm; Custom Field definition باید از value جدا باشد.
- &rlm;&lrm;FR-JIRA-FIELD-002&lrm; Field availability و Field options/defaults دو concern جدا هستند.
- &rlm;&lrm;FR-JIRA-FIELD-003&lrm; Required بودن Field باید Context-aware باشد.

### &rlm;دلالت برای Helpdesk

&rlm;Subtask فقط وقتی باید اضافه شود که Checklist کافی نباشد. Custom Field نیز Feature پرهزینه‌ای است که Validation، Search، Reporting و UI را هم‌زمان پیچیده می‌کند و باید دیرتر وارد محصول شود.

---

## &rlm;04 — Workflow، Status و Board Column

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Workflow مسیر lifecycle Work Item است و از Statusها و Transitionها تشکیل می‌شود. در Company-managed spaces، Workflow Scheme می‌تواند Work Typeهای مختلف را به Workflowهای متفاوت وصل کند.

&rlm;Board Column با Status یکی نیست. یک Column می‌تواند چند Status را map کند و Jira Board را به‌عنوان Projection روی Workflow نشان می‌دهد. در برخی Board محاسبات، Column سمت راست Completion را نمایندگی می‌کند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-WF-001&lrm; Status باید مستقل از Board Column باشد.
- &rlm;&lrm;FR-JIRA-WF-002&lrm; Transition باید From/To و Ruleهای مجاز را مشخص کند.
- &rlm;&lrm;FR-JIRA-WF-003&lrm; Presentation grouping نباید lifecycle semantic را تغییر دهد.
- &rlm;&lrm;FR-JIRA-WF-004&lrm; تغییر Workflow برای Work Itemهای موجود به Mapping/Migration روشن نیاز دارد.

### &rlm;دلالت برای Helpdesk

&rlm;اگر Kanban اضافه شود، `task.status` نباید به Column تبدیل شود. Status Domain و Board Column Presentation باید مستقل بمانند.

---

## &rlm;05 — Board، Backlog، Ranking، Scrum و Kanban

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Board Work Itemها را بر اساس Filter و Status-to-Column mapping نمایش می‌دهد. Company-managed board می‌تواند با JQL Filter Work Itemهای یک یا چند Space را جمع کند. Backlog Work آینده را از Work فعال جدا و Rank را با drag-and-drop مدیریت می‌کند. Quick Filterها مانند Assignee، Parent/Epic، Label و Work Type برای محدودکردن View وجود دارند.

&rlm;Scrum از Sprint lifecycle، Sprint goal و Estimation با Story Points یا Time استفاده می‌کند. Jira Estimate را از Time Spent/Remaining Estimate جدا می‌بیند. Kanban نیز Continuous Flow، Backlog اختیاری و Column constraint/WIP limit را پشتیبانی می‌کند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-BOARD-001&lrm; Board باید View روی Work Items باشد و مالک داده نباشد.
- &rlm;&lrm;FR-JIRA-BOARD-002&lrm; Board membership باید Query/Filter-driven باشد.
- &rlm;&lrm;FR-JIRA-BOARD-003&lrm; Ordering باید Rank صریح و پایدار داشته باشد.
- &rlm;&lrm;FR-JIRA-SCRUM-001&lrm; Sprint باید lifecycle مستقل Planned/Active/Completed داشته باشد.
- &rlm;&lrm;FR-JIRA-SCRUM-002&lrm; Estimate باید از Time tracking جدا باشد.
- &rlm;&lrm;FR-JIRA-KANBAN-001&lrm; WIP limit باید constraint روی View/flow باشد، نه Status جدید.

### &rlm;دلالت برای Helpdesk

&rlm;Kanban ساده و Filterهای عملیاتی احتمالاً بسیار زودتر از Sprint/Story Point برای Client Task Management ارزش ایجاد می‌کنند. Scrum باید Optional بماند و فقط برای use case واقعی Software Delivery اضافه شود.

---

## &rlm;06 — Search، JQL و Saved Filters

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;JQL برای Advanced Search Field-aware استفاده می‌شود. Search قابل ذخیره‌شدن به‌عنوان Filter است و Saved Filter می‌تواند Share، Favorite، Subscribe، Export شود یا منبع Board، Report و Dashboard Gadget قرار گیرد.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-SEARCH-001&lrm; Search باید روی Fieldهای اصلی Domain قابل ترکیب باشد.
- &rlm;&lrm;FR-JIRA-SEARCH-002&lrm; Saved View باید Query definition را به‌عنوان Data ذخیره کند.
- &rlm;&lrm;FR-JIRA-SEARCH-003&lrm; Filter ownership/sharing باید Access-controlled باشد.
- &rlm;&lrm;FR-JIRA-SEARCH-004&lrm; Board/Report/Dashboard باید بتوانند از Filter مشترک reuse کنند.

### &rlm;دلالت برای Helpdesk

&rlm;نسخه‌های نزدیک به Filter ساده بر اساس Project، Status، Assignee، Priority و Due Date نیاز دارند. DSL مشابه JQL فقط در صورت نیاز واقعی به Queryهای پیچیده و reusable توجیه دارد.

---

## &rlm;07 — Permission، Role و Work-item Security

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Jira چند لایه Access Control دارد: Global Permissions، Permission Schemes برای capability داخل Space، Space Roles و Work Item Security Schemes برای محدودکردن Visibility رکوردهای خاص. Permission Scheme و Work-item Security دو مفهوم متفاوت‌اند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-AUTH-001&lrm; Authentication، global capability، space capability و record visibility باید جدا باشند.
- &rlm;&lrm;FR-JIRA-AUTH-002&lrm; Permission باید در صورت نیاز به Role/Group قابل Grant باشد.
- &rlm;&lrm;FR-JIRA-AUTH-003&lrm; Record-level security باید مستقل از Space membership باشد.
- &rlm;&lrm;FR-JIRA-AUTH-004&lrm; Visibility rule باید در Search، Board، Report و API یکسان enforce شود.

### &rlm;دلالت برای Helpdesk

&rlm;مدل ساده Admin/Customer و Project Membership برای MVP مناسب‌تر است. اگر Permission Engine بعداً اضافه شد، Capability و Scope باید از ابتدا جدا طراحی شوند؛ نه اینکه یک `role_id` همه چیز را حل کند.

---

## &rlm;08 — Collaboration، Activity و Linking

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Work Item می‌تواند Comment، Attachment، Watcher و Activity داشته باشد. Watcher با تغییرات Work Item Notification دریافت می‌کند. Permissionها می‌توانند Comment، Attachment و Watcher management را کنترل کنند.

&rlm;Work Item Linking روابط typed مانند `blocks / is blocked by`، `duplicates`، `clones` و `relates to` را نگه می‌دارد. Linkها می‌توانند Work را بین Spaceها متصل کنند و Timeline از `Blocks` برای نمایش Dependency استفاده می‌کند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-COLLAB-001&lrm; Comment/Attachment باید Visibility والد را رعایت کنند.
- &rlm;&lrm;FR-JIRA-COLLAB-002&lrm; Watcher باید رابطه مستقل User↔Work Item باشد.
- &rlm;&lrm;FR-JIRA-LINK-001&lrm; Relationship باید typed و directional باشد.
- &rlm;&lrm;FR-JIRA-LINK-002&lrm; Linked Work باید Access Control هر دو طرف را رعایت کند.

### &rlm;دلالت برای Helpdesk

&rlm;Watch/Subscribe و `blocks / blocked by` دو Candidate مفید برای نسخه‌های بعدی‌اند و از ساخت Notification preference یا Dependency engine عمومی ساده‌تر و هدفمندتر هستند.

---

## &rlm;09 — Timeline، Plans، Components و Releases

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Timeline زمان، Duration، Parent/Child Work و Dependency را Visualize می‌کند. Advanced Plans برنامه‌ریزی چندتیمی/چندSpace را ارائه می‌دهد و به Jira Cloud Premium/Enterprise وابسته است.

&rlm;Components در Company-managed spaces Work را بر اساس Feature/Department/Workstream گروه‌بندی می‌کنند و می‌توانند Owner و Auto-assignment داشته باشند.

&rlm;Version/Release مجموعه Work برای یک Release است و از Sprint مستقل می‌ماند. Version می‌تواند Released، Unreleased یا Archived باشد. Release hub در صورت اتصال Development tools، وضعیت Work، PR/Commit، Build و Deployment را کنار هم نشان می‌دهد.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-PLAN-001&lrm; Timeline باید از Dates/Relations Work Item مشتق شود و Data duplicate مستقل نسازد.
- &rlm;&lrm;FR-JIRA-COMP-001&lrm; Component باید taxonomy داخل Space باشد و Owner آن از Task Assignee مستقل بماند.
- &rlm;&lrm;FR-JIRA-REL-001&lrm; Release باید Aggregate مستقل از Sprint باشد.
- &rlm;&lrm;FR-JIRA-REL-002&lrm; External development metadata باید Reference-based باشد و منبع اصلی را duplicate نکند.

### &rlm;دلالت برای Helpdesk

&rlm;Timeline ساده و Release/Milestone برای Software projects می‌توانند مفید باشند. Portfolio planning چندProjectی و Component model باید تا ایجاد نیاز واقعی به تعویق بیفتند.

---

## &rlm;10 — Automation Rules

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Jira Automation مدل Trigger → Condition → Action دارد. Trigger می‌تواند Event-based مثل Work Item Created/Field Changed یا Scheduled باشد. Condition ادامه Flow را محدود می‌کند و Ruleها می‌توانند از JQL/Smart Values و Fieldهای استاندارد/Custom استفاده کنند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-AUTO-001&lrm; Automation باید Trigger/Condition/Action را composable جدا کند.
- &rlm;&lrm;FR-JIRA-AUTO-002&lrm; Rule execution باید Audit log و failure visibility داشته باشد.
- &rlm;&lrm;FR-JIRA-AUTO-003&lrm; Automation باید loop/recursion guard و execution permission context روشن داشته باشد.

### &rlm;دلالت برای Helpdesk

&rlm;General Rule Builder باید دیر ساخته شود. ابتدا چند Automation صریح و setting-driven بهتر است؛ وقتی Patternها تکرار شدند، Rule Engine عمومی توجیه پیدا می‌کند.

---

## &rlm;11 — Forms، Reports و Dashboards

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Jira Forms برای Intake از Stakeholderها استفاده می‌شوند. هر Form به Work Type متصل است و Submit response یک Work Item در همان Space ایجاد می‌کند.

&rlm;Reports داده Space، Sprint، Version و Work Item را تحلیل می‌کنند. نمونه‌ها شامل Burnup/Burndown/Velocity، Control Chart، Cumulative Flow، Created vs Resolved و Resolution Time هستند. Dashboard نیز چند Gadget از Filter/Spaceهای مختلف را در یک View جمع می‌کند.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-JIRA-FORM-001&lrm; Intake Form باید Mapping صریح به Work Type/Field داشته باشد.
- &rlm;&lrm;FR-JIRA-FORM-002&lrm; Form response و Work Item باید traceable باشند.
- &rlm;&lrm;FR-JIRA-REPORT-001&lrm; Report باید Scope و Source مشخص داشته باشد و Access Control منبع را رعایت کند.
- &rlm;&lrm;FR-JIRA-REPORT-002&lrm; Dashboard باید Composition از Gadget/Widgetها باشد نه Report monolith.

### &rlm;دلالت برای Helpdesk

&rlm;Client Request Form می‌تواند بعد از MVP Candidate خوبی باشد، بدون اینکه JSM Portal تقلید شود. قبل از Dashboard Builder نیز چند KPI/Report ثابت برای Project/Task ارزش بیشتری دارند.

---

## &rlm;12 — مدل مفهومی استخراج‌شده

```text
Space
├── Work Types
├── Workflows
├── Fields / Field Rules
├── Roles / Permissions
├── Boards
├── Components
├── Versions / Releases
└── Work Items
    ├── Parent / Children
    ├── Status
    ├── Assignee / Reporter
    ├── Priority / Labels / Dates
    ├── Comments / Attachments / Watchers
    ├── Links / Dependencies
    ├── Sprint Membership
    └── Development References

Board
├── Filter
├── Columns
│   └── Status mappings
├── Rank
└── Quick Filters

Automation Rule
├── Trigger
├── Conditions
└── Actions
```

&rlm;Board، Search، Report و Dashboard مالک Work Item نیستند؛ Projection یا Consumer داده Work Item هستند.

---

## &rlm;13 — Candidate Matrix برای نسخه‌های بعدی Helpdesk

| Capability | ارزش احتمالی | پیچیدگی | پیشنهاد |
|---|---:|---:|---|
| Kanban Board ساده | بالا | پایین/متوسط | Near-term candidate |
| Filterهای Task/Project | بالا | پایین | Near-term candidate |
| Saved Views | بالا | متوسط | بعد از Filterهای ساده |
| Labels | متوسط/بالا | پایین | Candidate نزدیک |
| Task Reference Key | بالا | پایین/متوسط | Candidate معماری |
| Subtasks | متوسط | متوسط | فقط با use case واقعی |
| `blocks / blocked by` | متوسط/بالا | متوسط | قبل از Dependency engine کامل |
| Watch/Subscribe | متوسط | پایین/متوسط | Candidate Notification |
| Timeline ساده | متوسط | متوسط | بعد از Dates پایدار |
| Versions/Releases | بالا برای Software teams | متوسط | نسخه‌های بعدی |
| Sprint/Story Points | وابسته به use case | متوسط/بالا | Optional، نه Core |
| Custom Fields | بالقوه بالا | بالا | دیرتر |
| Custom Workflow Engine | بالقوه بالا | خیلی بالا | دیرتر |
| Permission Schemes | بالا در سازمان بزرگ | بالا | فقط با Scopeهای واقعی |
| Automation Builder عمومی | بالا | خیلی بالا | بعد از Automationهای ثابت |
| Forms / Intake | بالا | متوسط | Candidate بعد از MVP |
| Dashboard Builder | متوسط | بالا | بعد از KPI/Report ثابت |
| Advanced multi-project Plans | پایین در وضعیت فعلی | خیلی بالا | دور از MVP |

---

## &rlm;14 — Patternهایی که باید قرض گرفته شوند، نه Complexity Jira

- &rlm;Status را از Board Column جدا نگه دار.
- &rlm;Permission Capability را از Record Visibility جدا نگه دار.
- &rlm;Task/Sprint/Release/Plan را Aggregateهای متفاوت بدان.
- &rlm;Saved Query/View را در صورت نیاز reusable طراحی کن.
- &rlm;External development metadata را Reference کن، نه duplicate.
- &rlm;Custom Field/Workflow/Permission Scheme را فقط با Use Case واقعی بساز.
- &rlm;Scrum ceremony و Story Point را صرفاً به دلیل وجود در Jira به Client Task Management تحمیل نکن.

---

## &rlm;15 — منابع رسمی بررسی‌شده

&rlm;همه منابع زیر Official Atlassian Support هستند و در 2026-08-13 بررسی شده‌اند:

- Space model: https://support.atlassian.com/jira-software-cloud/docs/what-is-a-jira-software-project/
- Work items: https://support.atlassian.com/jira-software-cloud/docs/what-is-a-work-item/
- Work item/subtask creation: https://support.atlassian.com/jira-software-cloud/docs/create-a-work-item-and-a-subtask/
- Workflows: https://support.atlassian.com/jira-software-cloud/docs/what-are-jira-workflows/
- Board columns/status mapping: https://support.atlassian.com/jira-software-cloud/docs/configure-columns/
- Backlog: https://support.atlassian.com/jira-software-cloud/docs/use-your-scrum-backlog/
- Sprints: https://support.atlassian.com/jira-software-cloud/docs/coordinate-and-monitor-work-with-sprints/
- Estimation: https://support.atlassian.com/jira-software-cloud/docs/estimate-an-issue/
- Board filters/JQL: https://support.atlassian.com/jira-software-cloud/docs/configure-filters/
- JQL fields: https://support.atlassian.com/jira-software-cloud/docs/jql-fields/
- Saved filters: https://support.atlassian.com/jira-software-cloud/docs/save-your-search-as-a-filter/
- Permission schemes: https://support.atlassian.com/jira-cloud-administration/docs/what-are-permission-schemes-in-jira/
- Work item security: https://support.atlassian.com/jira-cloud-administration/docs/configure-issue-security-schemes/
- Field schemes: https://support.atlassian.com/jira-cloud-administration/docs/what-are-field-schemes/
- Work type schemes: https://support.atlassian.com/jira-cloud-administration/docs/what-are-issue-type-schemes/
- Comments/watchers: https://support.atlassian.com/jira-software-cloud/docs/watch-share-and-comment-on-a-work-item/
- Linked work: https://support.atlassian.com/jira-software-cloud/docs/link-issues/
- Timeline dependencies: https://support.atlassian.com/jira-software-cloud/docs/manage-dependencies-between-epics-on-the-timeline/
- Components: https://support.atlassian.com/jira-software-cloud/docs/what-are-jira-components/
- Releases/versions: https://support.atlassian.com/jira-software-cloud/docs/enable-releases-and-versions/
- Release status/development data: https://support.atlassian.com/jira-software-cloud/docs/check-the-progress-of-a-version/
- Automation triggers: https://support.atlassian.com/cloud-automation/docs/jira-automation-triggers/
- Automation conditions: https://support.atlassian.com/cloud-automation/docs/jira-automation-conditions/
- Forms: https://support.atlassian.com/jira-software-cloud/docs/what-are-forms-and-what-can-they-do/
- Reports: https://support.atlassian.com/jira-software-cloud/docs/generate-a-report/
- Dashboards: https://support.atlassian.com/jira-software-cloud/docs/what-is-a-jira-dashboard/

## &rlm;جمع‌بندی

&rlm;ارزش Jira برای پروژه ما در تعداد Featureها نیست؛ در جداسازی درست concernهاست: Work Item از Board مستقل است، Status از Column مستقل است، Permission از Record Security مستقل است، Sprint از Release مستقل است و Search/Filter می‌تواند چند View/Report را تغذیه کند. برای نسخه‌های بعدی Helpdesk باید همین Patternها را قرض گرفت، نه پیچیدگی Jira را.
