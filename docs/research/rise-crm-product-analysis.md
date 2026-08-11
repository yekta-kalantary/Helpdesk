<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# RISE CRM — Product Analysis

## &rlm;00 — نقشه محصول و دامنه‌ها

### &rlm;هدف این سند

&rlm;این سند یک تحلیل مرجع از RISE است؛ نه پیشنهاد معماری برای پروژه ما. هدف، استخراج Domainها، قابلیت‌های مشاهده‌شده، قواعد رفتاری و نیازمندی‌های قابل استنباط است تا در مرحله بعد بتوانیم مستقل از محصول مرجع، MVP خودمان را طراحی کنیم.

### &rlm;مرز منبع و استنباط

&rlm;«قابلیت مشاهده‌شده» یعنی در صفحه محصول رسمی، مستندات Fairsketch یا تغییرات نسخه جاری دیده شده است. «نیازمندی استخراج‌شده» یعنی رفتاری که برای پیاده‌سازی همان قابلیت لازم است و ممکن است نام آن عیناً در RISE نیامده باشد.

### &rlm;دامنه‌های اصلی

&rlm;CRM و مدیریت مشتری؛ فروش و چرخه تجاری؛ پروژه و تسک؛ Helpdesk و پشتیبانی؛ مالی و صورتحساب؛ تیم و منابع انسانی؛ همکاری و ارتباطات؛ بهره‌وری و تقویم؛ فایل و دانش؛ گزارش و داشبورد؛ تنظیمات و سفارشی‌سازی؛ اتوماسیون و AI؛ یکپارچه‌سازی و توسعه‌پذیری.

### &rlm;Actorهای اصلی

&rlm;Admin/Owner، Team Member، Client، Client Contact، Lead/Prospect و Public Visitor. بعضی قابلیت‌ها مانند Store و فرم‌های عمومی می‌توانند بدون Login نیز در دسترس باشند.

### &rlm;الگوی کلی محصول

&rlm;RISE یک سیستم یکپارچه CRM + Project Management + Client Portal + Helpdesk + Finance + Team Collaboration است. Client یک موجودیت مرکزی است و پروژه، صورتحساب، پرداخت، تیکت، قرارداد، سفارش و تماس‌ها پیرامون آن قرار می‌گیرند. Team Member از مسیر Role/Permission و Membership به منابع دسترسی می‌گیرد.

### &rlm;منابع بررسی‌شده

&rlm;صفحه رسمی CodeCanyon محصول RISE، RISE Docs، Client Management Docs، Projects & Tasks Docs، Team & Collaboration Docs، Invoices & Payments Docs، Personalization/Settings Docs و Changelog نسخه 4.0.

## &rlm;01 — CRM و مدیریت مشتری

### &rlm;دامنه

&rlm;مدیریت چرخه ارتباط با مشتری از Prospect تا Client، نگهداری Contactها و فراهم‌کردن Client Portal.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE از Clients و Contacts پشتیبانی می‌کند. هر Client می‌تواند چند Contact داشته باشد و دسترسی Portal برای Contactهای مشتری کنترل شود. Client Portal دارای Dashboard و Left Menu قابل تنظیم است و Permissionهای سمت مشتری از Settings قابل مدیریت‌اند. در سطح Client، اطلاعات مرتبط با پروژه، صورتحساب، پرداخت، Estimate، Ticket، Notes و فایل‌ها قابل تجمیع است. Leads به‌عنوان مشتریان بالقوه با Kanban مدیریت و به Client تبدیل می‌شوند.

### &rlm;Actorها

&rlm;Admin و Team Member برای مدیریت CRM؛ Client Contact برای استفاده از Portal؛ Lead/Prospect پیش از تبدیل به Client.

### &rlm;موجودیت‌های کلیدی

&rlm;Lead، Client، Client Contact، Client Portal Permission، Note، Custom Field، Label/Status.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-CRM-001&lrm; سیستم باید Lead و Client را دو وضعیت/موجودیت متمایز در چرخه فروش نگه دارد.
- &rlm;&lrm;FR-RISE-CRM-002&lrm; سیستم باید امکان تبدیل Lead به Client را بدون از دست‌دادن سابقه مرتبط فراهم کند.
- &rlm;&lrm;FR-RISE-CRM-003&lrm; هر Client باید بتواند چند Contact داشته باشد و یک Contact بتواند Portal Access مستقل داشته باشد.
- &rlm;&lrm;FR-RISE-CRM-004&lrm; دسترسی Contact مشتری به ماژول‌های Portal باید قابل تنظیم باشد.
- &rlm;&lrm;FR-RISE-CRM-005&lrm; Dashboard و Navigation پرتال مشتری باید قابل سفارشی‌سازی باشد.
- &rlm;&lrm;FR-RISE-CRM-006&lrm; صفحه Client باید نمای تجمیعی از روابط تجاری و عملیاتی مشتری ارائه کند.
- &rlm;&lrm;FR-RISE-CRM-007&lrm; فرم‌های CRM باید قابلیت Custom Field داشته باشند.
- &rlm;&lrm;FR-RISE-CRM-008&lrm; جست‌وجو، فیلتر و وضعیت‌بندی برای Lead/Client باید وجود داشته باشد.

### &rlm;Workflow اصلی

&rlm;Lead ایجاد می‌شود؛ Owner/Team آن را پیگیری می‌کند؛ Lead در Pipeline تغییر وضعیت می‌دهد؛ در صورت موفقیت به Client تبدیل می‌شود؛ Contactها اضافه می‌شوند؛ Portal و Permissionها فعال می‌شوند؛ سپس پروژه، فروش، مالی و پشتیبانی به Client متصل می‌شوند.

### &rlm;نکات مرزی

&rlm;Client با Contact یکی نیست. Client نماینده حساب/مشتری است و Contact شخصی است که از طرف آن حساب تعامل می‌کند. Portal یک سطح دسترسی مستقل روی Contactهای Client است.

## &rlm;02 — فروش و چرخه تجاری

### &rlm;دامنه

&rlm;مدیریت Prospect تا پیشنهاد تجاری، Estimate، Contract، Product/Service، Order، Store و Subscription.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE Lead Management با Kanban و تبدیل Lead به Client دارد. Proposal برای ارائه پیشنهاد رسمی، Estimate و Estimate Request برای برآورد و دریافت درخواست قیمت، Contract برای توافق تجاری، Items برای کالا/خدمت و Store برای نمایش محصولات یا خدمات دارد. Store می‌تواند برای Clientهای Login شده یا Public Visitor فعال شود. Orderها از Store ایجاد و پیگیری می‌شوند. Subscription از نوع App-managed یا Stripe-managed پشتیبانی می‌شود و Billing دوره‌ای هفتگی/ماهانه/سالانه دارد.

### &rlm;Actorها

&rlm;Sales/Admin، Team Member دارای Permission، Client/Contact و در Store عمومی Public Visitor.

### &rlm;موجودیت‌های کلیدی

&rlm;Lead، Proposal، Estimate Request، Estimate، Contract، Item/Product/Service، Cart، Order، Subscription.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-SALES-001&lrm; سیستم باید Pipeline فروش و تبدیل Lead به Client را پشتیبانی کند.
- &rlm;&lrm;FR-RISE-SALES-002&lrm; Proposal باید قابل ارسال به مشتری و دارای وضعیت چرخه پذیرش باشد.
- &rlm;&lrm;FR-RISE-SALES-003&lrm; Estimate باید شامل Items، مالیات، اعتبار زمانی و قابلیت تبدیل به مرحله بعدی فروش باشد.
- &rlm;&lrm;FR-RISE-SALES-004&lrm; Estimate Request باید امکان دریافت ساختاریافته نیاز مشتری از فرم عمومی/اختصاصی را بدهد.
- &rlm;&lrm;FR-RISE-SALES-005&lrm; Contract باید به Client و در صورت نیاز Project متصل باشد و امکان نمایش/اشتراک با مشتری را داشته باشد.
- &rlm;&lrm;FR-RISE-SALES-006&lrm; Store باید بتواند Product/Service را برای Client یا Visitor نمایش دهد.
- &rlm;&lrm;FR-RISE-SALES-007&lrm; Order باید از انتخاب Itemها تشکیل شود، مبلغ و وضعیت داشته باشد و به Client/Payment متصل شود.
- &rlm;&lrm;FR-RISE-SALES-008&lrm; Subscription باید Draft/Active/Cancelled و در نوع Stripe حالت Pending داشته باشد.
- &rlm;&lrm;FR-RISE-SALES-009&lrm; Billing دوره‌ای Subscription باید Invoice تولید کند و Cron/Payment Provider را در صورت نیاز درگیر کند.

### &rlm;Workflow اصلی

&rlm;Lead → Client → Proposal/Estimate → Contract یا Order → Invoice/Payment. در فروش تکرارشونده: Subscription → Billing Cycle → Invoice → Payment.

### &rlm;نکات مرزی

&rlm;Store و Order بخشی از CRM ساده نیستند و عملاً یک Sales Commerce سبک ایجاد می‌کنند. Subscription نیز از Recurring Invoice جداست؛ Subscription یک قرارداد تکرارشونده با چرخه وضعیت و Billing مشخص است.

## &rlm;03 — پروژه و تسک

### &rlm;دامنه

&rlm;برنامه‌ریزی، تخصیص، همکاری و رهگیری اجرای کار برای Client Project.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Project دارای Members، Tasks، Milestones، Files، Comments، Notes، Invoices، Time Logs، Expenses و Activity است. Task فقط به Project Member قابل Assign است؛ در صورت فعال‌سازی Client Task Permission، Client Contactهای اضافه‌شده به Project نیز می‌توانند Assignee باشند. Task از Collaborator، Comment، Pinned Comment، Checklist، Kanban، Recurring و Change Log پشتیبانی می‌کند. Milestone برای زمان‌بندی و Progress پروژه استفاده می‌شود. Progress پروژه خودکار محاسبه می‌شود. GitHub/Bitbucket Commit می‌تواند در Task Activity نمایش داده شود.

### &rlm;Actorها

&rlm;Project Manager/Admin، Project Member، Task Assignee/Collaborator و در صورت مجاز Client Contact.

### &rlm;موجودیت‌های کلیدی

&rlm;Project، Project Member، Client Contact Membership، Task، Task Assignee، Collaborator، Checklist Item، Milestone، Comment، Attachment، Time Log، Activity Log.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-PROJ-001&lrm; هر Project باید به Client اختیاری و مجموعه‌ای از Project Memberها متصل باشد.
- &rlm;&lrm;FR-RISE-PROJ-002&lrm; Assign کردن Task به Team Member باید فقط در محدوده Memberهای همان Project مجاز باشد.
- &rlm;&lrm;FR-RISE-PROJ-003&lrm; Assign کردن Task به Client Contact باید وابسته به Permission پرتال و Membership آن Contact در Project باشد.
- &rlm;&lrm;FR-RISE-PROJ-004&lrm; Task باید وضعیت، اولویت/زمان‌بندی، Assignee، Collaborator، Description، Checklist، Comment و Attachment داشته باشد.
- &rlm;&lrm;FR-RISE-PROJ-005&lrm; سیستم باید Kanban برای جریان وضعیت Task ارائه کند.
- &rlm;&lrm;FR-RISE-PROJ-006&lrm; Taskهای تکرارشونده باید بر اساس Rule زمانی Task جدید بسازند و ارتباط با Task مبدا حفظ شود.
- &rlm;&lrm;FR-RISE-PROJ-007&lrm; Project باید Milestone و Progress قابل رهگیری داشته باشد.
- &rlm;&lrm;FR-RISE-PROJ-008&lrm; تغییرات مهم Project/Task باید Activity Log ایجاد کنند.
- &rlm;&lrm;FR-RISE-PROJ-009&lrm; Time Log و Expense باید به Project و در صورت نیاز Task متصل شوند.
- &rlm;&lrm;FR-RISE-PROJ-010&lrm; Project Files/Notes/Comments باید در Context پروژه قابل همکاری باشند.

### &rlm;Workflow اصلی

&rlm;Project ایجاد می‌شود؛ Members و Client Contacts مجاز اضافه می‌شوند؛ Milestone/Task تعریف می‌شود؛ Task Assign و روی Kanban جابه‌جا می‌شود؛ Comment/Checklist/File و Time Log ثبت می‌شود؛ Progress و Activity به‌روزرسانی می‌شوند؛ پروژه تکمیل می‌شود.

### &rlm;وابستگی‌ها

&rlm;Client Management، Role/Permission، Timesheet، File Manager، Finance، Notifications و Integrations.

## &rlm;04 — Helpdesk و پشتیبانی

### &rlm;دامنه

&rlm;دریافت، دسته‌بندی، تخصیص، پاسخ‌گویی و بستن درخواست‌های پشتیبانی مشتری.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE Ticket Management با List/Compact View، Reply، Template، Label، Internal Note، Smart Filter، Attachment و Audio Attachment دارد. Ticket می‌تواند از ایمیل Incoming به‌صورت خودکار ساخته شود. Auto Reply برای تأیید دریافت وجود دارد. Automation می‌تواند بر اساس محتوای ایمیل Label و Group تعیین کند و Ticket را به Team Member مناسب Assign کند. Knowledge Base برای Self-service مشتریان وجود دارد. نسخه 4.0 AI-powered Ticket Reply بر اساس Agent سفارشی اضافه کرده است.

### &rlm;Actorها

&rlm;Client/Contact، Support Agent/Team Member، Admin و Email Sender ناشناس در سناریوی Email-to-Ticket.

### &rlm;موجودیت‌های کلیدی

&rlm;Ticket، Requester، Agent/Assignee، Group، Label، Reply، Internal Note، Reply Template، Attachment، Email Message، Knowledge Base Article/Category.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-TKT-001&lrm; Ticket باید Requester، Subject، Body، Status، Assignee، Labels و Attachments داشته باشد.
- &rlm;&lrm;FR-RISE-TKT-002&lrm; Reply عمومی و Internal Note باید از هم متمایز باشند.
- &rlm;&lrm;FR-RISE-TKT-003&lrm; پاسخ‌های از پیش تعریف‌شده باید قابل مدیریت و استفاده سریع باشند.
- &rlm;&lrm;FR-RISE-TKT-004&lrm; Ticket باید از Incoming Email ساخته شود و Thread مکاتبه حفظ گردد.
- &rlm;&lrm;FR-RISE-TKT-005&lrm; Auto Reply باید برای Ticketهای جدید قابل تنظیم باشد.
- &rlm;&lrm;FR-RISE-TKT-006&lrm; Routing خودکار باید بتواند Label/Group/Assignee را بر اساس Rule تعیین کند.
- &rlm;&lrm;FR-RISE-TKT-007&lrm; Agent باید Ticketها را با Filter/Label/Status اولویت‌بندی کند.
- &rlm;&lrm;FR-RISE-TKT-008&lrm; Reply باید Attachment و در صورت پشتیبانی Audio داشته باشد.
- &rlm;&lrm;FR-RISE-TKT-009&lrm; Knowledge Base باید Articleها را در Categoryها سازمان‌دهی و برای مشتری قابل جست‌وجو کند.
- &rlm;&lrm;FR-RISE-TKT-010&lrm; دسترسی Knowledge Base می‌تواند Public یا فقط Clientهای Login شده باشد.
- &rlm;&lrm;FR-RISE-TKT-011&lrm; AI Reply باید به‌عنوان پیشنهاد/ابزار Agent عمل کند و به Agent سفارشی قابل اتصال باشد.

### &rlm;Workflow اصلی

&rlm;Ticket از Portal/Email ایجاد می‌شود؛ Auto Reply ارسال می‌شود؛ Routing انجام می‌شود؛ Agent پاسخ یا Note ثبت می‌کند؛ در صورت نیاز Label/Assignee تغییر می‌کند؛ Ticket Resolve/Close می‌شود؛ KB برای کاهش Ticketهای تکراری استفاده می‌شود.

## &rlm;05 — مالی و صورتحساب

### &rlm;دامنه

&rlm;مدیریت Invoice، Tax، Payment، Credit Note، Client Wallet، Expense، Recurring Billing و e-Invoice.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE سه حالت Invoice مالیاتی دارد: Taxable، Non-taxable و Mix. Payment Methodها قابل تنظیم‌اند و پرداخت می‌تواند آنلاین یا دستی باشد. Credit Note مبلغ Invoice را معکوس می‌کند و وضعیت Invoice به Credited تغییر می‌کند. Client Wallet برای نگهداری Advance Payment و مصرف آن روی Invoiceهای آینده وجود دارد. Recurring Invoice با Cron تولید می‌شود و در صورت فعال بودن Notification خودکار برای Contactهای مشتری ایمیل می‌شود؛ در غیر این صورت Invoice در Draft باقی می‌ماند. Subscription نیز Invoice دوره‌ای می‌سازد. Expense و Recurring Expense برای ثبت هزینه و گزارش Profit/Loss وجود دارند. e-Invoice با قالب‌های استاندارد نیز پشتیبانی شده است.

### &rlm;Actorها

&rlm;Admin/Finance Team، Client/Contact و Payment Provider.

### &rlm;موجودیت‌های کلیدی

&rlm;Invoice، Invoice Item، Tax، Payment، Payment Method، Credit Note، Wallet Transaction، Expense، Recurring Invoice Rule، Subscription Billing، Currency.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-FIN-001&lrm; Invoice باید Client، Items، Tax Mode، Currency، Due/Issue Date و Status داشته باشد.
- &rlm;&lrm;FR-RISE-FIN-002&lrm; Tax باید در سطح Item قابل اعمال باشد و Invoice بتواند ترکیب Taxable/Non-taxable داشته باشد.
- &rlm;&lrm;FR-RISE-FIN-003&lrm; Payment باید به Invoice و Client متصل و دارای Method/Transaction Reference باشد.
- &rlm;&lrm;FR-RISE-FIN-004&lrm; Credit Note باید از Invoice ساخته شود و اثر مالی معکوس قابل رهگیری داشته باشد.
- &rlm;&lrm;FR-RISE-FIN-005&lrm; Client Wallet باید Balance و Transaction History داشته باشد و به‌عنوان Payment Method قابل مصرف باشد.
- &rlm;&lrm;FR-RISE-FIN-006&lrm; Advance Payment باید بدون Invoice اولیه قابل ثبت و بعداً روی Invoice تخصیص داده شود.
- &rlm;&lrm;FR-RISE-FIN-007&lrm; Recurring Invoice باید Schedule، Next Run و Generated Invoice History داشته باشد.
- &rlm;&lrm;FR-RISE-FIN-008&lrm; سیستم باید بتواند Generated Invoice را بر اساس Notification Rule خودکار برای Contactهای مشتری ارسال کند.
- &rlm;&lrm;FR-RISE-FIN-009&lrm; Expense باید Amount، Category، Date، Project/Client Reference و Recurrence اختیاری داشته باشد.
- &rlm;&lrm;FR-RISE-FIN-010&lrm; گزارش Income vs Expense و Profit/Loss باید از داده‌های مالی ساخته شود.
- &rlm;&lrm;FR-RISE-FIN-011&lrm; e-Invoice باید از Invoice استاندارد خروجی ساختاریافته قابل تولید داشته باشد.

### &rlm;Workflow اصلی

&rlm;Invoice Draft → Send/Publish → Partial/Full Payment یا Overdue → در صورت اصلاح Credit Note. برای پیش‌پرداخت: Wallet Deposit → Wallet Balance → Apply to Invoice. برای Billing دوره‌ای: Schedule/Cron → Invoice Generation → Notification → Payment.

## &rlm;06 — تیم و منابع انسانی

### &rlm;دامنه

&rlm;مدیریت اعضای تیم، Role/Permission، Team Group، Attendance، Leave و دسترسی‌های سازمانی.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE Team Memberها را با Role و Permission مدیریت می‌کند. Teamهای چندعضوی برای تسهیل Permission، Event Sharing، Notification و File Sharing قابل تعریف‌اند. Attendance و Leave Application در محصول وجود دارند و Leave می‌تواند Reject Reason داشته باشد. Timesheet زمان کاری اعضا را با Timer، Start/End یا Total Time ثبت می‌کند. Internal Wiki برای راهنماهای تیم و File Manager با Permission پوشه‌ای وجود دارد.

### &rlm;Actorها

&rlm;Admin/Owner، Team Member، Team Lead/Manager و HR/Approver.

### &rlm;موجودیت‌های کلیدی

&rlm;Team Member، Role، Permission، Team، Attendance Record، Leave Request، Leave Type، Timesheet، Internal Wiki Article.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-HR-001&lrm; Team Member باید پروفایل کاری و وضعیت دسترسی داشته باشد.
- &rlm;&lrm;FR-RISE-HR-002&lrm; Role باید مجموعه Permissionهای قابل تنظیم را نگه دارد.
- &rlm;&lrm;FR-RISE-HR-003&lrm; Permission باید Scope دسترسی به ماژول/رکورد را کنترل کند.
- &rlm;&lrm;FR-RISE-HR-004&lrm; Team باید شامل چند Member باشد و در نقاط مختلف سیستم به‌عنوان Target قابل استفاده باشد.
- &rlm;&lrm;FR-RISE-HR-005&lrm; Attendance باید ورود/خروج یا وضعیت حضور روزانه را ثبت کند.
- &rlm;&lrm;FR-RISE-HR-006&lrm; Leave Request باید Type، بازه زمانی، Reason و Status داشته باشد.
- &rlm;&lrm;FR-RISE-HR-007&lrm; Approver باید Leave را Approve/Reject کند و Reject Reason قابل ثبت باشد.
- &rlm;&lrm;FR-RISE-HR-008&lrm; Timesheet باید حداقل سه Mode ثبت Timer، Start/End و Total Duration را پشتیبانی کند.
- &rlm;&lrm;FR-RISE-HR-009&lrm; Time Log باید به Member و Context کاری مثل Project/Task متصل شود.
- &rlm;&lrm;FR-RISE-HR-010&lrm; محتوای داخلی تیم مانند Wiki باید با Access Control سازمانی قابل انتشار باشد.

### &rlm;نکات مرزی

&rlm;Team با Role متفاوت است. Role «چه کاری مجاز است» را تعیین می‌کند؛ Team «چه کسانی در یک گروه‌اند» را برای اشتراک و تخصیص گروهی مشخص می‌کند.

## &rlm;07 — همکاری و ارتباطات

### &rlm;دامنه

&rlm;ارتباط بلادرنگ و غیرهمزمان بین Team و Client و انتشار اعلان‌های سازمانی.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE پیام داخلی بین Team Memberها و Clientها دارد و Permission تعیین می‌کند چه کسانی می‌توانند با Client/Team گفتگو کنند. Pusher برای تجربه Real-time قابل اتصال است. Announcement برای نمایش اطلاعیه به Team یا Client روی Dashboard استفاده می‌شود. Internal Timeline برای Share کردن ایده و Update بین اعضای تیم وجود دارد. Comment، Mention/Notification و File Sharing در Context پروژه و تسک نیز بخشی از Collaboration است. Notificationها از مسیر In-app، Email، Slack و Push قابل تنظیم‌اند.

### &rlm;Actorها

&rlm;Team Member، Client Contact، Admin و Recipient Group/Team.

### &rlm;موجودیت‌های کلیدی

&rlm;Conversation، Message، Participant، Announcement، Timeline Post، Comment، Notification، Notification Preference.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-COL-001&lrm; سیستم باید Direct Messaging داخلی بین Userهای مجاز را پشتیبانی کند.
- &rlm;&lrm;FR-RISE-COL-002&lrm; امکان Chat با Client باید از Permission مستقل کنترل شود.
- &rlm;&lrm;FR-RISE-COL-003&lrm; Message باید Read/Unread و Delivery/Notification State داشته باشد.
- &rlm;&lrm;FR-RISE-COL-004&lrm; Announcement باید Audience قابل انتخاب مثل Team/Client داشته باشد.
- &rlm;&lrm;FR-RISE-COL-005&lrm; Timeline Post باید برای اشتراک Update داخلی قابل استفاده باشد.
- &rlm;&lrm;FR-RISE-COL-006&lrm; Notification Rule باید Event، Channel و Recipient Target را مشخص کند.
- &rlm;&lrm;FR-RISE-COL-007&lrm; Channelهای Notification می‌توانند In-app، Email، Slack و Push باشند.
- &rlm;&lrm;FR-RISE-COL-008&lrm; Collaboration contextual در Project/Task باید Comment و File را کنار همان رکورد نگه دارد.

### &rlm;نکات تحلیلی

&rlm;RISE ارتباط را در دو سطح جدا نگه می‌دارد: Messaging عمومی بین افراد و Collaboration وابسته به Context مثل Task Comment. این تفکیک برای Search، Permission و Notification مهم است.

## &rlm;08 — بهره‌وری و تقویم

### &rlm;دامنه

&rlm;ابزارهای شخصی و سازمان‌دهی کار روزانه، یادآوری‌ها، Event و Viewهای سریع.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Personal Todo List، Personal Notes، Sticky Note، Personal Reminders و Personal Calendar وجود دارد. Event می‌تواند Recurring باشد و با Google Calendar Sync شود. Smart Filterها قابل Bookmark هستند و آخرین Filter State حفظ می‌شود. Compact View جزئیات Record را بدون خروج از List نشان می‌دهد. چند Dashboard قابل ساخت و Widgetها قابل چیدمان هستند.

### &rlm;Actorها

&rlm;هر User در ابزارهای شخصی؛ Admin برای تنظیم Dashboard و برخی Event/Notificationها.

### &rlm;موجودیت‌های کلیدی

&rlm;Todo، Note، Sticky Note، Reminder، Calendar Event، Recurrence Rule، Smart Filter، Dashboard، Widget Layout.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-PROD-001&lrm; Todo شخصی باید Private و مستقل از Task پروژه باشد.
- &rlm;&lrm;FR-RISE-PROD-002&lrm; Note و Sticky Note شخصی باید فقط برای Owner قابل مشاهده باشند مگر خلاف آن تعریف شود.
- &rlm;&lrm;FR-RISE-PROD-003&lrm; Reminder باید زمان Trigger و Link اختیاری به Entity داشته باشد.
- &rlm;&lrm;FR-RISE-PROD-004&lrm; Event باید Audience، Start/End، Recurrence و Calendar Sync اختیاری داشته باشد.
- &rlm;&lrm;FR-RISE-PROD-005&lrm; Smart Filter باید Criteria و Owner را ذخیره کند و قابل Bookmark باشد.
- &rlm;&lrm;FR-RISE-PROD-006&lrm; Listها باید بتوانند Last Filter State را برای User حفظ کنند.
- &rlm;&lrm;FR-RISE-PROD-007&lrm; Compact View باید Detail Record را بدون تغییر Context اصلی نمایش دهد.
- &rlm;&lrm;FR-RISE-PROD-008&lrm; Dashboard باید چند Layout/Widget Set قابل ذخیره داشته باشد.

### &rlm;نکات مرزی

&rlm;Todo شخصی با Task پروژه یکسان نیست. Todo برای برنامه‌ریزی فردی و بدون چرخه Project Membership طراحی شده است.

## &rlm;09 — فایل و دانش

### &rlm;دامنه

&rlm;مدیریت فایل مشترک، Permission پوشه‌ای، Knowledge Base مشتری و Wiki داخلی.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;File Manager یک فضای مرکزی برای Folder/File است. Folder می‌تواند با Team، Team Member یا Client Share شود و Access Level سفارشی داشته باشد. Subfolderها به‌صورت پیش‌فرض Permission والد را به ارث می‌برند. Drag & Drop، Preview فایل و Bookmark Folder وجود دارد. Knowledge Base برای مشتری Article و Category دارد و Self-service Support ایجاد می‌کند. دسترسی Knowledge Base می‌تواند فقط برای Clientهای Login شده محدود شود. Internal Wiki نیز برای Team استفاده می‌شود.

### &rlm;Actorها

&rlm;Admin/Content Manager، Team Member، Client Contact و در صورت Public بودن Visitor.

### &rlm;موجودیت‌های کلیدی

&rlm;Folder، File، Folder Permission، Share Target، Bookmark، Knowledge Category، Knowledge Article، Wiki Article.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-FILE-001&lrm; File Manager باید ساختار سلسله‌مراتبی Folder/Subfolder داشته باشد.
- &rlm;&lrm;FR-RISE-FILE-002&lrm; Folder Permission باید برای Team، User و Client قابل تعریف باشد.
- &rlm;&lrm;FR-RISE-FILE-003&lrm; Child Folder باید به‌صورت پیش‌فرض Permission والد را Inherit کند و امکان Override داشته باشد.
- &rlm;&lrm;FR-RISE-FILE-004&lrm; File باید Upload، Move/Organize، Preview و Metadata پایه داشته باشد.
- &rlm;&lrm;FR-RISE-FILE-005&lrm; User باید بتواند Folderهای پرتکرار را Bookmark کند.
- &rlm;&lrm;FR-RISE-KB-001&lrm; Knowledge Base باید Category و Article داشته باشد.
- &rlm;&lrm;FR-RISE-KB-002&lrm; Article باید Search/Browse برای مشتری را پشتیبانی کند.
- &rlm;&lrm;FR-RISE-KB-003&lrm; Visibility Knowledge Base باید Public/Authenticated قابل تنظیم باشد.
- &rlm;&lrm;FR-RISE-KB-004&lrm; Wiki داخلی باید از Knowledge Base مشتری از نظر Audience جدا باشد.

### &rlm;نکات مرزی

&rlm;Project File و File Manager می‌توانند دو Context متفاوت باشند: فایل وابسته به Project و فایل سازمانی عمومی. طراحی داده باید از مخلوط‌کردن Ownership این دو جلوگیری کند.

## &rlm;10 — گزارش و داشبورد

### &rlm;دامنه

&rlm;نمایش وضعیت عملیاتی و مالی از داده‌های ماژول‌های مختلف.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE چند Dashboard قابل سفارشی‌سازی با Widget Layout دارد. Project Progress خودکار محاسبه می‌شود. Income vs Expense Report وجود دارد و نسخه 4.0 Currency Filter به آن اضافه کرده است. Client، Project، Task، Timesheet، Attendance، Leave، Ticket و Finance داده‌های قابل گزارش تولید می‌کنند. Widgetهای عملیاتی مانند Contracts Expiring Soon نیز در نسخه‌های جدید اضافه شده‌اند.

### &rlm;Actorها

&rlm;Admin/Manager، Finance، Project Manager و User دارای Permission گزارش.

### &rlm;موجودیت‌های کلیدی

&rlm;Dashboard، Widget، Report Definition، Filter، Metric، Aggregate.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-REP-001&lrm; Dashboard باید Widgetهای قابل افزودن/حذف/چیدمان داشته باشد.
- &rlm;&lrm;FR-RISE-REP-002&lrm; User/Role باید فقط Metricهای مجاز را ببیند.
- &rlm;&lrm;FR-RISE-REP-003&lrm; گزارش‌ها باید Filter زمانی و Contextual داشته باشند.
- &rlm;&lrm;FR-RISE-REP-004&lrm; گزارش مالی باید Currency-aware باشد.
- &rlm;&lrm;FR-RISE-REP-005&lrm; Project Progress باید از داده‌های اجرای پروژه محاسبه شود و Source of Truth مشخص داشته باشد.
- &rlm;&lrm;FR-RISE-REP-006&lrm; گزارش‌های عملیاتی باید Drill-down به Recordهای مبنا را تا حد امکان پشتیبانی کنند.
- &rlm;&lrm;FR-RISE-REP-007&lrm; Widgetهای Alert-oriented مثل قراردادهای نزدیک انقضا باید بر Rule زمانی متکی باشند.

### &rlm;نکات تحلیلی

&rlm;Dashboard در RISE فقط صفحه تزئینی نیست؛ یک Surface تجمیع Cross-domain است. بنابراین Permission، Query Performance و تعریف Metric در این لایه مهم می‌شود.

## &rlm;11 — تنظیمات و سفارشی‌سازی

### &rlm;دامنه

&rlm;قابل تنظیم‌کردن ظاهر، Navigation، Moduleها، فرم‌ها، Notificationها و رفتار عمومی سیستم.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Feature/Moduleها قابل Enable/Disable هستند. Dashboard، Left Menu، Theme، Notification، Custom Field، Email Template، Landing Page و Custom Page قابل تنظیم‌اند. Client Portal نیز Dashboard، Left Menu و Client Permission مستقل دارد. زبان قابل انتخاب است. reCAPTCHA برای Login/Signup/Public Form قابل فعال‌سازی است. Update از داخل Settings پشتیبانی می‌شود.

### &rlm;Actorها

&rlm;Admin/Owner و در برخی موارد User برای Theme/Profile Preference.

### &rlm;موجودیت‌های کلیدی

&rlm;Module Setting، Feature Flag، Menu Item، Theme Setting، Dashboard Config، Custom Field Definition، Email Template، Custom Page، Notification Rule، Localization Setting، Security Setting.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-SET-001&lrm; Admin باید بتواند Moduleهای غیرضروری را غیرفعال کند.
- &rlm;&lrm;FR-RISE-SET-002&lrm; Navigation باید قابل reorder/customize باشد.
- &rlm;&lrm;FR-RISE-SET-003&lrm; Custom Field باید Module Target، Type، Validation و Visibility داشته باشد.
- &rlm;&lrm;FR-RISE-SET-004&lrm; Email Template باید Event/Use Case و Variables قابل جایگزینی داشته باشد.
- &rlm;&lrm;FR-RISE-SET-005&lrm; Notification Setting باید Event، Channel و Recipients را کنترل کند.
- &rlm;&lrm;FR-RISE-SET-006&lrm; Client Portal Setting باید از Staff App Setting تفکیک شود.
- &rlm;&lrm;FR-RISE-SET-007&lrm; Localization باید حداقل Language و Date/Currency presentation را پوشش دهد.
- &rlm;&lrm;FR-RISE-SET-008&lrm; Security Setting باید قابلیت‌هایی مانند reCAPTCHA و تنظیمات Login را کنترل کند.
- &rlm;&lrm;FR-RISE-SET-009&lrm; Custom Page باید Visibility عمومی/خصوصی و Navigation Placement داشته باشد.
- &rlm;&lrm;FR-RISE-SET-010&lrm; Feature Toggle نباید داده موجود را با Disable شدن ماژول از بین ببرد.

### &rlm;نکات مرزی

&rlm;Custom Field، Feature Toggle و Permission سه مفهوم جدا هستند: Custom Field ساختار داده را توسعه می‌دهد؛ Feature Toggle قابلیت را فعال/غیرفعال می‌کند؛ Permission تعیین می‌کند چه کسی از قابلیت فعال استفاده کند.

## &rlm;12 — اتوماسیون و AI

### &rlm;دامنه

&rlm;کاهش کار دستی با Recurrence، Cron، Rule-based Routing و AI Assistant.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;RISE Recurring Task، Recurring Invoice، Recurring Expense و Recurring Event دارد. Cron Job برای اجرای بسیاری از Recurrenceها لازم است. Email-to-Ticket و Auto Reply و Ruleهای Routing برای Ticket وجود دارند. نسخه 4.0 AI Assistant، ChatGPT/Gemini Integration، AI Agent با Custom Training، AI Chatbox، Quick Assistant Context Menu و AI-powered Ticket Reply اضافه کرده است.

### &rlm;Actorها

&rlm;Admin/Automation Configurator، End User، Support Agent و External AI Provider.

### &rlm;موجودیت‌های کلیدی

&rlm;Automation Rule، Schedule/Recurrence Rule، Cron Run، Trigger، Condition، Action، AI Provider Config، AI Agent، Training Source، AI Conversation/Prompt Context.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-AUTO-001&lrm; Recurrence باید Frequency، Start/End/Cycle و Next Run داشته باشد.
- &rlm;&lrm;FR-RISE-AUTO-002&lrm; اجرای Schedule باید Idempotent باشد تا Run تکراری Record اضافی نسازد.
- &rlm;&lrm;FR-RISE-AUTO-003&lrm; Automation Rule باید Trigger، Conditions و Actions قابل تعریف داشته باشد.
- &rlm;&lrm;FR-RISE-AUTO-004&lrm; Ticket Routing باید بر محتوای Email و Ruleهای قابل تنظیم کار کند.
- &rlm;&lrm;FR-RISE-AUTO-005&lrm; AI Provider باید Config/Secret جدا و قابل تعویض داشته باشد.
- &rlm;&lrm;FR-RISE-AUTO-006&lrm; AI Agent باید Instruction/Training Context مستقل داشته باشد.
- &rlm;&lrm;FR-RISE-AUTO-007&lrm; AI Ticket Reply باید Context Ticket را دریافت و Draft/Proposal برای Agent ایجاد کند.
- &rlm;&lrm;FR-RISE-AUTO-008&lrm; AI Output نباید بدون Policy مشخص به‌صورت خودکار به مشتری ارسال شود.
- &rlm;&lrm;FR-RISE-AUTO-009&lrm; Automation Run باید Log/Failure State قابل بررسی داشته باشد.

### &rlm;نکات تحلیلی

&rlm;Automationهای قطعی زمان‌بندی‌شده با AI Automation یک چیز نیستند. اولی Deterministic و قابل Retry است؛ دومی نیازمند کنترل Context، Cost، Provider Failure و Human Review است.

## &rlm;13 — یکپارچه‌سازی و توسعه‌پذیری

### &rlm;دامنه

&rlm;اتصال RISE به سرویس‌های بیرونی و توسعه قابلیت بدون تغییر Core.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Google Drive برای Storage، Google Calendar برای Event Sync، Gmail API و Outlook برای SMTP/IMAP، Email-to-Ticket، Pusher برای Real-time/Push، Slack برای Notification، GitHub و Bitbucket برای Commit Log روی Task، Payment Gatewayهایی مانند PayPal/Stripe/Paytm و Plugin Hooks برای توسعه وجود دارند. RISE Plugin System اجازه نصب Featureهای جدید را می‌دهد. reCAPTCHA و Native App Notification نیز پشتیبانی شده‌اند.

### &rlm;Actorها

&rlm;Admin/Integrator، Developer/Plugin Author و External Service.

### &rlm;موجودیت‌های کلیدی

&rlm;Integration Config، Credential/Token، Webhook/Callback، External Account Mapping، Sync State، Plugin، Hook/Event.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-RISE-INT-001&lrm; Integration Credential باید امن، قابل Rotation و جدا از Business Data نگهداری شود.
- &rlm;&lrm;FR-RISE-INT-002&lrm; Calendar Sync باید Mapping بین Event داخلی و خارجی داشته باشد.
- &rlm;&lrm;FR-RISE-INT-003&lrm; Email Integration باید Send و Receive را با Providerهای مختلف abstraction کند.
- &rlm;&lrm;FR-RISE-INT-004&lrm; Commit Integration باید External Commit را به Task Activity مرتبط کند.
- &rlm;&lrm;FR-RISE-INT-005&lrm; Notification Integration باید Channel Failure را از Business Transaction جدا کند.
- &rlm;&lrm;FR-RISE-INT-006&lrm; Payment Gateway باید Provider-specific Flow را پشت Interface مشترک نگه دارد.
- &rlm;&lrm;FR-RISE-INT-007&lrm; Plugin باید بدون تغییر Core نصب/فعال/غیرفعال شود.
- &rlm;&lrm;FR-RISE-INT-008&lrm; Hook/Event Extension Point باید برای Moduleهای اصلی در دسترس باشد.
- &rlm;&lrm;FR-RISE-INT-009&lrm; Integration Sync/Callback باید Audit و Error Handling داشته باشد.

### &rlm;نکات تحلیلی

&rlm;RISE عمداً Core را با Plugin Hook توسعه‌پذیر می‌کند. برای محصولی که قرار است رشد کند، مرز Extension Pointها بخشی از طراحی Domain نیست اما روی Coupling و قابلیت توسعه اثر مستقیم دارد.
