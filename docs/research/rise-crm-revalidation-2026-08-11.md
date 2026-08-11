<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# RISE CRM — Live Re-validation — 2026-08-11

## &rlm;هدف

&rlm;این سند Delta بازبینی مجدد RISE نسبت به [Base Product Analysis](rise-crm-product-analysis.md) است. Base Analysis همچنان مرجع Domain Map است؛ این فایل فقط چیزهایی را ثبت می‌کند که در منابع زنده فعلی تأیید، شفاف‌تر یا جدید شده‌اند.

## &rlm;منابع بازبینی‌شده

- [RISE — CodeCanyon item](https://codecanyon.net/item/rise-ultimate-project-manager/15455641)
- [RISE Docs](https://risedocs.fairsketch.com/doc)
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

### &rlm;Communication و Collaboration

&rlm;RISE دو نوع Collaboration را همزمان نگه می‌دارد:

1. Contextual collaboration در Project/Task مثل Comment، File، Checklist و Activity
2. General communication مثل Chat، Announcement و Internal Timeline

&rlm;Chat بین Team Member و Client وجود دارد و Permission می‌تواند مشخص کند چه کسی اجازه Chat با Client یا Team را دارد. Pusher برای Real-time، و Email/In-app/Slack/Push برای Notification در اکوسیستم محصول دیده می‌شوند.

### &rlm;Automation

&rlm;Recurring Task، Recurring Invoice، Recurring Expense و Recurring Event همچنان بخشی از Automation هستند. Ticket automation نیز می‌تواند از Email ورودی Ticket بسازد، Auto Reply ارسال کند و بر اساس محتوا Label/Group/Assignee را تعیین کند.

### &rlm;Extensibility

&rlm;Plugin System یک Extension Mechanism رسمی است، نه صرفاً یک Marketplace بیرونی. RISE برای Pluginها Installation/Uninstallation/Activation/Deactivation/Update hook و Data Insert/Update/Delete hook مستند کرده است. این موضوع Base Analysis درباره جداسازی Core از Extension Point را تأیید می‌کند.

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

## &rlm;اثر روی MVP پروژه ما

&rlm;بازبینی جدید Scope فعلی MVP را بزرگ‌تر نمی‌کند. برعکس، RISE 4.0 نشان می‌دهد Full Product تا چه حد از MVP ما بزرگ‌تر است.

&rlm;Patternهایی که برای MVP ما همچنان ارزش انتقال دارند:

- Client Account از Login User/Contact جدا باشد.
- Project Membership مرز اصلی Visibility باشد.
- Customer/Client Contact Assignment فقط داخل Project و با Permission معتبر انجام شود.
- Task Collaboration در Context همان Task باقی بماند.
- Activity/Change History برای تغییرات مهم نگه داشته شود.
- Core از Integration/Plugin/AI جدا بماند.

&rlm;قابلیت‌هایی مانند AI، Ticket Automation، General Task Time Log، Finance، HR، Store، Subscription، Chat عمومی و Plugin Marketplace از این Research به‌صورت خودکار وارد MVP نمی‌شوند.

## &rlm;Source confidence

&rlm;موارد نسخه‌ای این سند از Changelog رسمی FairSketch استخراج شده‌اند. قابلیت‌های عمومی از CodeCanyon رسمی و RISE Docs تأیید شده‌اند. هر جمله‌ای که درباره «اثر معماری» یا «اثر روی MVP» صحبت می‌کند Engineering Inference است، نه ادعای مستقیم درباره پیاده‌سازی داخلی RISE.
