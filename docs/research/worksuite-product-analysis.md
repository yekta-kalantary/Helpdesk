<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# Worksuite — Product Analysis

## &rlm;00 — نقشه محصول و دامنه‌ها

### &rlm;هدف این سند

&rlm;این سند تحلیل مرجع Worksuite است و مستقیماً معماری یا Scope پروژه ما را تعیین نمی‌کند. هدف، استخراج ساختار دامنه‌ها، قابلیت‌های مشاهده‌شده، Actorها، موجودیت‌ها، Workflowها و نیازمندی‌هایی است که برای بازتولید رفتار محصول لازم می‌شوند.

### &rlm;روش تحلیل

&rlm;«قابلیت مشاهده‌شده» فقط چیزی است که در مستندات رسمی Worksuite/Froiden یا Release Notes محصول دیده شده است. «نیازمندی استخراج‌شده» برداشت مهندسی از رفتاری است که برای اجرای آن قابلیت لازم است. Core Product و Add-on/Optional Module از هم جدا نگه داشته شده‌اند.

### &rlm;دامنه‌های اصلی

&rlm;CRM و مشتریان؛ منابع انسانی و کارکنان؛ پروژه، تسک و زمان؛ قراردادها؛ مالی و صورتحساب؛ محصولات و سفارش‌ها؛ Helpdesk و Ticket؛ همکاری/پیام/رویداد؛ گزارش‌ها و تحلیل؛ تنظیمات/نقش/دسترسی؛ یکپارچه‌سازی‌ها؛ افزونه‌های اختیاری.

### &rlm;Actorهای اصلی

&rlm;Admin/Authority، Employee، Client، Lead/Prospect، Ticket Agent، Project Member، Finance/HR Manager و External Service Provider.

### &rlm;الگوی کلی محصول

&rlm;Worksuite یک Business Management Suite با سه ستون اصلی Project Management، HR و CRM است که Finance، Sales/Order، Helpdesk و Collaboration روی آن سوار شده‌اند. Employee و Client دو Actor سطح‌اول‌اند و هر دو در چندین Domain مثل Project، Message، Event و Ticket حضور دارند.

### &rlm;منابع اصلی بررسی‌شده

&rlm;مستندات رسمی New Worksuite Documentation در Froiden/Freshdesk، بخش‌های Customers، HR, Work, Finance, Products, Orders, Tickets, Events, Messages, Notice Board, Reports, Application Settings, Integrations و Release Notes رسمی محصول.

## &rlm;01 — CRM و مشتریان

### &rlm;دامنه

&rlm;مدیریت Lead، تبدیل آن به Client و ایجاد نمای یکپارچه از سوابق تجاری Client.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Lead دارای Source، Owner، Status، Follow-up، Profile، Deals و Notes است و قابل Export و Convert to Client است. هنگام ایجاد Lead اطلاعاتی مثل Salutation، Name، Email، Source، Owner، Company، Website، Mobile/Phone، Country/State/City/Postal Code و Address قابل ثبت‌اند و Deal نیز می‌تواند همزمان ایجاد شود. Client Summary نمایی از Profile، Projects، Invoices، Estimates، Credit Notes، Payments، Contacts و Notes می‌دهد. Custom Lead Fields نیز در نسخه‌های جدید اضافه شده‌اند.

### &rlm;Actorها

&rlm;Admin/Sales، Lead Owner، Employee دارای CRM Permission، Lead/Prospect و Client.

### &rlm;موجودیت‌های کلیدی

&rlm;Lead، Lead Source، Lead Status، Lead Owner، Follow-up، Deal، Client، Client Contact، Note، Custom Field.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-CRM-001&lrm; سیستم باید Lead را با Owner، Source و Status قابل مدیریت نگه دارد.
- &rlm;&lrm;FR-WS-CRM-002&lrm; Lead باید Follow-up History و Notes داشته باشد.
- &rlm;&lrm;FR-WS-CRM-003&lrm; Lead می‌تواند یک یا چند Deal/Opportunity مرتبط داشته باشد.
- &rlm;&lrm;FR-WS-CRM-004&lrm; Lead باید بدون از دست‌رفتن اطلاعات اصلی به Client تبدیل شود.
- &rlm;&lrm;FR-WS-CRM-005&lrm; Client باید Contactهای متعدد داشته باشد.
- &rlm;&lrm;FR-WS-CRM-006&lrm; Client Profile باید روابط مهم شامل Project، Invoice، Estimate، Credit Note، Payment و Note را تجمیع کند.
- &rlm;&lrm;FR-WS-CRM-007&lrm; Lead/Client باید Search، Filter و Export داشته باشند.
- &rlm;&lrm;FR-WS-CRM-008&lrm; فرم Lead باید Custom Field قابل تنظیم داشته باشد.
- &rlm;&lrm;FR-WS-CRM-009&lrm; دسترسی Employeeها به CRM باید از Role/Permission تبعیت کند.
- &rlm;&lrm;FR-WS-CRM-010&lrm; Conversion باید ارتباط Deal/Note/Follow-up قابل حفظ را مشخص کند.

### &rlm;Workflow اصلی

&rlm;Lead ثبت می‌شود؛ Owner مشخص می‌شود؛ Follow-up و Deal ایجاد می‌شوند؛ Status در چرخه فروش تغییر می‌کند؛ Lead موفق به Client تبدیل می‌شود؛ سپس Project، Contract، Estimate، Invoice و Support به Client متصل می‌شوند.

### &rlm;نکات مرزی

&rlm;Lead، Client و Client Contact سه مفهوم متفاوت‌اند. Lead هنوز Prospect است؛ Client حساب تجاری پذیرفته‌شده است؛ Contact یک فرد مرتبط با Client است.

## &rlm;02 — منابع انسانی و کارکنان

### &rlm;دامنه

&rlm;مدیریت Employee Lifecycle، حضور، مرخصی، شیفت، اسناد و داده‌های منابع انسانی.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Employee می‌تواند دستی ایجاد یا با Invitation اضافه شود. پروفایل Employee شامل Projects، Gantt، Tasks، Leaves/Leave Quota، Time Logs، Documents، Permissions، Emergency Contacts، Increment & Promotion، Tickets، Appreciation، Shift Roster و Passport است. Leave به شکل List/Calendar با Category، Status و Reason مدیریت می‌شود و Approval/Reject وابسته به Permission است. Attendance دارای Easy/Member View و وضعیت‌هایی مثل Working، Present، Late، Half Day، Absent و Holiday است و Clock In/Out دستی پشتیبانی می‌شود. Shift Roster امکان تخصیص گروهی شیفت، تغییر شیفت توسط Employee و Accept/Decline توسط Admin دارد.

### &rlm;Actorها

&rlm;Admin/HR، Manager/Approver و Employee.

### &rlm;موجودیت‌های کلیدی

&rlm;Employee، Invitation، Designation، Department، Emergency Contact، Document، Leave Type، Leave Request، Leave Quota، Attendance Record، Clock Event، Shift، Shift Assignment، Shift Change Request، Appreciation، Promotion/Increment.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-HR-001&lrm; Employee باید پروفایل سازمانی مستقل از User Authentication داشته باشد.
- &rlm;&lrm;FR-WS-HR-002&lrm; Onboarding باید هم Manual Creation و هم Invitation را پشتیبانی کند.
- &rlm;&lrm;FR-WS-HR-003&lrm; Employee Profile باید روابط کاری مهم را در یک نمای واحد تجمیع کند.
- &rlm;&lrm;FR-WS-HR-004&lrm; Leave Type و Leave Quota باید قابل تعریف باشند.
- &rlm;&lrm;FR-WS-HR-005&lrm; Leave Request باید بازه، Category، Reason، Status و Approver داشته باشد.
- &rlm;&lrm;FR-WS-HR-006&lrm; Approve/Reject Leave باید Permission-controlled باشد.
- &rlm;&lrm;FR-WS-HR-007&lrm; Attendance باید Clock In/Out و وضعیت روزانه قابل محاسبه/اصلاح داشته باشد.
- &rlm;&lrm;FR-WS-HR-008&lrm; Shift باید قابل تخصیص به چند Employee و چند Date باشد.
- &rlm;&lrm;FR-WS-HR-009&lrm; Employee باید بتواند Shift Change Request ایجاد کند و Manager آن را Approve/Reject کند.
- &rlm;&lrm;FR-WS-HR-010&lrm; HR Document باید به Employee متصل و Access-controlled باشد.
- &rlm;&lrm;FR-WS-HR-011&lrm; Promotion/Increment و Appreciation باید تاریخچه Employee را بسازند.
- &rlm;&lrm;FR-WS-HR-012&lrm; Employee Status و Lifecycle باید بدون حذف History قابل مدیریت باشد.

### &rlm;نکات تحلیلی

&rlm;Worksuite Employee را فقط User نمی‌بیند؛ Employee یک Aggregate پرجزئیات منابع انسانی است. Authentication/Role و HR Profile بهتر است از نظر مفهوم از هم جدا بمانند.

## &rlm;03 — پروژه، تسک و زمان

### &rlm;دامنه

&rlm;برنامه‌ریزی پروژه، همکاری تیم، زمان‌بندی، Task Execution و Time Tracking.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Project دارای Name، Start/Deadline، Category، Department، Client، Summary، Notes و Members است. List پروژه Progress، Deadline، Client و Completion را نمایش می‌دهد و Gantt، Archive و Edit/Delete دارد. Releaseهای جدید Custom Project Status، Project Calendar و Project Shortcode را اضافه کرده‌اند. Task دارای Title، Category، Project، Start/Due Date، Assigned Employee، Description و Label است و Kanban Taskboard برای جریان کار وجود دارد. Milestone، Task Label، Files/Comments/Notes/History و Time Logs در Context پروژه/تسک استفاده می‌شوند. Time Log با Project، Task، Employee، Start و End ثبت و برای Report/Project Aggregate استفاده می‌شود.

### &rlm;Actorها

&rlm;Project Manager/Admin، Employee/Project Member، Task Assignee و Client در Visibilityهای مجاز.

### &rlm;موجودیت‌های کلیدی

&rlm;Project، Project Member، Project Status، Project Category، Milestone، Task، Task Assignee، Task Label، Comment، File، Time Log، Gantt Item.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-WORK-001&lrm; Project باید Client اختیاری، Members، Start Date، Deadline و Status داشته باشد.
- &rlm;&lrm;FR-WS-WORK-002&lrm; Project Membership باید مبنای Visibility و Assignment قابل استفاده باشد.
- &rlm;&lrm;FR-WS-WORK-003&lrm; Project باید Progress قابل محاسبه و Status قابل سفارشی‌سازی داشته باشد.
- &rlm;&lrm;FR-WS-WORK-004&lrm; Project باید List، Calendar و Gantt View قابل ارائه داشته باشد.
- &rlm;&lrm;FR-WS-WORK-005&lrm; Task باید Project، Assignee، Label/Category، Start/Due Date، Description و Status داشته باشد.
- &rlm;&lrm;FR-WS-WORK-006&lrm; Task باید در Kanban بر اساس Workflow Status قابل جابه‌جایی باشد.
- &rlm;&lrm;FR-WS-WORK-007&lrm; Task Visibility برای Employee و Client باید قابل تنظیم باشد.
- &rlm;&lrm;FR-WS-WORK-008&lrm; Milestone باید بازه/تاریخ و ارتباط با Project داشته باشد.
- &rlm;&lrm;FR-WS-WORK-009&lrm; Time Log باید Employee، Project، Task، Start/End یا Duration قابل استخراج داشته باشد.
- &rlm;&lrm;FR-WS-WORK-010&lrm; مجموع Time Log باید در Project و Report قابل Aggregate باشد.
- &rlm;&lrm;FR-WS-WORK-011&lrm; Files، Comments، Notes و History باید در Context رکورد کاری باقی بمانند.
- &rlm;&lrm;FR-WS-WORK-012&lrm; Deadline Reminder باید Rule قابل تنظیم برای Recipient و Days-before داشته باشد.

### &rlm;Workflow اصلی

&rlm;Project ایجاد و Client/Memberها تعیین می‌شوند؛ Milestone/Task تعریف می‌شود؛ Task Assign و روی Kanban مدیریت می‌شود؛ Time Log و Collaboration ثبت می‌شود؛ Progress/Gantt/Calendar وضعیت اجرای پروژه را نمایش می‌دهند؛ Project در پایان Archive/Complete می‌شود.

## &rlm;04 — قراردادها

### &rlm;دامنه

&rlm;ثبت و مدیریت توافق رسمی با Client و رهگیری چرخه قرارداد و تمدید.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Contract دارای Subject، Client، Amount، Start Date و End Date است. امکان Public Link، Copy، Edit، Delete و Download وجود دارد. Detail قرارداد Client Details، Summary، Discussion، Files و Renewal History را نشان می‌دهد. Worksuite در معرفی محصول از e-signature در فضای Contract/Finance نیز نام می‌برد.

### &rlm;Actorها

&rlm;Admin/Sales/Account Manager، Client و User دارای Contract Permission.

### &rlm;موجودیت‌های کلیدی

&rlm;Contract، Client، Contract Discussion، Contract File، Renewal، Signature/Acceptance State.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-CON-001&lrm; Contract باید Client، Subject، Amount، Start/End Date و Status داشته باشد.
- &rlm;&lrm;FR-WS-CON-002&lrm; Contract باید Public/Shareable View کنترل‌شده داشته باشد.
- &rlm;&lrm;FR-WS-CON-003&lrm; Contract باید فایل و Discussion مرتبط نگه دارد.
- &rlm;&lrm;FR-WS-CON-004&lrm; Renewal باید به Contract قبلی مرتبط باشد و Renewal History حفظ شود.
- &rlm;&lrm;FR-WS-CON-005&lrm; Copy/Duplicate Contract باید داده‌های قابل کپی و داده‌های منحصربه‌فرد را تفکیک کند.
- &rlm;&lrm;FR-WS-CON-006&lrm; Download/Printable Representation باید از نسخه قرارداد تولید شود.
- &rlm;&lrm;FR-WS-CON-007&lrm; در صورت استفاده از e-signature، Signer، Time، Status و Audit Evidence باید ثبت شوند.
- &rlm;&lrm;FR-WS-CON-008&lrm; Contract Permission باید مشاهده و عملیات حساس را جداگانه کنترل کند.

### &rlm;Workflow اصلی

&rlm;Draft/Create → Share/Review → Acceptance/Signature در صورت فعال بودن → Active → Expiring/Expired → Renewal یا Closure.

## &rlm;05 — مالی و صورتحساب

### &rlm;دامنه

&rlm;مدیریت Proposal، Estimate، Invoice، Payment، Credit Note، Expense و جریان وصول.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Finance Module شامل Proposal، Estimate، Invoice، Payment، Credit Note و Expense است. Estimate دارای Number، Validity، Currency، Client، Tax، Billing Address و Note است و می‌تواند ارسال، Duplicate، Cancel یا به Invoice تبدیل شود. Invoice به Project/Client مرتبط است و Create، Recurring Invoice، Invoice from Time Logs، Send، Payment Link، Reminder، Add Payment و Credit Note را پشتیبانی می‌کند. Payment به Project/Invoice/Order متصل و Transaction ID دارد. Credit Note از Invoice قابل ایجاد و در Invoice آینده قابل Redeem است. Expense شامل Item، Price، Employee، Vendor، Date، Status و Bill Document است و فرآیند Approval دارد.

### &rlm;Actorها

&rlm;Admin/Finance، Employee دارای Finance Permission، Client و Payment Gateway.

### &rlm;موجودیت‌های کلیدی

&rlm;Proposal، Estimate، Invoice، Invoice Item، Tax، Currency، Payment، Payment Gateway Transaction، Credit Note، Expense، Vendor، Recurring Invoice Rule.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-FIN-001&lrm; Estimate باید Client، Currency، Validity، Items/Tax و Status داشته باشد.
- &rlm;&lrm;FR-WS-FIN-002&lrm; Estimate باید قابل ارسال، Duplicate، Cancel و Convert to Invoice باشد.
- &rlm;&lrm;FR-WS-FIN-003&lrm; Invoice باید Client و در صورت نیاز Project/Order را مرجع دهد.
- &rlm;&lrm;FR-WS-FIN-004&lrm; Invoice باید از Items و در سناریوی خدماتی از Time Log قابل تولید باشد.
- &rlm;&lrm;FR-WS-FIN-005&lrm; Recurring Invoice باید Rule زمانی و Generation History داشته باشد.
- &rlm;&lrm;FR-WS-FIN-006&lrm; Payment باید Amount، Date، Method/Provider، Status و Transaction Reference داشته باشد.
- &rlm;&lrm;FR-WS-FIN-007&lrm; Payment Link باید Invoice مشخص را به Checkout/Provider متصل کند.
- &rlm;&lrm;FR-WS-FIN-008&lrm; Invoice Reminder باید قبل/بعد از Due Date بر اساس Rule قابل ارسال باشد.
- &rlm;&lrm;FR-WS-FIN-009&lrm; Credit Note باید اثر مالی قابل رهگیری و قابلیت Redeem روی Invoice/Payment آینده داشته باشد.
- &rlm;&lrm;FR-WS-FIN-010&lrm; Expense باید Employee/Vendor، Amount، Date، Category/Item، Status و Attachment/Bill داشته باشد.
- &rlm;&lrm;FR-WS-FIN-011&lrm; Expense Approval باید از Permission و Workflow وضعیت تبعیت کند.
- &rlm;&lrm;FR-WS-FIN-012&lrm; سیستم باید Multi-currency را در اسناد و گزارش‌های مالی لحاظ کند.

### &rlm;Workflow اصلی

&rlm;Proposal/Estimate → Client Review → Invoice → Reminder/Payment → Paid؛ در صورت برگشت مبلغ Credit Note ایجاد و Redeem می‌شود. Expense نیز Draft/Submitted → Approved/Rejected → Accounting/Report را طی می‌کند.

## &rlm;06 — محصولات و سفارش‌ها

### &rlm;دامنه

&rlm;تعریف Product/Service Catalog و تبدیل انتخاب مشتری به Order، Invoice و Payment.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Product می‌تواند کالا یا خدمت باشد و اطلاعات Name، Price، Tax، HSN/SAC، Category، Subcategory، Description و Client Accessibility دارد. Product برای Client قابل خرید است و می‌تواند به Project نیز اضافه شود. Client اقلام را به Cart اضافه و Order ثبت می‌کند. Order برای Admin اعلان ایجاد می‌کند، سپس Bill/Invoice و Payment انجام می‌شود. Order List شامل Amount، Date، Status و Payment Status است.

### &rlm;Actorها

&rlm;Admin/Sales، Client و Payment Provider.

### &rlm;موجودیت‌های کلیدی

&rlm;Product/Service، Product Category/Subcategory، Tax، Cart، Cart Item، Order، Order Item، Invoice، Payment.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-ORD-001&lrm; Product باید Type، Name، Description، Price، Tax و Category داشته باشد.
- &rlm;&lrm;FR-WS-ORD-002&lrm; Product Visibility/Accessibility برای Client باید قابل کنترل باشد.
- &rlm;&lrm;FR-WS-ORD-003&lrm; Product می‌تواند در Context پروژه یا فروش مستقیم استفاده شود.
- &rlm;&lrm;FR-WS-ORD-004&lrm; Cart باید اقلام، Quantity، Unit Price، Tax و Total را محاسبه کند.
- &rlm;&lrm;FR-WS-ORD-005&lrm; Checkout باید Cart را به Order پایدار تبدیل کند.
- &rlm;&lrm;FR-WS-ORD-006&lrm; Order باید Client، Items، Amount، Order Status و Payment Status داشته باشد.
- &rlm;&lrm;FR-WS-ORD-007&lrm; ثبت Order باید Notification لازم برای مسئول سازمان ایجاد کند.
- &rlm;&lrm;FR-WS-ORD-008&lrm; Order باید قابلیت تولید/اتصال Invoice داشته باشد.
- &rlm;&lrm;FR-WS-ORD-009&lrm; Payment باید Order/Invoice مربوطه را تسویه کند و وضعیت‌ها همگام شوند.
- &rlm;&lrm;FR-WS-ORD-010&lrm; تغییر Price/Product بعد از Order نباید Historical Order Item را تغییر دهد؛ Snapshot مالی لازم است.

### &rlm;Workflow اصلی

Catalog → Cart → Order → Admin Processing/Billing → Invoice → Payment → Completed/Closed.

### &rlm;نکات تحلیلی

&rlm;Catalog داده مرجع است اما Order Item باید Snapshot باشد؛ وگرنه تغییر قیمت یا نام محصول، تاریخچه سفارش‌های قبلی را مخدوش می‌کند.

## &rlm;07 — Helpdesk و تیکت

### &rlm;دامنه

&rlm;مدیریت درخواست‌های Client و Employee، تخصیص Agent و Workflow پاسخ‌گویی.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Ticket هم توسط Client و هم Employee قابل ایجاد است. فرم Ticket شامل Requester Type/Name، Subject، Description، File، Agent، Priority، Type، Channel و Tags است و خود Ticket Form قابل سفارشی‌سازی/Preview است. Dashboard تیکت Total، Closed، Open، Pending و Received را نشان می‌دهد. Ticket Settings شامل Ticket Agents، Ticket Types، Ticket Channels و Reply Templates است. Visibility Agent سه حالت All Tickets، Tickets in a Group و Assigned Tickets دارد. Round Robin برای Assignment مساوی و ترتیبی Agentها پشتیبانی می‌شود.

### &rlm;Actorها

&rlm;Client، Employee، Ticket Agent، Ticket Group/Manager و Admin.

### &rlm;موجودیت‌های کلیدی

&rlm;Ticket، Requester، Agent، Agent Group، Priority، Ticket Type، Channel، Tag، Attachment، Reply، Reply Template، Ticket Form Definition، Assignment Rule.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-TKT-001&lrm; Ticket باید Requester Type را بین Client/Employee متمایز کند.
- &rlm;&lrm;FR-WS-TKT-002&lrm; Ticket باید Subject، Description، Status، Priority، Type، Channel، Tags و Attachments داشته باشد.
- &rlm;&lrm;FR-WS-TKT-003&lrm; Ticket باید Agent یا Group قابل تخصیص داشته باشد.
- &rlm;&lrm;FR-WS-TKT-004&lrm; Agent Visibility باید حداقل All، Group-scoped و Assigned-only را پشتیبانی کند.
- &rlm;&lrm;FR-WS-TKT-005&lrm; Ticket Type و Channel باید Data-driven و قابل مدیریت باشند.
- &rlm;&lrm;FR-WS-TKT-006&lrm; Reply Template باید برای پاسخ سریع Agent قابل استفاده باشد.
- &rlm;&lrm;FR-WS-TKT-007&lrm; Ticket Form باید Fieldهای قابل فعال/غیرفعال و Preview داشته باشد.
- &rlm;&lrm;FR-WS-TKT-008&lrm; Round Robin باید Agentهای واجد شرایط را به‌صورت عادلانه و قابل پیش‌بینی انتخاب کند.
- &rlm;&lrm;FR-WS-TKT-009&lrm; Ticket List باید Filter و Counterهای عملیاتی بر اساس Status داشته باشد.
- &rlm;&lrm;FR-WS-TKT-010&lrm; File Attachment باید به Ticket/Reply متصل و Access-controlled باشد.
- &rlm;&lrm;FR-WS-TKT-011&lrm; تغییر Assignment و Status باید History/Audit قابل رهگیری ایجاد کند.
- &rlm;&lrm;FR-WS-TKT-012&lrm; Permissionهای Ticket باید بین Requester، Agent و Admin تفکیک شوند.

### &rlm;Workflow اصلی

&rlm;Requester Ticket ایجاد می‌کند → Type/Channel/Priority مشخص می‌شود → Agent دستی یا Round Robin Assign می‌شود → Agent Reply/Update می‌کند → Pending/Open/Closed تغییر می‌کند → History و Metrics به‌روز می‌شوند.

### &rlm;نکات مرزی

&rlm;Worksuite Ticket را Internal + External Support می‌بیند. اگر محصول ما فقط Helpdesk مشتری باشد، Employee-as-requester یک Scope مستقل است و نباید خودکار وارد MVP شود.

## &rlm;08 — همکاری، پیام و رویداد

### &rlm;دامنه

&rlm;ارتباط داخلی/مشتری، Event Coordination، Notice Board و Notification Delivery.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Messages بین Individualها و Groupها وجود دارد. Worksuite از Messaging برای Employee/Client استفاده می‌کند و در Release Notes محدودشدن بعضی تعاملات Employee/Client بر اساس رابطه Project نیز دیده می‌شود. Event دارای Daily/Weekly/Monthly View، Color، Location، Description، Start/End، Invite برای Employee/Client، Repeat و Reminder است؛ نسخه‌های جدید File/Link روی Event اضافه کرده‌اند. Notice Board دارای Heading، Date، Recipient Audience و View Tracking است و می‌تواند برای Employee، Client یا Department هدف‌گذاری شود. Notificationها از Email/SMTP، Slack، OneSignal Push و Pusher پشتیبانی می‌کنند.

### &rlm;Actorها

&rlm;Employee، Client، Admin، Department/Group و Event Organizer.

### &rlm;موجودیت‌های کلیدی

&rlm;Conversation، Message، Participant/Group، Event، Event Attendee، Recurrence Rule، Reminder، Event Attachment/Link، Notice، Notice Recipient، Notice View Receipt، Notification Preference/Delivery.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-COL-001&lrm; Messaging باید Conversation فردی و گروهی داشته باشد.
- &rlm;&lrm;FR-WS-COL-002&lrm; Client/Employee Messaging باید قابلیت اعمال Scope مانند Project Relationship را داشته باشد.
- &rlm;&lrm;FR-WS-COL-003&lrm; Message باید Participant، Timestamp و Read State داشته باشد.
- &rlm;&lrm;FR-WS-COL-004&lrm; Event باید Start/End، Location، Description، Color و Invitee داشته باشد.
- &rlm;&lrm;FR-WS-COL-005&lrm; Event باید Recurrence و Reminder قابل تنظیم داشته باشد.
- &rlm;&lrm;FR-WS-COL-006&lrm; Event باید Attachment/Link اختیاری داشته باشد.
- &rlm;&lrm;FR-WS-COL-007&lrm; Notice باید Audience را روی Employee/Client/Department هدف‌گذاری کند.
- &rlm;&lrm;FR-WS-COL-008&lrm; Notice باید View Tracking برای Recipientها داشته باشد.
- &rlm;&lrm;FR-WS-COL-009&lrm; Notification باید Event، Recipient و Channel را از Business Event جدا نگه دارد.
- &rlm;&lrm;FR-WS-COL-010&lrm; Channelهای Delivery باید Failure/Retry مستقل از Transaction اصلی داشته باشند.

### &rlm;نکات تحلیلی

&rlm;Message، Notice و Event سه الگوی متفاوت ارتباط‌اند: Conversation، Broadcast و Scheduled Coordination. یکی‌کردن آن‌ها معمولاً Permission و UX را پیچیده می‌کند.

## &rlm;09 — گزارش‌ها و تحلیل

### &rlm;دامنه

&rlm;تبدیل داده‌های عملیاتی HR، Project و Finance به گزارش قابل تصمیم‌گیری.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Worksuite در مستندات خود Report Module مستقلی با Task Report، Time Log Report و Finance Report دارد و گزارش‌های دیگری مانند Income vs Expense، Leave و Attendance در ساختار محصول دیده می‌شوند. Expense Report نیز در Releaseهای بعدی اضافه شده است. Project Progress و Time Log Aggregate نیز در صفحات عملیاتی نمایش داده می‌شوند.

### &rlm;Actorها

&rlm;Admin/Manager، HR، Finance، Project Manager و User دارای Report Permission.

### &rlm;موجودیت‌های کلیدی

&rlm;Report Definition، Filter، Metric، Aggregate، Export، Dashboard Widget.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-REP-001&lrm; Report باید از داده Source-of-Truth ماژول مربوطه ساخته شود نه داده تکراری مستقل.
- &rlm;&lrm;FR-WS-REP-002&lrm; Task Report باید وضعیت، Assignee، Project، Date Range و سایر Filterهای کاری را پشتیبانی کند.
- &rlm;&lrm;FR-WS-REP-003&lrm; Time Log Report باید Employee، Project، Task، Date Range و Duration Aggregate داشته باشد.
- &rlm;&lrm;FR-WS-REP-004&lrm; Finance Report باید Currency و Date Range را به شکل سازگار مدیریت کند.
- &rlm;&lrm;FR-WS-REP-005&lrm; Income/Expense Report باید Revenue و Expense را از تراکنش‌های مبنا تجمیع کند.
- &rlm;&lrm;FR-WS-REP-006&lrm; Leave/Attendance Report باید Employee/Department/Date Range قابل فیلتر داشته باشد.
- &rlm;&lrm;FR-WS-REP-007&lrm; Expense Report باید Category/Employee/Vendor/Status و بازه زمانی را پشتیبانی کند.
- &rlm;&lrm;FR-WS-REP-008&lrm; Report Visibility باید Role/Permission و Scope داده کاربر را رعایت کند.
- &rlm;&lrm;FR-WS-REP-009&lrm; Export باید همان Filter و Scope گزارش روی صفحه را حفظ کند.
- &rlm;&lrm;FR-WS-REP-010&lrm; Metric Definition باید قابل تست و قابل رهگیری تا رکوردهای مبنا باشد.

### &rlm;نکات تحلیلی

&rlm;گزارش‌گیری یک Domain داده‌مصرف‌کننده است. بهتر است Ruleهای اصلی مثل وضعیت Paid یا Duration در Domain مبنا تعریف شوند و Report صرفاً آن‌ها را Aggregate کند.

## &rlm;10 — تنظیمات، نقش و دسترسی

### &rlm;دامنه

&rlm;کنترل رفتار سراسری سیستم، Role/Permission، Module Visibility، Customization و Security/Localization.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Worksuite سه Role پیش‌فرض Admin، Employee و Client دارد که نقش‌های سیستمی‌اند و Roleهای سفارشی برای Permission قابل ساخت هستند. Module Settings می‌تواند Moduleها را برای Admin/Employee/Client فعال یا غیرفعال کند. Company/App/Profile Settings، Notification Settings، Currency، Payment Credentials، Finance/Ticket/Project/Attendance Settings، Custom Fields و Language Settings در بخش Application Settings وجود دارند. Dark Theme، RTL، Color Skin، Multi-language، Multi-currency و GDPR نیز جزو قابلیت‌های معرفی‌شده محصول‌اند. Storage می‌تواند Local یا AWS باشد. Dashboard Widgetهای Employee نیز از سمت Admin قابل مدیریت شده‌اند.

### &rlm;Actorها

&rlm;System Admin/Owner، Custom Role Admin و End User برای Preferenceهای شخصی.

### &rlm;موجودیت‌های کلیدی

&rlm;System Role، Custom Role، Permission، Module Setting، Company Setting، User Preference، Custom Field Definition، Language/Locale، Currency، Storage Config، Notification Setting، Dashboard Config، Security/GDPR Setting.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-SET-001&lrm; Roleهای سیستمی باید از Roleهای سفارشی تفکیک شوند.
- &rlm;&lrm;FR-WS-SET-002&lrm; Role سفارشی باید مجموعه Permissionهای Module/Action را نگه دارد.
- &rlm;&lrm;FR-WS-SET-003&lrm; Module Availability باید بتواند بر اساس Actor Type مثل Admin/Employee/Client کنترل شود.
- &rlm;&lrm;FR-WS-SET-004&lrm; Module Disable نباید داده موجود را حذف کند.
- &rlm;&lrm;FR-WS-SET-005&lrm; Custom Field باید Target Module، Type، Validation، Required/Optional و Visibility داشته باشد.
- &rlm;&lrm;FR-WS-SET-006&lrm; Notification Setting باید Channel و Event را قابل تنظیم کند.
- &rlm;&lrm;FR-WS-SET-007&lrm; Localization باید Language، RTL و Formattingهای وابسته را پشتیبانی کند.
- &rlm;&lrm;FR-WS-SET-008&lrm; Currency Setting باید از Document Currency و Base Currency تفکیک شود.
- &rlm;&lrm;FR-WS-SET-009&lrm; Theme/Color Preference نباید با Permission یا Business Setting مخلوط شود.
- &rlm;&lrm;FR-WS-SET-010&lrm; Storage Backend باید از Business Entityها abstraction داشته باشد.
- &rlm;&lrm;FR-WS-SET-011&lrm; Dashboard Widget Visibility/Layout باید قابل تنظیم باشد.
- &rlm;&lrm;FR-WS-SET-012&lrm; GDPR/Security Setting باید Consent/Privacy رفتارهای لازم را در سطح سیستم اعمال کند.
- &rlm;&lrm;FR-WS-SET-013&lrm; Settings حساس مانند Payment Credential باید Secret Storage و Access Restriction داشته باشند.

### &rlm;نکات مرزی

&rlm;Module Visibility و Permission یک چیز نیستند. Module Setting می‌گوید قابلیت برای یک Actor Class وجود دارد یا نه؛ Permission می‌گوید User مشخص داخل قابلیت فعال چه عملیاتی می‌تواند انجام دهد.

## &rlm;11 — یکپارچه‌سازی‌ها

### &rlm;دامنه

&rlm;اتصال Worksuite به Notification Provider، Payment Gateway، Map/Translation/Accounting Service و Extensionهای خارجی.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;مستندات Integrations مواردی مانند Mollie، Paystack، Custom JavaScript Widgets، Telegram Notifications، Google Maps، Square، Authorize.net، Payfast، Google Translate API و QuickBooks را پوشش می‌دهد. در معرفی محصول Slack و Pusher نیز آمده‌اند؛ OneSignal برای Push Notification در تنظیمات مستند شده است. Payment Gatewayهای متعدد بخشی از لایه Finance/Checkout هستند.

### &rlm;Actorها

&rlm;Admin/Integrator، External Provider، Finance System و Notification Recipient.

### &rlm;موجودیت‌های کلیدی

&rlm;Integration Config، Provider Credential، OAuth/Token، External Account Mapping، Payment Transaction، Notification Delivery، Sync State، Callback/Webhook، Custom Widget Config.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-INT-001&lrm; Credentialهای Integration باید امن و قابل Rotation باشند.
- &rlm;&lrm;FR-WS-INT-002&lrm; هر Integration باید Enable/Disable و Validation State مستقل داشته باشد.
- &rlm;&lrm;FR-WS-INT-003&lrm; Payment Gateway باید Flow Provider-specific را از Invoice/Order Domain جدا کند.
- &rlm;&lrm;FR-WS-INT-004&lrm; Callback/Webhook پرداخت باید Idempotent باشد و Duplicate Event را تحمل کند.
- &rlm;&lrm;FR-WS-INT-005&lrm; Notification Provider Failure نباید Business Transaction را Rollback کند.
- &rlm;&lrm;FR-WS-INT-006&lrm; Accounting Integration مانند QuickBooks باید Mapping ID و Sync State داشته باشد.
- &rlm;&lrm;FR-WS-INT-007&lrm; Translation/Maps Integration باید Failure Fallback داشته باشد.
- &rlm;&lrm;FR-WS-INT-008&lrm; Custom JavaScript Widget باید از نظر Scope/Security محدود و قابل کنترل باشد.
- &rlm;&lrm;FR-WS-INT-009&lrm; External Sync باید Error Log، Retry و Last Successful Sync داشته باشد.
- &rlm;&lrm;FR-WS-INT-010&lrm; Integration Permission و Secret Visibility باید فقط برای Adminهای مجاز باشد.

### &rlm;نکات تحلیلی

&rlm;Integration Contract باید بر رفتار دامنه مسلط نباشد. Invoice نباید Stripe/Mollie-specific شود و Notification نباید Pusher-specific طراحی شود؛ Provider Adapter مرز مناسب‌تری است.

## &rlm;12 — افزونه‌ها و ماژول‌های اختیاری

### &rlm;دامنه

&rlm;قابلیت‌هایی که در معرفی/Marketplace Worksuite به‌صورت Add-on یا Module جدا ارائه شده‌اند و نباید با Core Requirementها مخلوط شوند.

### &rlm;قابلیت‌های مشاهده‌شده

&rlm;Worksuite در مستندات/معرفی خود Moduleهای اختیاری از جمله Zoom Meeting، Payroll، SMS، REST API و Assets Management را نام می‌برد. در اکوسیستم محصول همچنین Recruit، Asset، Subdomain و HRM SaaS Module دیده می‌شوند. این قابلیت‌ها بسته به Edition/License می‌توانند خارج از Core باشند.

### &rlm;دسته‌بندی تحلیلی

&rlm;Payroll: محاسبه/مدیریت حقوق و اجزای پرداخت کارکنان.
&rlm;Recruit: چرخه استخدام، Vacancy/Candidate/Application و فرآیند جذب.
&rlm;Assets: دارایی سازمانی، تخصیص به Employee و Lifecycle دارایی.
&rlm;SMS: Notification/Communication Channel پیامکی.
&rlm;Zoom: Meeting Integration و ارتباط Event/Meeting.
&rlm;REST API: دسترسی برنامه‌ای به منابع Worksuite.
&rlm;Subdomain/HRM SaaS: قابلیت‌های Multi-tenant/SaaS-oriented و HR توسعه‌یافته.

### &rlm;نیازمندی‌های استخراج‌شده

- &rlm;&lrm;FR-WS-ADD-001&lrm; Add-on باید Dependency و Compatibility Version مشخص داشته باشد.
- &rlm;&lrm;FR-WS-ADD-002&lrm; نصب/فعال‌سازی Add-on نباید Core Data را تخریب کند.
- &rlm;&lrm;FR-WS-ADD-003&lrm; Add-on Permission باید با مدل Role/Permission اصلی یکپارچه شود.
- &rlm;&lrm;FR-WS-ADD-004&lrm; Payroll در صورت انتخاب باید یک Domain مستقل HR/Finance با Audit بالا باشد.
- &rlm;&lrm;FR-WS-ADD-005&lrm; Recruit در صورت انتخاب باید Candidate را از Employee جدا نگه دارد و Conversion/Onboarding تعریف کند.
- &rlm;&lrm;FR-WS-ADD-006&lrm; Asset در صورت انتخاب باید Asset Inventory و Assignment History داشته باشد.
- &rlm;&lrm;FR-WS-ADD-007&lrm; SMS در صورت انتخاب باید یک Notification Channel باشد، نه Logic تکراری برای هر Domain.
- &rlm;&lrm;FR-WS-ADD-008&lrm; REST API باید Authentication، Authorization، Versioning، Rate Limit و Audit داشته باشد.
- &rlm;&lrm;FR-WS-ADD-009&lrm; Multi-tenant/Subdomain capability فقط در صورت نیاز محصول SaaS باید وارد Core Architecture شود.

### &rlm;نتیجه برای تحلیل MVP

&rlm;وجود یک قابلیت در Marketplace به‌تنهایی دلیل ورود آن به MVP نیست. در مرحله انتخاب Scope باید Core، Extension و Future Capability صریحاً برچسب‌گذاری شوند.
