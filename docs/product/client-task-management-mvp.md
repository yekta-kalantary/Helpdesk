<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# PRD — MVP مدیریت تسک بین مدیر و مشتری

## &rlm;00 — نمای کلی MVP


### &rlm;وضعیت سند

&rlm;این سند مرجع رسمی Product Requirements Document برای نسخه MVP است. تحلیل‌های RISE CRM و Worksuite فقط به‌عنوان Reference Product استفاده می‌شوند و Scope این سند را تعیین نمی‌کنند.

&rlm;Revision فعلی: MVP v1.1 — 2026-08-11.
&rlm;مبنای این Revision: [RISE Base Research](../research/rise-crm-product-analysis.md)، [RISE Re-validation 2026-08-11](../research/rise-crm-revalidation-2026-08-11.md)، [Worksuite Base Research](../research/worksuite-product-analysis.md) و [Worksuite Re-validation 2026-08-11](../research/worksuite-revalidation-2026-08-11.md).
&rlm;قاعده تصمیم: Research برای کشف Pattern و Risk استفاده می‌شود؛ ورود هر قابلیت به MVP فقط با تصمیم صریح همین PRD مجاز است.

### &rlm;چشم‌انداز محصول

&rlm;یک فضای مشترک ساده و قابل اعتماد برای مدیریت کار بین مدیر سیستم و مشتری؛ به‌گونه‌ای که هر مشتری فقط پروژه‌های مجاز خودش را ببیند، Taskهای همان پروژه را پیگیری کند و گفتگو و فایل‌های مرتبط با کار در همان Context باقی بمانند.

### &rlm;مسئله‌ای که MVP حل می‌کند

&rlm;در همکاری با مشتری، Taskها، وضعیت انجام کار، فایل‌ها و گفتگوها معمولاً بین پیام‌رسان، ایمیل و یادداشت‌های شخصی پخش می‌شوند. نتیجه، ابهام در مسئول فعلی کار، از دست رفتن Context و سخت شدن پیگیری است. MVP باید یک منبع واحد برای «چه کاری، برای کدام پروژه، دست چه کسی و در چه وضعیتی» ایجاد کند.

### &rlm;هدف MVP

&rlm;هدف نسخه اول ساخت CRM، Helpdesk یا ERP نیست. هدف فقط ایجاد چرخه کامل Client → Project → Membership → Task → Conversation → Completion است.

### &rlm;Actorهای MVP

&rlm;Admin: مدیر سیستم با دسترسی کامل به همه Clientها، Projectها و Taskها.
&rlm;Customer: کاربر متعلق به یک Client که فقط از طریق Project Membership به اطلاعات دسترسی دارد.

### &rlm;Scope عملکردی MVP

&rlm;احراز هویت و مدیریت User؛ مدیریت Client؛ مدیریت Project و Project Membership؛ ایجاد و مدیریت Task؛ Assignment؛ وضعیت و اولویت؛ Comment؛ Attachment؛ Activity Log؛ In-app Notification؛ Email Notification؛ Dashboard و Task List.

### &rlm;مسیر اصلی کاربر

&rlm;Admin یک Client می‌سازد؛ برای Client یک Customer User ایجاد می‌کند؛ Project می‌سازد؛ Customer User را عضو Project می‌کند؛ Admin یا Customer در Project Task ایجاد می‌کند؛ Task در صورت نیاز وارد Admin Queue و سپس Assign می‌شود؛ گفتگو و فایل داخل Task ثبت می‌شود؛ وضعیت Task تغییر می‌کند؛ Task در نهایت Completed یا Cancelled می‌شود.

### &rlm;اصول محصول

&rlm;اصل ۱ — Isolation: هیچ Customer نباید داده Client یا Project دیگری را مشاهده کند.
&rlm;اصل ۲ — Context First: گفتگو و فایل باید به Task مربوط باقی بماند؛ Chat عمومی در MVP نداریم.
&rlm;اصل ۳ — Simple Permissions: فقط دو Role ثابت Admin و Customer وجود دارد.
&rlm;اصل ۴ — Membership Based Access: دسترسی Customer از عضویت Project می‌آید، نه از حدس URL یا مالکیت Client به‌تنهایی.
&rlm;اصل ۵ — No Feature Creep: هر Feature که مستقیماً چرخه Task Collaboration را کامل نمی‌کند خارج از MVP است.
&rlm;اصل ۶ — Traceability: تغییرات مهم Task باید قابل رهگیری باشند.
&rlm;اصل ۷ — No Hard Delete From UI: داده عملیاتی از UI به‌صورت دائمی حذف نمی‌شود؛ Lifecycle با Inactive، Completed، Cancelled یا پایان Membership مدیریت می‌شود.
&rlm;اصل ۸ — Explicit Boundaries: Client Account، Login User، Project Membership و Task Assignment چهار مفهوم جدا هستند و نباید به‌جای یکدیگر استفاده شوند.

### &rlm;تعاریف کلیدی

&rlm;Client: حساب مشتری؛ می‌تواند یک یا چند Customer User داشته باشد.
&rlm;User: هویت قابل Login؛ در MVP Role آن Admin یا Customer است.
&rlm;Customer User: هویت Login یک شخص وابسته به Client؛ در MVP جایگزین Contact Directory مستقل نیست.
&rlm;Project: فضای کاری متعلق به یک Client.
&rlm;Project Member: Customer User مجاز به مشاهده و تعامل با یک Project در یک بازه Membership فعال.
&rlm;Task: واحد اصلی کار داخل Project.
&rlm;Assignee: User مشخصی که مسئول اقدام بعدی روی Task است؛ Visibility از Membership می‌آید نه Assignment.
&rlm;Admin Queue: Taskهایی با Status=Waiting Admin که اقدام بعدی از Admin لازم دارند و ممکن است هنوز Assignee مشخص نداشته باشند.
&rlm;Comment: پیام Contextual داخل Task.
&rlm;Attachment: فایل مرتبط با Task یا Comment.
&rlm;Activity: رکورد سیستمی تغییر مهم روی Task یا Project.

### &rlm;معیار موفقیت MVP

&rlm;یک Admin بتواند بدون ابزار جانبی Client، User، Project و Task را مدیریت کند.
&rlm;یک Customer بتواند Login کند و فقط Projectهای عضو شده را ببیند.
&rlm;Task از ایجاد تا تکمیل، شامل Assignment، Status، Comment و File، کاملاً داخل سیستم قابل پیگیری باشد.
&rlm;هیچ مسیر شناخته‌شده‌ای برای دسترسی Customer به داده Client دیگر وجود نداشته باشد.
&rlm;رابط در موبایل و دسکتاپ برای جریان‌های اصلی قابل استفاده باشد.

### &rlm;خارج از محدوده MVP

&rlm;Lead و Sales Pipeline؛ Contact Directory مستقل؛ Proposal و Estimate؛ Contract؛ Invoice و Payment؛ HR؛ Attendance و Leave؛ Ticket/Helpdesk مستقل؛ Knowledge Base؛ Product/Order/Subscription؛ Time Tracking؛ Gantt؛ Milestone؛ Kanban؛ Calendar؛ Project Archive؛ Chat عمومی؛ Custom Role/Permission Builder؛ Custom Fields؛ Automation/AI؛ API/Webhook؛ Recurring Task؛ Task Dependency؛ Comment Editing؛ SMS/Push/Slack Integration.

### &rlm;خلاصه بازبینی 2026-08-11

&rlm;این Revision چند Pattern را از Researchهای جدید تثبیت می‌کند بدون افزایش Scope: جدایی Client از Login User، Project-scoped access، Membership lifecycle قابل Audit، Task Reference انسانی و مستقل از PK، و تفکیک Admin Queue از Assignee مشخص.

&rlm;همزمان چند قابلیت رایج محصولات مرجع عمداً وارد MVP نشده‌اند: Project Archive، Contact Directory، Task Dependency، Time Tracking، Kanban/Gantt، Ticket/Helpdesk، Chat عمومی، AI و Add-onها.

### &rlm;تصمیم محصول

&rlm;این PRD عمداً کوچک نگه داشته شده است. هر قابلیت جدید فقط زمانی وارد MVP می‌شود که نبود آن مانع تکمیل جریان اصلی Client Task Collaboration باشد. Research می‌تواند دلیل بازبینی باشد ولی به‌تنهایی مجوز Scope Expansion نیست.

## &rlm;01 — کاربران و دسترسی

### &rlm;هدف دامنه

&rlm;فراهم‌کردن احراز هویت ساده و امن با دو Role ثابت و اعمال Authorization قطعی در سمت Server.

### &rlm;Actorها

Admin و Customer.

### &rlm;Use Caseها

- &rlm;&lrm;UC-USR-001&lrm; Admin یک Customer User برای Client موجود ایجاد می‌کند.
- &rlm;&lrm;UC-USR-002&lrm; Customer با Email و Password وارد سیستم می‌شود.
- &rlm;&lrm;UC-USR-003&lrm; User رمز عبور فراموش‌شده را بازیابی می‌کند.
- &rlm;&lrm;UC-USR-004&lrm; Admin یک User را Inactive یا Active می‌کند.
- &rlm;&lrm;UC-USR-005&lrm; Customer اطلاعات پایه پروفایل و Password خودش را مدیریت می‌کند.
- &rlm;&lrm;UC-USR-006&lrm; Admin لیست Userها را مشاهده و بر اساس نام، Email، Client و Status جست‌وجو/فیلتر می‌کند.

### &rlm;نیازمندی‌های عملکردی

- &rlm;&lrm;FR-USR-001&lrm; سیستم باید دقیقاً دو Role سیستمی Admin و Customer داشته باشد.
- &rlm;&lrm;FR-USR-002&lrm; Roleهای سیستمی در MVP قابل ایجاد، حذف یا تغییر نام نیستند.
- &rlm;&lrm;FR-USR-003&lrm; Public Registration وجود ندارد؛ Customer User فقط توسط Admin ایجاد می‌شود.
- &rlm;&lrm;FR-USR-004&lrm; هر Customer User باید به دقیقاً یک Client متصل باشد.
- &rlm;&lrm;FR-USR-005&lrm; Admin می‌تواند به همه منابع MVP دسترسی داشته باشد و برای دسترسی به Project نیاز به Membership ندارد.
- &rlm;&lrm;FR-USR-006&lrm; Customer فقط از طریق Project Membership به Project و Task دسترسی دارد.
- &rlm;&lrm;FR-USR-007&lrm; User غیرفعال نباید بتواند Login کند یا Session جدید بسازد.
- &rlm;&lrm;FR-USR-008&lrm; سیستم باید Forgot Password و Account Setup از طریق Email را پشتیبانی کند.
- &rlm;&lrm;FR-USR-009&lrm; Email باید در کل Userها به‌صورت Case-insensitive یکتا باشد؛ Inactive شدن User اجازه استفاده مجدد از همان Email را نمی‌دهد.
- &rlm;&lrm;FR-USR-010&lrm; Customer نمی‌تواند Role، Client یا Status خودش را تغییر دهد.
- &rlm;&lrm;FR-USR-011&lrm; Customer می‌تواند نام و Password خودش را تغییر دهد؛ تغییر Email فقط توسط Admin انجام می‌شود.
- &rlm;&lrm;FR-USR-012&lrm; تمام Authorizationها باید در Backend enforce شوند و مخفی‌کردن UI به‌تنهایی کنترل دسترسی محسوب نمی‌شود.
- &rlm;&lrm;FR-USR-013&lrm; منطق سیستم نباید وجود یک Admin ثابت یا یک Admin ID خاص را فرض کند؛ Role=Admin می‌تواند بیش از یک User فعال داشته باشد.

### &rlm;Business Ruleها

- &rlm;&lrm;BR-USR-001&lrm; User با Role=Customer بدون Client معتبر قابل ایجاد نیست.
- &rlm;&lrm;BR-USR-002&lrm; User غیرفعال نه به Membership جدید اضافه می‌شود و نه Assignee یک Task جدید یا باز قرار می‌گیرد.
- &rlm;&lrm;BR-USR-003&lrm; Customer User فقط می‌تواند عضو Projectهایی باشد که Client آن Project با Client خود User یکسان است.
- &rlm;&lrm;BR-USR-004&lrm; Admin می‌تواند Customer User را غیرفعال کند بدون اینکه History، Comment یا Taskهای قبلی حذف شوند.
- &rlm;&lrm;BR-USR-005&lrm; اگر Client غیرفعال شود، Login تمام Customer Userهای آن Client مسدود می‌شود تا Client دوباره فعال شود.
- &rlm;&lrm;BR-USR-006&lrm; User حذف دائمی از UI ندارد.

### &rlm;Data Requirementها

&rlm;User باید حداقل Name، Email، Role، Status و Created At داشته باشد.
&rlm;Email قبل از مقایسه/ذخیره باید Normalize شود و Unique Constraint سراسری داشته باشد.
&rlm;Customer User علاوه بر موارد بالا باید Client Reference داشته باشد.
&rlm;Mobile در MVP اختیاری است.
&rlm;Last Login At برای پشتیبانی و Audit توصیه می‌شود.
&rlm;Password فقط به‌صورت Hash امن نگهداری می‌شود.

### &rlm;Workflow — ایجاد Customer User

&rlm;Admin وارد Client می‌شود؛ Create User را انتخاب می‌کند؛ Name و Email را ثبت می‌کند؛ سیستم User را با Role=Customer و Status=Active می‌سازد؛ Account Setup Email ارسال می‌شود؛ User Password را تعیین می‌کند؛ سپس بر اساس Project Membership به پروژه‌ها دسترسی می‌گیرد.

### &rlm;Permissionهای دامنه

&rlm;Admin: List/Create/Edit/Activate/Deactivate همه Userها.
&rlm;Customer: مشاهده و ویرایش Profile شخصی؛ تغییر Password؛ بدون دسترسی به مدیریت Userهای دیگر.

### Acceptance Criteria

- &rlm;&lrm;AC-USR-001&lrm; Customer بدون Membership هیچ Projectی را نمی‌بیند.
- &rlm;&lrm;AC-USR-002&lrm; Customer با دستکاری URL/API نمی‌تواند User، Project یا Task خارج از دسترسی را بخواند.
- &rlm;&lrm;AC-USR-003&lrm; User غیرفعال نمی‌تواند Login کند.
- &rlm;&lrm;AC-USR-004&lrm; Admin می‌تواند User جدید بسازد و User پس از Account Setup وارد شود.
- &rlm;&lrm;AC-USR-005&lrm; تغییر Role و Client از سمت Customer از UI و API غیرمجاز است.
- &rlm;&lrm;AC-USR-006&lrm; ایجاد User دوم با Email یکسان، حتی اگر User قبلی Inactive باشد، Reject می‌شود.

## &rlm;02 — مشتریان

### &rlm;هدف دامنه

&rlm;مدل‌کردن Client به‌عنوان حساب مشتری مستقل از User؛ به‌طوری‌که یک Client بتواند چند User و چند Project داشته باشد.

### &rlm;تعریف محصول

&rlm;Client شخص Login‌کننده نیست. Client حساب مشتری است و Customer User هویتی است که از طرف Client وارد سیستم می‌شود. این جداسازی همان Pattern تأییدشده در محصولات مرجع را با ساده‌ترین شکل MVP حفظ می‌کند.

&rlm;در MVP موجودیت Contact مستقل نداریم؛ Customer User مستقیماً به Client متصل می‌شود. اگر بعداً Contact Directory وارد محصول شود، Contact و Login User می‌توانند از هم جدا شوند بدون اینکه مرز Client → Project → Task تغییر کند.

### &rlm;Use Caseها

- &rlm;&lrm;UC-CLI-001&lrm; Admin Client جدید ایجاد می‌کند.
- &rlm;&lrm;UC-CLI-002&lrm; Admin اطلاعات Client را ویرایش می‌کند.
- &rlm;&lrm;UC-CLI-003&lrm; Admin Client را Active/Inactive می‌کند.
- &rlm;&lrm;UC-CLI-004&lrm; Admin صفحه Client را باز می‌کند و Userها و Projectهای مرتبط را می‌بیند.
- &rlm;&lrm;UC-CLI-005&lrm; Admin Clientها را بر اساس Name و Status جست‌وجو/فیلتر می‌کند.

### &rlm;نیازمندی‌های عملکردی

- &rlm;&lrm;FR-CLI-001&lrm; فقط Admin می‌تواند Client ایجاد یا ویرایش کند.
- &rlm;&lrm;FR-CLI-002&lrm; Client باید Name و Status داشته باشد.
- &rlm;&lrm;FR-CLI-003&lrm; Client می‌تواند چند Customer User داشته باشد.
- &rlm;&lrm;FR-CLI-004&lrm; Client می‌تواند چند Project داشته باشد.
- &rlm;&lrm;FR-CLI-005&lrm; صفحه Client باید حداقل Summary، Userها و Projectهای مرتبط را نمایش دهد.
- &rlm;&lrm;FR-CLI-006&lrm; Client List باید Search بر اساس Name و Filter بر اساس Status داشته باشد.
- &rlm;&lrm;FR-CLI-007&lrm; Client غیرفعال همچنان برای Admin و History قابل مشاهده است.
- &rlm;&lrm;FR-CLI-008&lrm; برای Client غیرفعال Project جدید ساخته نمی‌شود و Customer Userهای آن امکان Login ندارند.

### &rlm;Business Ruleها

- &rlm;&lrm;BR-CLI-001&lrm; Statusهای MVP فقط Active و Inactive هستند.
- &rlm;&lrm;BR-CLI-002&lrm; Inactive کردن Client نباید Project، Task، Comment، Attachment یا Activity قبلی را حذف کند.
- &rlm;&lrm;BR-CLI-003&lrm; Client از UI Hard Delete نمی‌شود.
- &rlm;&lrm;BR-CLI-004&lrm; Customer User نمی‌تواند Client Profile را مدیریت کند؛ در MVP اطلاعات Client فقط توسط Admin نگهداری می‌شود.
- &rlm;&lrm;BR-CLI-005&lrm; Contact Directory مستقل، Lead و نوع‌بندی CRM مشتری در MVP وجود ندارد؛ Customer User فقط Login Identity سبک وابسته به Client است.

### &rlm;Data Requirementها

&rlm;Client: Name الزامی؛ Description اختیاری؛ Status الزامی؛ Created At و Updated At.
&rlm;اطلاعات تجاری گسترده مانند Address، Tax ID، Industry، Lead Source و Billing Profile در MVP اجباری نیستند.

### &rlm;Workflow — Onboarding مشتری

&rlm;Admin Client را ایجاد می‌کند؛ Customer Userهای لازم را به Client اضافه می‌کند؛ سپس Project ایجاد و Userهای مجاز را Member می‌کند. ایجاد Client به‌تنهایی هیچ Project Access به Customer نمی‌دهد.

### &rlm;Permissionهای دامنه

&rlm;Admin: دسترسی کامل به Client.
&rlm;Customer: فقط از طریق Projectهای مجاز Context مشتری خود را تجربه می‌کند و صفحه مدیریت Client ندارد.

### Acceptance Criteria

- &rlm;&lrm;AC-CLI-001&lrm; یک Client می‌تواند حداقل دو Customer User مستقل داشته باشد.
- &rlm;&lrm;AC-CLI-002&lrm; User یک Client نمی‌تواند Project متعلق به Client دیگر را Member شود.
- &rlm;&lrm;AC-CLI-003&lrm; Inactive کردن Client داده‌های قبلی را حفظ و دسترسی Customer را متوقف می‌کند.
- &rlm;&lrm;AC-CLI-004&lrm; Client بدون Project نیز قابل ایجاد و نگهداری است.

## &rlm;03 — پروژه‌ها و عضویت

### &rlm;هدف دامنه

&rlm;ایجاد مرز اصلی دسترسی و Context کاری. Project متعلق به یک Client است و Customer فقط در صورت Membership آن را مشاهده می‌کند.

### &rlm;Use Caseها

- &rlm;&lrm;UC-PRJ-001&lrm; Admin برای Client فعال Project ایجاد می‌کند.
- &rlm;&lrm;UC-PRJ-002&lrm; Admin Customer Userهای همان Client را به Project اضافه یا از Membership خارج می‌کند.
- &rlm;&lrm;UC-PRJ-003&lrm; Admin Project را ویرایش، Complete یا Reopen می‌کند.
- &rlm;&lrm;UC-PRJ-004&lrm; Customer لیست Projectهای عضو شده را مشاهده می‌کند.
- &rlm;&lrm;UC-PRJ-005&lrm; Customer وارد Project Detail می‌شود و Taskها و Memberهای مجاز را می‌بیند.
- &rlm;&lrm;UC-PRJ-006&lrm; Admin Projectها را بر اساس Client و Status جست‌وجو/فیلتر می‌کند.

### &rlm;نیازمندی‌های عملکردی

- &rlm;&lrm;FR-PRJ-001&lrm; هر Project باید دقیقاً به یک Client متصل باشد.
- &rlm;&lrm;FR-PRJ-002&lrm; فقط Admin می‌تواند Project ایجاد کند.
- &rlm;&lrm;FR-PRJ-003&lrm; Project باید Name، Description اختیاری، Status، Start Date اختیاری و Due Date اختیاری داشته باشد.
- &rlm;&lrm;FR-PRJ-004&lrm; Admin می‌تواند یک یا چند Customer User از همان Client را Member Project کند.
- &rlm;&lrm;FR-PRJ-005&lrm; Customer فقط Projectهایی را می‌بیند که Membership فعال در آن‌ها دارد.
- &rlm;&lrm;FR-PRJ-006&lrm; Membership مبنای Visibility تمام Taskهای Project برای Customer است.
- &rlm;&lrm;FR-PRJ-007&lrm; Customer عضو Project همه Taskهای همان Project را می‌بیند؛ Per-Task Visibility در MVP وجود ندارد.
- &rlm;&lrm;FR-PRJ-008&lrm; Project Detail باید Summary، Members، Task List و Activityهای اصلی را نمایش دهد.
- &rlm;&lrm;FR-PRJ-009&lrm; Admin همه Projectها را مستقل از Membership می‌بیند.
- &rlm;&lrm;FR-PRJ-010&lrm; Project Completed برای Customer Read-only است.
- &rlm;&lrm;FR-PRJ-011&lrm; Admin می‌تواند Project Completed را دوباره Active کند.
- &rlm;&lrm;FR-PRJ-012&lrm; Project از UI Hard Delete نمی‌شود.
- &rlm;&lrm;FR-PRJ-013&lrm; Membership باید Lifecycle قابل Audit داشته باشد و خروج Member نباید History Membership را حذف کند.
- &rlm;&lrm;FR-PRJ-014&lrm; Re-add کردن User به Project باید همان Membership تاریخی را دوباره فعال کند، نه اینکه رکورد هم‌معنی جدید بسازد.
- &rlm;&lrm;FR-PRJ-015&lrm; Customer در Member List فقط اطلاعات لازم برای همکاری مانند Name را می‌بیند؛ Email، Mobile و اطلاعات خصوصی سایر Memberها نمایش داده نمی‌شود.

### &rlm;Statusهای Project

&rlm;Active: Project قابل تعامل و Task جدید قابل ایجاد است.
&rlm;Completed: کار Project پایان یافته؛ Project برای Customer Read-only است و Task/Comment/Attachment جدید ایجاد نمی‌شود.

&rlm;Archive در MVP وجود ندارد؛ Completed Project در List با Filter قابل مدیریت است. Archive فقط در صورت نیاز واقعی به Post-MVP منتقل می‌شود.

### &rlm;Business Ruleها

- &rlm;&lrm;BR-PRJ-001&lrm; فقط Customer User فعال با Client یکسان می‌تواند Member شود.
- &rlm;&lrm;BR-PRJ-002&lrm; خارج‌کردن User از Project باید Membership را End کند؛ دسترسی بعدی قطع می‌شود ولی Membership، Comment و Activity قبلی حفظ می‌شوند.
- &rlm;&lrm;BR-PRJ-003&lrm; Customer User نمی‌تواند Membership ایجاد، حذف یا تغییر دهد.
- &rlm;&lrm;BR-PRJ-004&lrm; Task Assignee از سمت Customer باید عضو همان Project باشد.
- &rlm;&lrm;BR-PRJ-005&lrm; تغییر Client یک Project پس از ایجاد در MVP مجاز نیست؛ برای جلوگیری از نشت History باید Project جدید ساخته شود.
- &rlm;&lrm;BR-PRJ-006&lrm; Project Completed اجازه ایجاد Task، Comment یا Attachment جدید را نمی‌دهد؛ برای ادامه کار Admin ابتدا Project را Reopen می‌کند.
- &rlm;&lrm;BR-PRJ-007&lrm; Project فقط زمانی Completed می‌شود که هیچ Task با Status غیرCompleted/Cancelled نداشته باشد.
- &rlm;&lrm;BR-PRJ-008&lrm; Customer Member List نباید Email، Mobile یا داده خصوصی Userهای دیگر را افشا کند.

### &rlm;Data Requirementها

&rlm;Project: Client Reference، Name، Description، Status، Start Date، Due Date، Created At، Updated At.
&rlm;Project Membership: Project Reference، Customer User Reference، Joined At، Removed At اختیاری.
&rlm;Membership فعال یعنی Removed At خالی است.
&rlm;Membership باید Unique روی ترکیب Project + User باشد؛ Re-add با خالی‌کردن Removed At انجام می‌شود.

### &rlm;Workflow — ایجاد Project

&rlm;Admin Client فعال را انتخاب می‌کند؛ Project را با Name و اطلاعات پایه می‌سازد؛ Customer Userهای مجاز همان Client را Member می‌کند؛ Project فعال می‌شود؛ Memberها پس از Login Project را در لیست خود می‌بینند.

### &rlm;Workflow — بستن Project

&rlm;Admin ابتدا تمام Taskهای باز را Completed یا Cancelled می‌کند؛ سپس Project را Completed می‌کند؛ همه Interactionهای جدید متوقف می‌شوند؛ در صورت نیاز Admin Project را Reopen می‌کند.

### Acceptance Criteria

- &rlm;&lrm;AC-PRJ-001&lrm; Customer عضو Project A آن را می‌بیند و Customer غیرعضو همان Client آن را نمی‌بیند.
- &rlm;&lrm;AC-PRJ-002&lrm; Member هیچ Project از Client دیگر را از UI یا API نمی‌بیند.
- &rlm;&lrm;AC-PRJ-003&lrm; Member خارج‌شده بلافاصله Access جدید به Project ندارد و Membership History او باقی می‌ماند.
- &rlm;&lrm;AC-PRJ-004&lrm; Completed Project برای Customer Read-only است.
- &rlm;&lrm;AC-PRJ-005&lrm; Admin بدون Membership به همه Projectها دسترسی دارد.
- &rlm;&lrm;AC-PRJ-006&lrm; Project دارای Task باز نمی‌تواند Completed شود.
- &rlm;&lrm;AC-PRJ-007&lrm; Re-add کردن Member قبلی Access را برمی‌گرداند بدون ایجاد Membership تکراری.

## &rlm;04 — تسک‌ها

### &rlm;هدف دامنه

&rlm;Task هسته MVP است. تمام همکاری اجرایی بین Admin و Customer باید در Context یک Project و یک Task قابل پیگیری باشد.

### &rlm;مدل Task در MVP

&rlm;هر Task دقیقاً متعلق به یک Project است، یک Creator دارد و در هر لحظه حداکثر یک Assignee دارد. همه Customer Memberهای Project Task را می‌بینند؛ Assignment مسئول اقدام بعدی را مشخص می‌کند، نه Visibility را. تنها حالت فعال که می‌تواند بدون Assignee باشد Waiting Admin است؛ این حالت Admin Queue را نشان می‌دهد.

### &rlm;Use Caseها

- &rlm;&lrm;UC-TSK-001&lrm; Admin در Project فعال Task ایجاد و آن را به Admin یا Customer Member Assign می‌کند.
- &rlm;&lrm;UC-TSK-002&lrm; Customer در Project فعال Task جدید ثبت می‌کند؛ Task با Status=Waiting Admin و بدون Assignee مشخص وارد Admin Queue می‌شود.
- &rlm;&lrm;UC-TSK-003&lrm; Admin Title، Description، Priority، Due Date، Assignee و Status را مدیریت می‌کند.
- &rlm;&lrm;UC-TSK-004&lrm; Customer Taskهای Project را مشاهده و روی Taskهای Assign‌شده به خودش Status را در محدوده مجاز تغییر می‌دهد.
- &rlm;&lrm;UC-TSK-005&lrm; User Task را باز می‌کند، Comment می‌گذارد و File پیوست می‌کند.
- &rlm;&lrm;UC-TSK-006&lrm; User Taskها را بر اساس Project، Status، Priority، Assignee و Due Date فیلتر می‌کند و بر اساس Title جست‌وجو می‌کند.
- &rlm;&lrm;UC-TSK-007&lrm; Admin Task اشتباه یا نامعتبر را Cancel و در صورت نیاز Reopen می‌کند.

### &rlm;فیلدهای محصول

&rlm;Title الزامی.
&rlm;Description اختیاری.
&rlm;Project الزامی.
&rlm;Created By الزامی.
&rlm;Assigned To برای Todo، In Progress و Waiting Customer الزامی است؛ در Waiting Admin می‌تواند خالی باشد.
&rlm;Status الزامی.
&rlm;Priority الزامی با Default=Normal.
&rlm;Due Date اختیاری.
&rlm;Completed At فقط هنگام Completed شدن ثبت می‌شود.
&rlm;Created At و Updated At الزامی.

### &rlm;Priorityهای MVP

&rlm;Low، Normal، High. Priority سفارشی در MVP وجود ندارد.

### &rlm;Statusهای MVP

&rlm;Todo: Task ایجاد شده ولی کار شروع نشده است.
&rlm;In Progress: کار در حال انجام است.
&rlm;Waiting Admin: اقدام بعدی از سمت Admin لازم است؛ Task می‌تواند در Admin Queue بدون Assignee باشد یا به یک Admin مشخص Assign شده باشد.
&rlm;Waiting Customer: اقدام بعدی از سمت Customer لازم است.
&rlm;Completed: Task تمام شده است.
&rlm;Cancelled: Task بدون انجام بسته شده است.

### &rlm;نیازمندی‌های عملکردی

- &rlm;&lrm;FR-TSK-001&lrm; Task فقط در Project فعال ایجاد می‌شود.
- &rlm;&lrm;FR-TSK-002&lrm; Admin می‌تواند Task را برای هر User معتبر در Context Project Assign کند.
- &rlm;&lrm;FR-TSK-003&lrm; Customer فقط داخل Projectی که Member آن است Task ایجاد می‌کند.
- &rlm;&lrm;FR-TSK-004&lrm; Customer-created Task با Status=Waiting Admin، Priority=Normal و Assignee=null ساخته می‌شود تا به Admin Queue وارد شود.
- &rlm;&lrm;FR-TSK-005&lrm; Customer نمی‌تواند Priority یا Assignee را تغییر دهد.
- &rlm;&lrm;FR-TSK-006&lrm; Customer می‌تواند Status Task Assign‌شده به خودش را به Todo، In Progress، Waiting Admin یا Completed تغییر دهد.
- &rlm;&lrm;FR-TSK-007&lrm; Customer نمی‌تواند Task را Cancel کند.
- &rlm;&lrm;FR-TSK-008&lrm; Admin می‌تواند همه Status transitionها را انجام دهد.
- &rlm;&lrm;FR-TSK-009&lrm; Waiting Customer باید Assignee از Customer Memberهای همان Project داشته باشد.
- &rlm;&lrm;FR-TSK-010&lrm; Waiting Admin نباید Customer Assignee داشته باشد؛ Assignee می‌تواند null یا یک Admin فعال باشد.
- &rlm;&lrm;FR-TSK-011&lrm; Completed شدن Task باید Completed At را ثبت کند.
- &rlm;&lrm;FR-TSK-012&lrm; Reopen کردن Task باید Completed At را خالی کند.
- &rlm;&lrm;FR-TSK-013&lrm; Task Cancelled یا Completed برای Customer Read-only است و History قابل مشاهده می‌ماند؛ Admin برای ادامه کار ابتدا Task را Reopen می‌کند.
- &rlm;&lrm;FR-TSK-014&lrm; Admin می‌تواند Task Completed/Cancelled را Reopen کند.
- &rlm;&lrm;FR-TSK-015&lrm; Task از UI Hard Delete نمی‌شود.
- &rlm;&lrm;FR-TSK-016&lrm; Task List باید Pagination داشته باشد.
- &rlm;&lrm;FR-TSK-017&lrm; Task List باید Search و Filterهای Project، Status، Priority، Assignee و Due/Overdue را پشتیبانی کند.
- &rlm;&lrm;FR-TSK-018&lrm; Overdue یعنی Due Date گذشته و Status نه Completed و نه Cancelled باشد.
- &rlm;&lrm;FR-TSK-019&lrm; وقتی Customer Task خودش را به Waiting Admin می‌برد، سیستم Customer Assignee را پاک می‌کند و Task را به Admin Queue برمی‌گرداند.
- &rlm;&lrm;FR-TSK-020&lrm; Todo و In Progress باید Assignee فعال و معتبر داشته باشند.
- &rlm;&lrm;FR-TSK-021&lrm; هر Task باید یک Human-readable Reference یکتا و Immutable داشته باشد که از Internal Primary Key به‌عنوان مفهوم محصولی مستقل باشد.

### &rlm;Business Ruleها

- &rlm;&lrm;BR-TSK-001&lrm; Assignee Customer باید Active و Member همان Project باشد.
- &rlm;&lrm;BR-TSK-010&lrm; Assignee Admin باید User فعال با Role=Admin باشد و برای Assignment نیاز به Project Membership ندارد.
- &rlm;&lrm;BR-TSK-002&lrm; تغییر Membership نباید Creator/Comment History قبلی Task را حذف کند.
- &rlm;&lrm;BR-TSK-003&lrm; Customer-created Task قبل از ذخیره باید Title داشته باشد؛ Description و Attachment اختیاری‌اند.
- &rlm;&lrm;BR-TSK-004&lrm; Customer Project Member همه Taskهای Project را می‌بیند حتی اگر Assignee خودش نباشد.
- &rlm;&lrm;BR-TSK-005&lrm; Assignment مسئول اقدام است و کنترل Visibility نیست.
- &rlm;&lrm;BR-TSK-006&lrm; تغییر Status، Assignee، Priority و Due Date باید Activity ایجاد کند.
- &rlm;&lrm;BR-TSK-007&lrm; تغییر Project یک Task پس از ایجاد در MVP مجاز نیست.
- &rlm;&lrm;BR-TSK-008&lrm; Task Reference باید در زمان ایجاد تولید، Immutable و در URL/Search/Notification قابل استفاده باشد؛ Format دقیق آن Technical Design است.
- &rlm;&lrm;BR-TSK-009&lrm; Waiting Admin یک Queue برای Role=Admin است و نباید به وجود Admin User با ID ثابت وابسته باشد.

### &rlm;Workflow — Task توسط Admin

&rlm;Admin Project را باز می‌کند؛ Task ایجاد می‌کند؛ Assignee و Priority و Due Date را مشخص می‌کند؛ Assignee Notification دریافت می‌کند؛ کار با Status و Comment جلو می‌رود؛ در پایان Task Completed می‌شود.

### &rlm;Workflow — Task توسط Customer

&rlm;Customer Project فعال را باز می‌کند؛ Task ایجاد می‌کند؛ Title، Description و Attachment اختیاری را ثبت می‌کند؛ Task با Waiting Admin و Assignee خالی وارد Admin Queue می‌شود؛ Admin آن را بررسی، Assignee/Priority/Due Date را تعیین و چرخه همکاری را ادامه می‌دهد.

### &rlm;Workflow — تحویل بین Admin و Customer

&rlm;اگر Admin منتظر اقدام Customer باشد Status=Waiting Customer و Assignee یک Customer Member می‌شود. Customer پس از انجام اقدام می‌تواند Status را به Waiting Admin تغییر دهد؛ سیستم Assignee مشتری را پاک می‌کند و Task به Admin Queue برمی‌گردد. یک Admin بعداً می‌تواند Task را Claim/Assign کند. این وضعیت باید در Dashboard قابل مشاهده باشد.

### Acceptance Criteria

- &rlm;&lrm;AC-TSK-001&lrm; Customer نمی‌تواند Task خارج از Projectهای عضو شده را بخواند یا بسازد.
- &rlm;&lrm;AC-TSK-002&lrm; Customer-created Task بدون نیاز به انتخاب Admin با Waiting Admin و Assignee خالی ایجاد می‌شود و در Admin Queue دیده می‌شود.
- &rlm;&lrm;AC-TSK-003&lrm; Customer نمی‌تواند Assignee یا Priority را از UI یا API تغییر دهد.
- &rlm;&lrm;AC-TSK-004&lrm; Waiting Customer با Assignee غیرCustomer معتبر ذخیره نمی‌شود.
- &rlm;&lrm;AC-TSK-005&lrm; Waiting Admin با Customer Assignee معتبر ذخیره نمی‌شود و می‌تواند بدون Assignee باقی بماند.
- &rlm;&lrm;AC-TSK-006&lrm; Overdue به‌درستی فقط برای Taskهای باز محاسبه می‌شود.
- &rlm;&lrm;AC-TSK-007&lrm; Completed At با Complete/Reopen درست set/unset می‌شود.
- &rlm;&lrm;AC-TSK-008&lrm; تمام تغییرات کلیدی Task Activity قابل مشاهده ایجاد می‌کنند.
- &rlm;&lrm;AC-TSK-009&lrm; Todo/In Progress بدون Assignee معتبر ذخیره نمی‌شود.
- &rlm;&lrm;AC-TSK-010&lrm; Task Reference بعد از ایجاد تغییر نمی‌کند و با Internal ID به‌عنوان یک مفهوم واحد فرض نمی‌شود.

## &rlm;05 — گفتگو و فایل‌ها

### &rlm;هدف دامنه

&rlm;نگه‌داشتن تمام گفتگو و فایل مرتبط با اجرای کار در Context همان Task تا نیاز به پیام‌رسان جانبی برای پیگیری Task کاهش پیدا کند.

### &rlm;اصول

&rlm;در MVP Comment عمومی داخل Project است؛ Internal Note خصوصی برای Admin نداریم.
&rlm;Chat عمومی یا Direct Message نداریم.
&rlm;فایل باید به Task یا Comment مربوط باشد و بدون Permission همان Task قابل دانلود نباشد.

### &rlm;Use Caseها

- &rlm;&lrm;UC-COL-001&lrm; Admin یا Customer Project Member روی Task Comment می‌گذارد.
- &rlm;&lrm;UC-COL-002&lrm; User هنگام ایجاد Task یا Comment فایل پیوست می‌کند.
- &rlm;&lrm;UC-COL-003&lrm; User تاریخچه Commentها را به ترتیب زمانی مشاهده می‌کند.
- &rlm;&lrm;UC-COL-004&lrm; Admin یک Comment یا Attachment نامناسب را از دسترس عادی خارج می‌کند بدون Hard Delete.
- &rlm;&lrm;UC-COL-005&lrm; User فایل مجاز را دانلود یا در صورت پشتیبانی مرورگر Preview می‌کند.

### &rlm;نیازمندی‌های عملکردی

- &rlm;&lrm;FR-COL-001&lrm; فقط User دارای Access به Task می‌تواند Commentها را مشاهده کند.
- &rlm;&lrm;FR-COL-002&lrm; Admin و Customer Member Project فعال می‌توانند روی Task فعال Comment ایجاد کنند.
- &rlm;&lrm;FR-COL-003&lrm; Comment باید Author، Body و Created At داشته باشد.
- &rlm;&lrm;FR-COL-004&lrm; Body Comment الزامی است مگر اینکه حداقل یک Attachment همراه Comment وجود داشته باشد.
- &rlm;&lrm;FR-COL-005&lrm; Commentها در MVP Thread تو در تو ندارند و Chronological هستند.
- &rlm;&lrm;FR-COL-006&lrm; Comment برای Customer قابل حذف دائمی نیست.
- &rlm;&lrm;FR-COL-007&lrm; Attachment می‌تواند هنگام Task Creation یا Comment Creation اضافه شود.
- &rlm;&lrm;FR-COL-008&lrm; Download Attachment باید همان Authorization Task را enforce کند.
- &rlm;&lrm;FR-COL-009&lrm; فایل‌ها نباید با URL عمومی قابل حدس یا بدون Authorization در دسترس باشند.
- &rlm;&lrm;FR-COL-010&lrm; نوع و حجم فایل باید Server-side validate شود.
- &rlm;&lrm;FR-COL-011&lrm; محدودیت حجم پیش‌فرض MVP برای هر فایل 20MB است و می‌تواند در Configuration تغییر کند.
- &rlm;&lrm;FR-COL-012&lrm; Admin می‌تواند Comment/Attachment را Hidden کند؛ رکورد Audit آن باقی می‌ماند.
- &rlm;&lrm;FR-COL-013&lrm; Project Completed و Task Completed/Cancelled برای هیچ Userی Comment یا Attachment جدید نمی‌پذیرند؛ Admin برای ادامه Collaboration باید Project/Task را Reopen کند.
- &rlm;&lrm;FR-COL-014&lrm; Comment جدید باید Notification مرتبط ایجاد کند.
- &rlm;&lrm;FR-COL-015&lrm; Comment پس از ثبت در MVP Edit نمی‌شود؛ اصلاح محتوا با Comment جدید انجام می‌شود و Admin فقط قابلیت Hide با Audit دارد.

### &rlm;نوع فایل‌های پایه MVP

&rlm;تصویرهای رایج، PDF، فایل‌های متنی و Office رایج و Archiveهای متداول در صورت عبور از Validation امنیتی. فایل اجرایی و نوع‌های پرریسک باید Reject شوند.

### &rlm;Business Ruleها

- &rlm;&lrm;BR-COL-001&lrm; همه Project Memberها Commentهای Task را می‌بینند؛ Comment خصوصی نداریم.
- &rlm;&lrm;BR-COL-002&lrm; Hidden content برای Customer نمایش داده نمی‌شود ولی Admin باید بتواند وجود و Audit آن را ببیند.
- &rlm;&lrm;BR-COL-003&lrm; حذف Membership دسترسی آینده User به فایل‌های Project را قطع می‌کند.
- &rlm;&lrm;BR-COL-004&lrm; Attachment نباید مستقیماً از Public Storage بدون Authorization سرو شود.
- &rlm;&lrm;BR-COL-005&lrm; Metadata فایل شامل Original Name، MIME Type، Size، Uploader و Created At نگهداری می‌شود.

### &rlm;Data Requirementها

&rlm;Comment: Task Reference، Author User Reference، Body، Visibility State، Created At.
&rlm;Attachment: Uploader، Parent Context، Original Name، Storage Path/Key، MIME Type، Size، Visibility State، Created At.
&rlm;Attachment Parent می‌تواند Task یا Comment باشد.

### &rlm;Workflow — پاسخ Customer

&rlm;Customer Task را باز می‌کند؛ Comment و در صورت نیاز File اضافه می‌کند؛ Comment ذخیره می‌شود؛ Activity/Notification تولید می‌شود؛ Admin از Task Detail همان Context را مشاهده می‌کند.

### Acceptance Criteria

- &rlm;&lrm;AC-COL-001&lrm; User بدون Task Access نمی‌تواند فایل را با URL مستقیم دانلود کند.
- &rlm;&lrm;AC-COL-002&lrm; Comment بدون متن و بدون فایل Reject می‌شود.
- &rlm;&lrm;AC-COL-003&lrm; Comment و Attachment بعد از Deactivate شدن User همچنان با Author تاریخی نمایش داده می‌شوند.
- &rlm;&lrm;AC-COL-004&lrm; فایل بزرگ‌تر از Limit یا نوع ممنوع Reject می‌شود.
- &rlm;&lrm;AC-COL-005&lrm; روی Project/Task بسته‌شده هیچ Comment/Upload جدیدی ایجاد نمی‌شود مگر Resource توسط Admin Reopen شود.
- &rlm;&lrm;AC-COL-006&lrm; Comment ثبت‌شده Edit نمی‌شود و Hide شدن آن History/Audit را حفظ می‌کند.

## &rlm;06 — اعلان‌ها و فعالیت

### &rlm;هدف دامنه

&rlm;اطمینان از اینکه تغییر مسئولیت یا تعامل مهم روی Task بدون نیاز به چک‌کردن دستی دائمی قابل مشاهده باشد و در عین حال History تغییرات کلیدی حفظ شود.

### &rlm;کانال‌های MVP

&rlm;In-app Notification و Email Notification. Push، SMS، Telegram، Slack و Realtime WebSocket خارج از MVP هستند.

### &rlm;رویدادهای Notification

&rlm;Task Created/Assigned: اگر Assignee وجود داشته باشد Recipient اصلی Assignee است؛ Customer-created Task بدون Assignee به Admin Queue notification می‌دهد.
&rlm;Assignee Changed: Recipient اصلی New Assignee است.
&rlm;Status Changed: Creator و Assignee، به‌جز Actor، Notification دریافت می‌کنند.
&rlm;Comment Added: Creator و Assignee، به‌جز Comment Author، Notification دریافت می‌کنند.
&rlm;Customer Task Created: همه Admin Userهای Active حداقل In-app Notification دریافت می‌کنند؛ این Event به Admin ID ثابت وابسته نیست.
&rlm;Project Membership Added: Customer User Notification دریافت می‌کند.

### &rlm;نیازمندی‌های عملکردی Notification

- &rlm;&lrm;FR-NOT-001&lrm; هر Notification باید Recipient، Type، Related Resource، Read State و Created At داشته باشد.
- &rlm;&lrm;FR-NOT-002&lrm; User باید لیست Notificationهای خودش را ببیند.
- &rlm;&lrm;FR-NOT-003&lrm; User باید Notification را Read کند و Unread Count داشته باشد.
- &rlm;&lrm;FR-NOT-004&lrm; کلیک روی Notification باید User را به Resource مجاز مرتبط هدایت کند.
- &rlm;&lrm;FR-NOT-005&lrm; Email Notification باید Link به Task/Project داشته باشد ولی Attachment فایل را به Email الصاق نکند.
- &rlm;&lrm;FR-NOT-006&lrm; اگر Recipient دیگر Access به Resource ندارد، Link نباید اطلاعات Resource را افشا کند.
- &rlm;&lrm;FR-NOT-007&lrm; Notification Actor نباید برای Action خودش Notification تکراری دریافت کند.
- &rlm;&lrm;FR-NOT-008&lrm; Failure ارسال Email نباید Transaction اصلی Task/Comment را Rollback کند و باید در Log فنی ثبت شود.
- &rlm;&lrm;FR-NOT-009&lrm; Eventهای Admin Queue باید Audience=Active Admin Users را پشتیبانی کنند و نبود Assignee مشخص باعث گم‌شدن Notification نشود.

### &rlm;Activityهای MVP

&rlm;Task Created؛ Assignee Changed؛ Status Changed؛ Priority Changed؛ Due Date Changed؛ Task Completed؛ Task Reopened؛ Task Cancelled؛ Comment Added؛ Attachment Added/Hidden؛ Project Status Changed؛ Project Membership Added/Removed.

### &rlm;نیازمندی‌های عملکردی Activity

- &rlm;&lrm;FR-ACT-001&lrm; Activity باید Actor، Action Type، Resource، Timestamp و Metadata حداقلی تغییر را ثبت کند.
- &rlm;&lrm;FR-ACT-002&lrm; Activityهای Task باید در Task Detail به ترتیب زمانی قابل مشاهده باشند.
- &rlm;&lrm;FR-ACT-003&lrm; Activityهای Project باید در Project Detail قابل مشاهده باشند یا از Activity Taskها تجمیع شوند.
- &rlm;&lrm;FR-ACT-004&lrm; Customer فقط Activityهایی را می‌بیند که به Resource مجاز خودش مربوط‌اند.
- &rlm;&lrm;FR-ACT-005&lrm; Activity از UI Hard Delete نمی‌شود.
- &rlm;&lrm;FR-ACT-006&lrm; برای تغییرات فیلدی مهم مقدار قبلی و جدید در صورت امن بودن ثبت می‌شود.
- &rlm;&lrm;FR-ACT-007&lrm; داده حساس مانند Password یا Token هرگز وارد Activity Metadata نمی‌شود.

### &rlm;Business Ruleها

- &rlm;&lrm;BR-NOT-001&lrm; Notification جای Authorization را نمی‌گیرد؛ بازکردن Link همیشه Access را دوباره بررسی می‌کند.
- &rlm;&lrm;BR-NOT-002&lrm; برای User غیرفعال Notification جدید ایجاد یا Email ارسال نمی‌شود؛ History Notificationهای قبلی حفظ می‌شود.
- &rlm;&lrm;BR-ACT-001&lrm; Activity History باید حتی پس از Inactive شدن User یا Client قابل استفاده برای Admin باقی بماند.
- &rlm;&lrm;BR-ACT-002&lrm; نمایش Activity برای Customer نباید اطلاعات Admin-only یا Client دیگر را افشا کند.

### Acceptance Criteria

- &rlm;&lrm;AC-NOT-001&lrm; Assign شدن Task به User حداقل یک In-app Notification ایجاد می‌کند.
- &rlm;&lrm;AC-NOT-002&lrm; Comment توسط Customer برای Assignee/Creator مجاز Notification ایجاد می‌کند و برای خود Customer Duplicate ایجاد نمی‌شود؛ اگر Task در Admin Queue بدون Assignee باشد Active Adminها Notification دریافت می‌کنند.
- &rlm;&lrm;AC-NOT-003&lrm; Mark as Read، Unread Count را درست تغییر می‌دهد.
- &rlm;&lrm;AC-ACT-001&lrm; تغییر Status و Assignee در History Task با Actor و زمان قابل مشاهده است.
- &rlm;&lrm;AC-ACT-002&lrm; Customer Activity خارج از Access خودش را نمی‌بیند.

## &rlm;07 — داشبورد و تجربه کاربری

### &rlm;هدف دامنه

&rlm;ارائه مسیرهای ساده برای اینکه Admin و Customer سریع بفهمند چه Projectهایی دارند، چه Taskهایی نیازمند اقدام‌اند و چه مواردی Overdue شده‌اند.

### &rlm;جهت و زبان

&rlm;رابط MVP فارسی‌محور و RTL است. ساختار UI باید Responsive باشد و جریان‌های اصلی در Desktop، Tablet و Mobile قابل استفاده باشند.

### Admin Dashboard

Active Projects Count.
Open Tasks Count.
Tasks Assigned To Me.
Waiting Admin / Admin Queue Count.
Waiting Customer Count.
Overdue Tasks Count.
Recent Activity.
&rlm;لیست کوتاه Taskهای نیازمند اقدام با Link مستقیم.

### Customer Dashboard

My Active Projects.
&rlm;Open Tasks در Projectهای مجاز.
Tasks Assigned To Me.
Waiting Customer Assigned To Me Count.
Overdue Tasks Assigned To Me.
&rlm;Recent Task Updates مرتبط با Projectهای مجاز.

### Project List

&rlm;Admin همه Projectها را می‌بیند و بر اساس Client و Status فیلتر می‌کند.
&rlm;Customer فقط Projectهای Member شده را می‌بیند.
&rlm;هر ردیف حداقل Name، Client برای Admin، Status، Due Date و تعداد Task باز را نشان می‌دهد.

### Project Detail

&rlm;Summary؛ Status؛ Dates؛ Members؛ Task List؛ Activity خلاصه.
&rlm;Customer در Project بسته‌شده فقط حالت Read-only می‌بیند.

### Task List

&rlm;ستون‌های حداقلی: Reference، Title، Project، Status، Priority، Assignee یا Unassigned، Due Date، Updated At.
&rlm;Search بر اساس Title/Reference.
&rlm;Filter بر اساس Project، Status، Priority، Assignee و Overdue.
&rlm;Sort حداقل بر اساس Updated At و Due Date.
&rlm;Pagination الزامی است.
&rlm;Saved Filter و Advanced Query Builder خارج از MVP است.

### Task Detail

&rlm;Header شامل Title، Status، Priority، Assignee و Due Date.
Description.
&rlm;Conversation شامل Comment و Attachment.
Activity Timeline.
&rlm;Actionهای مجاز باید بر اساس Role و Task State نمایش داده شوند.

### &rlm;ایجاد Task

&rlm;فرم Customer باید کوتاه باشد: Title، Description اختیاری، Attachment اختیاری؛ Assignee و Priority از Customer پرسیده نمی‌شوند و Task مستقیم به Admin Queue می‌رود.
&rlm;فرم Admin علاوه بر موارد بالا Assignee، Priority و Due Date را دارد.
&rlm;Project از Context صفحه انتخاب یا از قبل مشخص می‌شود و Customer نمی‌تواند Project خارج از Membership را انتخاب کند.

### &rlm;نیازمندی‌های UX

- &rlm;&lrm;FR-UX-001&lrm; Navigation اصلی Customer فقط Dashboard، Projects، Tasks و Notifications را نشان می‌دهد.
- &rlm;&lrm;FR-UX-002&lrm; Navigation Admin علاوه بر موارد بالا Users و Clients را دارد.
- &rlm;&lrm;FR-UX-003&lrm; Empty State باید Action بعدی واضح داشته باشد.
- &rlm;&lrm;FR-UX-004&lrm; Loading، Validation Error، Forbidden و Not Found باید حالت مشخص و قابل فهم داشته باشند.
- &rlm;&lrm;FR-UX-005&lrm; Operationهای Create/Update باید Success/Error Feedback واضح داشته باشند.
- &rlm;&lrm;FR-UX-006&lrm; Status، Priority و Overdue باید در List بدون بازکردن Task قابل تشخیص باشند.
- &rlm;&lrm;FR-UX-007&lrm; Mobile Layout نباید برای ایجاد Task، Comment و تغییر Status نیاز به Desktop داشته باشد.
- &rlm;&lrm;FR-UX-008&lrm; Date/Time باید با یک Format ثابت در کل سیستم نمایش داده شود.
- &rlm;&lrm;FR-UX-009&lrm; Pagination و Filter State در Navigation معمول کاربر رفتار قابل پیش‌بینی داشته باشند.
- &rlm;&lrm;FR-UX-010&lrm; دسترسی Forbidden به‌جای افشای وجود Resource باید پاسخ امن مناسب برگرداند.

### &rlm;چیزهایی که عمداً در UI MVP نداریم

&rlm;Kanban Board، Gantt، Calendar View، Drag & Drop Workflow، Widget Builder، Custom Dashboard، Dark/Theme Builder، Chat Sidebar و Complex Analytics.

### Acceptance Criteria

- &rlm;&lrm;AC-UX-001&lrm; Customer پس از Login از Dashboard در حداکثر دو Navigation Step به Task Detail می‌رسد.
- &rlm;&lrm;AC-UX-002&lrm; Create Task و Add Comment در Mobile قابل انجام است.
- &rlm;&lrm;AC-UX-003&lrm; Filterهای Task List با Pagination کار می‌کنند و داده خارج از Scope User وارد Result نمی‌شود.
- &rlm;&lrm;AC-UX-004&lrm; Project/Task بسته‌شده برای Customer Actionهای ویرایشی نمایش نمی‌دهد.
- &rlm;&lrm;AC-UX-005&lrm; Dashboard Customer هیچ Count یا Activity از Client/Project غیرمجاز نشان نمی‌دهد.

## &rlm;08 — قواعد سراسری و پذیرش

### &rlm;هدف

&rlm;تعریف قوانین مشترک، Non-functional Requirementها و معیارهایی که قبل از Release MVP باید به‌صورت End-to-End پاس شوند.

### &rlm;مدل مفهومی ارتباطات

&rlm;Client یک یا چند Customer User دارد.
&rlm;Client یک یا چند Project دارد.
&rlm;Project دقیقاً یک Client دارد.
&rlm;Project چند Customer Membership دارد که هرکدام Lifecycle Joined/Removed دارند.
&rlm;Project چند Task دارد.
&rlm;Task دقیقاً یک Project، یک Creator، یک Human-readable Reference و حداکثر یک Assignee دارد؛ Waiting Admin می‌تواند Assignee خالی داشته باشد.
&rlm;Task چند Comment، Attachment و Activity دارد.
&rlm;User چند Notification دارد.
&rlm;دسترسی Customer به Task از مسیر User → Project Membership → Project → Task اثبات می‌شود.

### &rlm;ماتریس Permission مفهومی

&rlm;Admin: مدیریت User، Client، Project، Membership و همه Taskها؛ Comment/Attachment؛ مشاهده همه Activityها و Notificationهای شخصی.
&rlm;Customer: Profile شخصی؛ مشاهده Projectهای Member شده؛ مشاهده همه Taskهای همان Project؛ ایجاد Task در Project فعال؛ Comment/Attachment؛ تغییر Status فقط در محدوده تعریف‌شده برای Taskهای Assign‌شده به خود؛ مشاهده Activity مجاز.
&rlm;Customer هیچ دسترسی به User Management، Client Management، Project Creation، Membership Management، Assignment یا Priority Management ندارد.

### &rlm;قواعد امنیت و Isolation

- &rlm;&lrm;NFR-SEC-001&lrm; Authorization تمام Resourceها باید Server-side و ترجیحاً Policy/Guard محور باشد.
- &rlm;&lrm;NFR-SEC-002&lrm; تمام Queryهای Customer باید Scope دسترسی Project را رعایت کنند.
- &rlm;&lrm;NFR-SEC-003&lrm; ID مستقیم در URL/API نباید امکان Horizontal Privilege Escalation بدهد.
- &rlm;&lrm;NFR-SEC-004&lrm; Password باید با Hash استاندارد Framework ذخیره شود.
- &rlm;&lrm;NFR-SEC-005&lrm; Login، Password Reset و Upload endpointها باید Rate Limit مناسب داشته باشند.
- &rlm;&lrm;NFR-SEC-006&lrm; فایل خصوصی فقط پس از Authorization ارائه شود.
- &rlm;&lrm;NFR-SEC-007&lrm; Validation فایل باید Extension، MIME و Size را در Server بررسی کند.
- &rlm;&lrm;NFR-SEC-008&lrm; داده حساس نباید در Log، Notification یا Activity ذخیره شود.
- &rlm;&lrm;NFR-SEC-009&lrm; CSRF، Session Security و Output Escaping باید از استانداردهای Framework پیروی کنند.

### &rlm;قواعد داده و Lifecycle

- &rlm;&lrm;NFR-DATA-001&lrm; عملیات UI روی Entityهای اصلی Hard Delete انجام نمی‌دهد.
- &rlm;&lrm;NFR-DATA-002&lrm; Inactive/Completed/Cancelled و Membership Removal باید History را حفظ کنند.
- &rlm;&lrm;NFR-DATA-003&lrm; تمام Relationهای دسترسی باید با Foreign Key/Constraint مناسب در Technical Design محافظت شوند.
- &rlm;&lrm;NFR-DATA-004&lrm; Email User به‌صورت Normalized/Case-insensitive در کل Userها یکتا و Membership ترکیب Project+User یکتا است.
- &rlm;&lrm;NFR-DATA-005&lrm; Timestampهای سیستمی باید Consistent و قابل Audit باشند.
- &rlm;&lrm;NFR-DATA-006&lrm; تغییر Client یک Project یا تغییر Project یک Task در MVP مجاز نیست.
- &rlm;&lrm;NFR-DATA-007&lrm; Task Reference باید Unique و Immutable باشد و Internal Database Key نباید به‌عنوان تنها Reference محصولی فرض شود.

### &rlm;Performance و Reliability

- &rlm;&lrm;NFR-PERF-001&lrm; Listهای اصلی Pagination دارند و Query بدون Bound برای Customer-facing list اجرا نمی‌شود.
- &rlm;&lrm;NFR-PERF-002&lrm; صفحات اصلی در حجم عادی MVP باید بدون N+1 Query محسوس طراحی شوند.
- &rlm;&lrm;NFR-PERF-003&lrm; Upload و Email failure نباید باعث Corruption وضعیت Task شود.
- &rlm;&lrm;NFR-PERF-004&lrm; Jobهای Email می‌توانند Async باشند و Retry/Failure Log داشته باشند.
- &rlm;&lrm;NFR-PERF-005&lrm; Database transaction برای عملیات چندمرحله‌ای حساس مانند Status+Assignee+Activity استفاده می‌شود.

### Observability

- &rlm;&lrm;NFR-OBS-001&lrm; خطاهای Backend باید Log ساختاریافته داشته باشند.
- &rlm;&lrm;NFR-OBS-002&lrm; Failure ارسال Email و Job باید قابل مشاهده برای Admin فنی باشد.
- &rlm;&lrm;NFR-OBS-003&lrm; Security/Authorization failure نباید داده Resource را در Error Message افشا کند.

### &rlm;سازگاری و UI

- &rlm;&lrm;NFR-UX-001&lrm; UI فارسی و RTL است.
- &rlm;&lrm;NFR-UX-002&lrm; صفحات اصلی Responsive هستند.
- &rlm;&lrm;NFR-UX-003&lrm; Chrome، Safari و Firefox نسخه‌های مدرن هدف MVP هستند.
- &rlm;&lrm;NFR-UX-004&lrm; Formها Validation message قابل فهم دارند.
- &rlm;&lrm;NFR-UX-005&lrm; Accessible label و Keyboard navigation پایه برای Formها رعایت می‌شود.

### &rlm;سناریوهای پذیرش End-to-End

#### &rlm;E2E-001 — Onboarding کامل

&rlm;Admin Client می‌سازد؛ Customer User می‌سازد؛ Project ایجاد می‌کند؛ User را Member می‌کند؛ Task برای User می‌سازد؛ Customer Login می‌کند و Project/Task را می‌بیند.

#### E2E-002 — Isolation

&rlm;دو Client و دو Customer User و دو Project وجود دارند. User A تحت هیچ Route، API یا Search Result نباید Project/Task/Attachment مربوط به Client B را ببیند.

#### E2E-003 — Customer Request

&rlm;Customer در Project عضو شده Task می‌سازد؛ Task با Waiting Admin و Assignee خالی وارد Admin Queue می‌شود؛ Active Adminها Notification می‌گیرند؛ یک Admin Task را Assign/Claim می‌کند؛ Admin پاسخ می‌دهد و Task را با Waiting Customer به Customer تحویل می‌دهد؛ Customer پاسخ داده و Waiting Admin را انتخاب می‌کند؛ Task دوباره بدون Customer Assignee وارد Admin Queue می‌شود؛ در پایان Completed می‌شود.

#### E2E-004 — File Security

&rlm;Customer مجاز File روی Task آپلود می‌کند و دانلود می‌کند؛ Customer غیرمجاز با URL مستقیم همان File پاسخ Unauthorized/Not Found امن دریافت می‌کند.

#### E2E-005 — Membership Removal

&rlm;Admin Membership Customer را End می‌کند؛ Removed At ثبت و History قبلی باقی می‌ماند؛ Customer دیگر Project، Task، Comment یا File را مشاهده نمی‌کند؛ Re-add همان Membership را دوباره Active می‌کند و Access برمی‌گردد.

#### E2E-006 — Client Deactivation

&rlm;Admin Client را Inactive می‌کند؛ Customer Userهای آن Client Login جدید ندارند؛ داده‌ها برای Admin باقی می‌مانند؛ با Reactivate دسترسی بر اساس Membership قبلی برمی‌گردد.

#### E2E-007 — Closed Project

&rlm;تا زمانی که Task باز وجود دارد Complete کردن Project Reject می‌شود؛ پس از بسته‌شدن همه Taskها Admin Project را Completed می‌کند؛ Customer می‌تواند History را ببیند ولی Task/Comment/Attachment جدید ایجاد نمی‌شود؛ Admin Project را Reopen می‌کند و Interaction دوباره فعال می‌شود.

#### E2E-008 — Task State Integrity

&rlm;Task Waiting Customer فقط Customer Assignee فعال و Member دارد؛ Task Waiting Admin Customer Assignee ندارد و می‌تواند Assignee=null یا Admin فعال داشته باشد؛ Todo/In Progress بدون Assignee معتبر Reject می‌شود؛ Complete کردن Completed At را ثبت می‌کند؛ Reopen آن را Reset می‌کند؛ Task Reference در تمام چرخه ثابت می‌ماند.

### &rlm;Definition of Done برای MVP

&rlm;تمام FRها و Business Ruleهای این PRD پیاده‌سازی شده یا با تصمیم مکتوب از Scope خارج شده‌اند.
&rlm;تمام E2E Scenarioهای بالا Test خودکار یا Test Case تکرارپذیر دارند.
&rlm;Authorization برای Admin/Customer و Project Isolation تست شده است.
&rlm;جریان Login، Reset Password، Client/User/Project/Task Creation، Comment، File، Notification و Completion قابل اجراست.
&rlm;هیچ Feature خارج از Scope برای Release MVP وابستگی اجباری ایجاد نمی‌کند.
&rlm;Migration و Seed لازم برای Roleهای سیستمی وجود دارد و هیچ منطق Domain به Admin ID ثابت وابسته نیست.
&rlm;محیط Production قابلیت Queue/Email/Private File Storage موردنیاز MVP را دارد.
&rlm;Error handling، Logging و Backup پایه برای Release تعریف شده‌اند.

### &rlm;Post-MVP Candidateها

&rlm;Project Archive؛ Kanban؛ Milestone؛ Time Tracking؛ Recurring Task؛ Task Dependency؛ Customer Contact Directory؛ Ticket/Helpdesk؛ Knowledge Base؛ Contract؛ Invoice/Payment؛ Advanced Role/Permission؛ Internal Note؛ Comment Editing با Audit؛ Mention/Watcher؛ Realtime Notification؛ Calendar؛ API/Webhook؛ Automation و AI.

### Release Gate

&rlm;MVP زمانی آماده Release است که E2E-001 تا E2E-008 پاس شوند، تست Isolation هیچ نشت داده‌ای نشان ندهد، Admin Queue بدون وابستگی به Admin ID ثابت کار کند، Customer بتواند بدون کمک Admin جریان Task را از مشاهده تا پاسخ/تکمیل انجام دهد و Admin بتواند کل Lifecycle Client → Project → Task را از یک پنل مدیریت کند.
