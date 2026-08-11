<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# RISE CRM — Live Re-validation — 2026-08-11

## &rlm;هدف

&rlm;این سند Delta بازبینی مجدد RISE نسبت به [Base Product Analysis](rise-crm-product-analysis.md) است. Base Analysis همچنان مرجع Domain Map است؛ این فایل فقط چیزهایی را ثبت می‌کند که در منابع زنده فعلی تأیید، شفاف‌تر یا جدید شده‌اند.

&rlm;تاریخچه تحلیلی نسخه‌های `3.0 → 4.0` به‌صورت جداگانه در [RISE Version Evolution 3.0 → 4.0](rise-crm-version-evolution-3-to-4.md) نگهداری می‌شود تا این سند Current-state باقی بماند و به Changelog dump تبدیل نشود.

## &rlm;منابع بازبینی‌شده

- [RISE — CodeCanyon item](https://codecanyon.net/item/rise-ultimate-project-manager/15455641)
- [RISE Docs](https://risedocs.fairsketch.com/doc)
- [Official RISE Change Logs](https://risedocs.fairsketch.com/doc/category/4)
- [RISE Version Evolution 3.0 → 4.0](rise-crm-version-evolution-3-to-4.md)
- [RISE 4.0 — 22 July 2026 changelog](https://risedocs.fairsketch.com/doc/view/181-version-4-0-22-july-2026)
- [Assign project tasks to client contacts](https://risedocs.fairsketch.com/doc/view/154-assign-project-tasks-to-the-client-contacts)
- [Install plugins](https://risedocs.fairsketch.com/doc/view/133-install-plugins)
- [Plugin introduction](https://risedocs.fairsketch.com/doc/view/48)

## &rlm;وضعیت فعلی محصول

&rlm;صفحه رسمی CodeCanyon در زمان این بازبینی، `Last Update = 23 July 2026` را نشان می‌دهد. Changelog رسمی FairSketch آخرین Release مستندشده را `RISE 4.0 — 22 July 2026` اعلام می‌کند.

&rlm;در سطح فنی، صفحه محصول CodeIgniter 4 را برای Backend و Bootstrap 5 را برای Frontend اعلام می‌کند؛ Metadata محصول نیز PHP 8.x و MySQL 8.x را ذکر می‌کند. Changelog نسخه 4.0 ارتقا به CodeIgniter `4.7.3` را ثبت کرده است.

## &rlm;تأیید مجدد نقشه قابلیت‌های Core

### &rlm;Client و Client Portal

&rlm;RISE همچنان Client را مرکز بسیاری از Domainها قرار می‌دهد: Invoice، Payment، Support، Contract، Project و File حول Client سازمان‌دهی می‌شوند. Client Portal بخشی رسمی از محصول است و برای Collaboration با مشتری استفاده می‌شود.

&rlm;تمایز `Client` و `Client Contact` همچنان یک Pattern مهم است. Contact شخصی است که از طرف Client وارد Portal می‌شود و Permission او می‌تواند مستقل از خود Client Account کنترل شود.

&rlm;Change Logهای 3.x این مرز را قوی‌تر می‌کنند: از نسخه 3.2 یک Contact می‌تواند به چند Client اضافه شود؛ در 3.6 Permissionهای Contact-level توسعه پیدا کردند؛ در 3.8 Client Contact وارد Mentionهای Task شد و در 4.0 امکان Reuse کردن Existing Contact هنگام افزودن Contact به Client اضافه شد. بنابراین `Person/Contact identity` و `Client account relationship` در RISE مفاهیم جدا هستند.

### &rlm;Project و Task

&rlm;صفحه رسمی محصول دوباره این قابلیت‌ها را تأیید می‌کند:

- Task creation و assignment برای Team Memberها
- Collaborator روی Task
- Kanban task flow
- Milestone
- Pinned Comment
- Checklist
- Auto-calculated Project Progress
- Project/Task Change Log
- Time tracking
- GitHub/Bitbucket commit log در Task Activity

&rlm;مستند رسمی Client Contact Assignment نیز صریح است: برای Assign کردن Project Task به Client Contact، ابتدا `Client Can View Tasks` باید در Client Portal Permission فعال شود؛ سپس Contact باید در Project اضافه شود. پس Assignment مشتری یک قابلیت Permission-gated و Project-scoped است، نه یک Assign آزاد روی همه Contactها.

&rlm;تاریخچه 3.0→4.0 نشان می‌دهد Task در Full RISE به‌مرور از Project-only فراتر رفته است: Project-specific Status در 3.0، Priority/Reference در 3.1، General Task in different contexts در 3.5، Context-free Task در 3.7، Client Contact Mention در 3.8، Move Task between Projects در 3.8.2، Private/Multi-level Task evidence در 3.9.5 و Time Log روی General Task در 4.0. این Evolution برای فهم Full Product مهم است ولی الزام MVP ما نیست.

### &rlm;Communication و Collaboration

&rlm;RISE دو نوع Collaboration را همزمان نگه می‌دارد:

1. Contextual collaboration در Project/Task مثل Comment، File، Checklist و Activity
2. General communication مثل Chat، Announcement و Internal Timeline

&rlm;Chat بین Team Member و Client وجود دارد و Permission می‌تواند مشخص کند چه کسی اجازه Chat با Client یا Team را دارد. Pusher برای Real-time، و Email/In-app/Slack/Push برای Notification در اکوسیستم محصول دیده می‌شوند.

&rlm;Change Logها این تفکیک را تقویت می‌کنند: Client Contact Mention در Task Comment، Event Sharing با Client Contact، targeted Announcement، PWA Push و Pusher Beams در نسخه‌های مختلف اضافه شده‌اند؛ بنابراین Collaboration Contextual و General Communication دو Surface متفاوت‌اند.

### &rlm;Automation

&rlm;Recurring Task، Recurring Invoice، Recurring Expense و Recurring Event همچنان بخشی از Automation هستند. Ticket automation نیز می‌تواند از Email ورودی Ticket بسازد، Auto Reply ارسال کند و بر اساس محتوا Label/Group/Assignee را تعیین کند.

### &rlm;Extensibility

&rlm;Plugin System یک Extension Mechanism رسمی است، نه صرفاً یک Marketplace بیرونی. RISE برای Pluginها Installation/Uninstallation/Activation/Deactivation/Update hook و Data Insert/Update/Delete hook مستند کرده است. Change Logهای 3.0 و 3.1 نیز گسترش Hookها و Multiple-instance support را ثبت کرده‌اند و 3.5.2 نصب دستی Plugin را تقویت کرده است. این History Base Analysis درباره جداسازی Core از Extension Point را تأیید می‌کند.

## &rlm;Cross-version findings مهم برای طراحی

### &rlm;Task Context یک تصمیم معماری است

&rlm;RISE نشان می‌دهد سه مدل متفاوت ممکن‌اند: Project-bound Task، Task مرتبط با Context تجاری دیگر، و Task بدون Context. این‌ها یکسان نیستند و انتخاب هرکدام روی Data Model، Authorization، Search، Notification و Time Log اثر دارد.

&rlm;Engineering Inference: اگر محصول ما Task را Project-bound نگه می‌دارد، این باید یک Constraint آگاهانه و تست‌شده باشد؛ نه یک محدودیت تصادفی Schema.

### &rlm;Assignment با Visibility یکی نیست

&rlm;Project Membership، Client Contact Permission، Private Task و Collaborator در تاریخچه RISE مسیرهای جداگانه‌ای برای Access/Responsibility ساخته‌اند. بنابراین Assignee تنها مسئول اقدام است و نباید به‌تنهایی Visibility را تعیین کند.

### &rlm;Project Relation در Full RISE mutable شده است

&rlm;3.8.2 Move Task between Projects را اضافه کرد. Engineering Inference: اگر چنین قابلیتی وجود داشته باشد، Membership، Assignee validity، Notification recipients، Files/Comments context و Activity باید هنگام Move دوباره validate شوند. MVP ما عمداً Project Task را immutable نگه می‌دارد.

### &rlm;Entity Conversion باید روابط را تعیین تکلیف کند

&rlm;3.9.5 Fix صریحی برای انتقال Taskهای Lead هنگام Lead→Client conversion دارد. این موضوع نشان می‌دهد Conversion فقط Copy فیلدهای Entity نیست و روابط وابسته مثل Task/Activity باید Rule مشخص داشته باشند.

## &rlm;Deltaهای مهم RISE 4.0

### &rlm;AI

&rlm;نسخه 4.0 این قابلیت‌ها را اضافه کرده است:

- AI Assistant
- ChatGPT integration
- Gemini integration
- AI Agent با Custom Training
- AI Chatbox
- AI Quick Assistant در Context Menu
- AI-powered Ticket Reply بر اساس Agent سفارشی

&rlm;نتیجه تحلیلی: AI در RISE دیگر فقط یک Integration کوچک نیست و یک Subsystem با Provider، Agent، Training Context و Surfaceهای متعدد UI شده است. این قابلیت برای پروژه ما همچنان `Post-MVP` است مگر PRD صریحاً تغییر کند.

### &rlm;Task / Time Log

&rlm;نسخه 4.0 دو Module جدید برای ثبت Time Log روی `general tasks` و `tickets` اضافه کرده است. این نکته مهم است چون نشان می‌دهد RISE Time Tracking را فقط وابسته به Project Task نمی‌بیند و Work Context می‌تواند غیرProject نیز باشد.

&rlm;همچنین در 4.0 امکان Edit کردن Comment شخصی روی Task و بهبود Task Kanban ثبت شده است.

### &rlm;Ticket / Support

&rlm;علاوه بر AI Ticket Reply، موارد زیر در 4.0 دیده می‌شوند:

- Confirmation قبل از ارسال Ticket Reply از سمت Admin
- حذف گروهی Ticket
- امکان اصلاح Email برای Ticket ساخته‌شده با Email ناشناس
- بهبود IMAP Ticket Thread
- Knowledge Base محدود به Clientهای Login شده

### &rlm;Client / Commercial

&rlm;نسخه 4.0 امکان انتخاب Contact موجود هنگام افزودن Contact به Client را اضافه کرده و برای Estimate/Proposal/Contract/Order/Subscription/Ticket امکان Display ID قابل تنظیم ثبت کرده است. این موارد نشان می‌دهند Reference ID انسانی و Reuse موجودیت Contact در محصول جدی گرفته شده‌اند.

### &rlm;Files / Dashboard / UX

&rlm;نسخه 4.0 Thumbnail Preview برای File/Folder، Keyboard Shortcut برای File Manager، Sticky Gantt Header و Widget قراردادهای نزدیک انقضا را اضافه/بهبود داده است.

## &rlm;Requirement Addendum

&rlm;این Requirementها به Base Analysis اضافه نمی‌شوند تا IDهای پایه پایدار بمانند؛ هنگام طراحی Feature مرتبط باید همراه Base Requirementها خوانده شوند.

- &rlm;&lrm;RV-RISE-001&lrm; Client Contact Task Assignment باید هم Client Portal Permission و هم Project-level Contact Membership را validate کند.
- &rlm;&lrm;RV-RISE-002&lrm; Work Log model در صورت پشتیبانی از General Task/Ticket نباید Project Reference را برای همه Contextها اجباری کند.
- &rlm;&lrm;RV-RISE-003&lrm; AI Provider، AI Agent و Training Context باید از Business Entity اصلی جدا مدل شوند.
- &rlm;&lrm;RV-RISE-004&lrm; AI-generated Ticket Reply باید Human Review/Send Action قابل کنترل داشته باشد و نباید صرف وجود AI باعث Auto-send شود.
- &rlm;&lrm;RV-RISE-005&lrm; Human-readable Display ID باید از Internal Primary Key جدا باشد اگر محصول چنین Referenceای را ارائه می‌کند.
- &rlm;&lrm;RV-RISE-006&lrm; Task Comment Edit باید Author/Permission و Audit behavior مشخص داشته باشد.
- &rlm;&lrm;RV-RISE-007&lrm; Plugin/Extension باید از Hookهای پایدار استفاده کند و Core modification پیش‌فرض مسیر توسعه نباشد.
- &rlm;&lrm;RV-RISE-008&lrm; اگر Contact بتواند با چند Client رابطه داشته باشد، Contact Identity و Client Membership/Relationship باید از هم جدا باشند.
- &rlm;&lrm;RV-RISE-009&lrm; اگر Task بین Projectها Move شود، Access، Assignee validity، dependent context و Activity History باید دوباره validate شوند.
- &rlm;&lrm;RV-RISE-010&lrm; اگر General/Context-free Task پشتیبانی شود، Task Context نباید به‌صورت اجباری Project فرض شود.
- &rlm;&lrm;RV-RISE-011&lrm; Private Task Visibility در صورت وجود باید Rule مستقل از Assignee و Project Membership داشته باشد.
- &rlm;&lrm;RV-RISE-012&lrm; Entity Conversion مانند Lead→Client باید Migration/Preservation روابط Task و Activity را صریحاً تعریف کند.

## &rlm;اثر روی MVP پروژه ما

&rlm;بازبینی کامل 3.0→4.0 Scope فعلی MVP را بزرگ‌تر نمی‌کند. برعکس، Evolution RISE نشان می‌دهد Full Product چگونه با General Task، Private Task، Subtask، Time Tracking، Ticket، File Manager و AI به‌مرور پیچیده شده است.

&rlm;Patternهایی که برای MVP ما همچنان ارزش انتقال دارند:

- Client Account از Login User/Contact جدا باشد.
- Project Membership مرز اصلی Visibility باشد.
- Customer/Client Contact Assignment فقط داخل Project و با Permission معتبر انجام شود.
- Assignment مسئولیت است و Visibility نیست.
- Task Collaboration در Context همان Task باقی بماند.
- Activity/Change History برای تغییرات مهم نگه داشته شود.
- Human-facing Task Reference از Internal PK مستقل باشد.
- Core از Integration/Plugin/AI جدا بماند.

&rlm;MVP ما عمداً برخلاف Full RISE این Constraintها را حفظ می‌کند: Task همیشه متعلق به یک Project است، Project Task قابل انتقال به Project دیگر نیست، Per-task Private Visibility نداریم و Contact Directory مستقل نداریم.

&rlm;قابلیت‌هایی مانند AI، Ticket Automation، General/Context-free Task، Move Task between Projects، Private Task، Subtask/Dependency، General Task Time Log، Finance، HR، Store، Subscription، Chat عمومی، File Manager و Plugin Marketplace از این Research به‌صورت خودکار وارد MVP نمی‌شوند.

## &rlm;Source confidence

&rlm;موارد نسخه‌ای این سند از Change Logs رسمی FairSketch و صفحات Release رسمی استخراج شده‌اند. قابلیت‌های عمومی از CodeCanyon رسمی و RISE Docs تأیید شده‌اند. برای بعضی Patchهای کوچک صفحه مستقل در زمان Research قابل Fetch نبود؛ در آن موارد Feature حدس زده نشده است. هر جمله‌ای که درباره «اثر معماری» یا «اثر روی MVP» صحبت می‌کند Engineering Inference است، نه ادعای مستقیم درباره پیاده‌سازی داخلی RISE.
