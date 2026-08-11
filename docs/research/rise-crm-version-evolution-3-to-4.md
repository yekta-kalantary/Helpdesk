<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# RISE CRM — Version Evolution 3.0 → 4.0

## &rlm;هدف سند

&rlm;این سند تاریخچه کامل Release Noteهای RISE را کپی نمی‌کند. هدف، استخراج روند تکامل Domainها و Ruleهای محصول از نسخه `3.0` تا `4.0` بر اساس Change Log رسمی FairSketch است؛ به‌خصوص تغییراتی که برای فهم Client/Contact، Project/Task، Permission، Collaboration، File، Ticket، Notification و Extension Model اهمیت مهندسی دارند.

&rlm;برای تصویر پایدار محصول ابتدا [Base Product Analysis](rise-crm-product-analysis.md) خوانده شود. برای وضعیت فعلی و Deltaهای روز نیز [Live Re-validation — 2026-08-11](rise-crm-revalidation-2026-08-11.md) مرجع مکمل است.

## &rlm;منبع اصلی و دامنه نسخه‌ها

- [Official RISE Change Logs](https://risedocs.fairsketch.com/doc/category/4)
- بازه تحلیل اصلی: `3.0 — 23 Nov 2021` تا `4.0 — 22 Jul 2026`

&rlm;صفحه رسمی Change Logs تمام نسخه‌های اصلی و Patchهای این بازه را فهرست می‌کند. بعضی Patchها صرفاً Bug Fix یا آماده‌سازی Environment هستند و Product Pattern جدیدی ایجاد نمی‌کنند؛ این نسخه‌ها در تحلیل فشرده ثبت می‌شوند و برایشان Feature مصنوعی استنباط نمی‌شود.

&rlm;برای `3.0.1` صفحه نسخه در فهرست رسمی وجود دارد، ولی محتوای مستقل آن در زمان این Research از منبع قابل بازیابی نبود. بنابراین هیچ Feature یا Rule از `3.0.1` حدس زده نشده است.

## &rlm;Phase 1 — 3.0 تا 3.2: تثبیت Permission، Task Configuration و Client Identity

### &rlm;Version 3.0 — Project-specific workflow و Extension Points

&rlm;نسخه 3.0 امکان حذف Statusهای خاص Task از Projectهای خاص را اضافه کرد. این تغییر نشان می‌دهد Workflow Task در RISE از همان مرحله صرفاً Global Status List نبود و Project می‌توانست روی Statusهای قابل استفاده Constraint داشته باشد.

&rlm;همزمان Permission جدید برای دسترسی Team Member به Client Groupهای مشخص و چند Plugin Hook برای Notification، Sign-in/Sign-out، Dashboard و Signup اضافه شدند. بنابراین تا 3.0 دو Pattern تثبیت شده بود: Access می‌تواند Scope داده‌ای داشته باشد و Extension Point بخشی از معماری محصول است.

### &rlm;Version 3.1 — Task Priority و Task Reference در UI

&rlm;نسخه 3.1 Task Priority Settings را اضافه کرد و امکان نمایش Task ID همراه Project و Client Title در Kanban را فراهم کرد. این Release همچنین Checklist Group، Task Import و Role Permission جدید Project را توسعه داد.

&rlm;نتیجه: Task در RISE فقط Title/Status نیست؛ Priority، ID قابل مشاهده، Checklist Configuration و Permission Context بخشی از Aggregate کاری هستند.

### &rlm;Version 3.2 — Client Contact قابل استفاده در چند Client و Internal Project

&rlm;نسخه 3.2 اجازه داد یک Client Contact به چند Client اضافه شود. این مورد مهم است چون نشان می‌دهد `Contact` از نظر مدل مفهومی لزوماً مالکیت یک‌به‌یک با Client ندارد و Person Identity می‌تواند در چند Account Context حضور داشته باشد.

&rlm;همین نسخه `Internal Project` را اضافه کرد؛ یعنی Project لزوماً همیشه Client-facing نیست. همچنین Dashboardهای متعدد، Reminder Module، Multi-language Custom Field/Menu و Server-side List برای Clients/Contacts/Tasks/Tickets اضافه یا تقویت شدند.

&rlm;در Recurring Task نیز Subtask و Checklist هنگام Clone/Recurrence منتقل می‌شدند؛ بنابراین Task hierarchy تا این مرحله یک رفتار واقعی و پایدار بود.

## &rlm;Phase 2 — 3.3 تا 3.5: رشد General Task، Smart Filter و Lifecycleهای تجاری

### &rlm;Version 3.3 — Permission granularity و own-task time logging

&rlm;نسخه 3.3 Permissionهای جدید Project و رفتار Timesheet مبتنی بر Own Tasks را اضافه/تقویت کرد. Bug Fixهای Subtask، Activity Log و Project Client UI نیز نشان می‌دهند Scope دسترسی و Context پروژه در چند Surface مختلف enforce می‌شود.

### &rlm;Versions 3.4 / 3.4.1 / 3.4.2 — Subtask UX، Project cloning و Performance

&rlm;نسخه 3.4 Subtask را در Kanban قابل فهم‌تر کرد: Completed Subtask Count و Parent Task Title نمایش داده شدند. Project Clone options توسعه یافتند و مشکل Notification برای User غیرProjectMember اصلاح شد؛ این Fix مدرک مهمی برای Project Membership به‌عنوان Boundary Notification/Access است.

&rlm;نسخه 3.4.1 Pagination برای Tasks Kanban اضافه کرد؛ نشانه‌ای از اینکه Task Board در مقیاس بالا نیازمند Server/Incremental Loading است. نسخه 3.4.2 صرفاً آماده‌سازی Environment Checker برای Release 3.5 بود و Product Delta مستقلی ندارد.

### &rlm;Version 3.5 — General/Contextual Tasks و Configurable Project Status

&rlm;نسخه 3.5 نقطه تغییر مهمی در مدل Task است. RISE `general tasks in different contexts` را اضافه کرد و در Invoice، Order، Subscription، Estimate، Proposal، Contract و Ticket تب Tasks قرار داد. همچنین Global Task List دارای Edit/Delete شد و برای General Task Eventها Notificationهای مستقل اضافه شدند.

&rlm;از این نسخه به بعد Task دیگر فقط Child مستقیم Project نیست؛ یک Work Item می‌تواند به Contextهای مختلف تجاری متصل باشد. این توسعه بعدها در نسخه 3.7 با Task بدون Context و در 4.0 با Time Log برای General Task ادامه پیدا کرد.

&rlm;همزمان Project Status سفارشی، Task Start/Deadline با Time، Smart Filter و Bookmark/Remember Filter اضافه شدند. در سطح Lifecycle اسناد نیز Restriction برای جلوگیری از Edit بعد از State خاص تقویت شد.

### &rlm;Versions 3.5.1 تا 3.5.3 — Lock State و Plugin Operations

&rlm;3.5.1 Lock State تنظیم‌پذیر برای Invoice/Estimate/Proposal/Contract را اضافه کرد. 3.5.2 امکان نصب دستی Plugin را تقویت کرد و یک Fix مهم نشان داد Project Task باید همه Project Memberهای معتبر را برای Assignment در دسترس داشته باشد. 3.5.3 بیشتر Maintenance/IMAP/Embedded Ticket Custom Field بود.

## &rlm;Phase 3 — 3.6 تا 3.8: Client Contact Permissions، File Boundaries و Task Mobility

### &rlm;Version 3.6 — Contact-level permission model

&rlm;3.6 یکی از مهم‌ترین Releaseها برای Client Portal است. قابلیت Permission سفارشی برای Contactهای مختلف یک Client اضافه شد؛ Default Permission برای Non-primary Contact تعریف شد و Primary Contact امکان مدیریت Permission Contactهای غیرPrimary را پیدا کرد.

&rlm;این Release همچنین File Manager Module، Folder در Client Portal/Client Detail، Client Contact Import و Permission برای مخفی کردن Project Files/Comments از Team Memberها را اضافه کرد. بنابراین Access در RISE فقط Module-level نیست؛ Contact-level، Project-content-level و Folder/File-level نیز می‌تواند باشد.

&rlm;در Gantt نیز Dependency و Child Task relationship بهبود یافت، که نشان می‌دهد Hierarchy و Dependency در Work Planning از UI صرف فراتر رفته‌اند.

### &rlm;Version 3.7 — Task بدون Context و Ticket Automation

&rlm;3.7 صریحاً امکان ایجاد Task بدون Context مرتبط را اضافه کرد. این Release Ticket Automation، PWA Mobile، Project File Folder، Smart Filter در Domainهای بیشتر و ارتباط Project/Ticket/Proposal را گسترش داد.

&rlm;همچنین Event Sharing بین Team Member و Client Contact اضافه شد. در نتیجه Collaboration مشتری فقط Comment روی Project نبود و Surfaceهای Calendar/Event نیز Client-aware شدند.

### &rlm;Version 3.7.1 — Security hardening

&rlm;3.7.1 عمدتاً Maintenance بود، اما بهبودهای مشخص SQL Injection/XSS نشان می‌دهد Security Validation در این دوره به‌عنوان Concern مستقل تقویت شده است.

### &rlm;Version 3.8 — Client Contact Mention و non-project task visibility

&rlm;3.8 امکان Mention کردن Client Contact در Task Comment را اضافه کرد. Checklist Sortability و Task List Status setting نیز توسعه یافتند. Fix مربوط به نمایش Taskهای non-project در Event Calendar نشان می‌دهد General Task در چند Surface سیستم به رسمیت شناخته شده بود.

### &rlm;Version 3.8.1 — Maintenance

&rlm;3.8.1 عمدتاً Framework Upgrade و List Fix بود و Product Pattern جدیدی ایجاد نکرد.

### &rlm;Version 3.8.2 — Move Task between Projects

&rlm;3.8.2 امکان انتقال Task از یک Project به Project دیگر را اضافه کرد. این قابلیت از نظر مدل داده مهم است: RISE Project Reference یک ویژگی قابل تغییر Task شده، نه یک Immutable identity rule.

&rlm;همزمان Setting برای محدودکردن Global Task Creation به Project-related Task اضافه شد. این نشان می‌دهد RISE بعد از گسترش General Task، Configuration لازم برای محدودکردن همان انعطاف را نیز فراهم کرده است.

## &rlm;Phase 4 — 3.9: Client Portal، Permission و Operational UX بالغ‌تر

### &rlm;Version 3.9 — Financial/Portal expansion و Permission granularity

&rlm;3.9 Client Wallet، e-Invoice، Permissionهای بیشتر Invoice، Permission برای مخفی‌کردن Userها در Team Member dropdown و Client Portal Dashboard configuration را اضافه کرد. در Task Domain، Bulk Delete، Recurring Task Custom Field Copy و چند Fix Permission/Notification ثبت شده‌اند.

&rlm;از منظر Security نیز CSP و CORS برای Lead Form اضافه و XSS Filterها تقویت شدند.

### &rlm;Version 3.9.1 — Task collaborator و Client management authority

&rlm;3.9.1 در Gantt هنگام Filter بر اساس Assignee، Taskهای Collaborator را نیز نمایش داد. Team Member دارای Client Management Permission توانست Client Login Info را به‌روزرسانی کند. Task Inline Update و Mobile Project Task List نیز بهبود یافتند.

### &rlm;Version 3.9.2 — Maintenance-only patch

&rlm;3.9.2 فقط یک Fix عمومی ثبت کرده و Product Delta قابل اتکایی برای Domain Analysis ندارد.

### &rlm;Version 3.9.3 — Multiple Client Managers و Push/PWA

&rlm;3.9.3 امکان چند Manager برای Client و Lead را اضافه کرد. این تغییر مدل Ownership/Responsibility را از Single Owner به Multi-manager توسعه می‌دهد.

&rlm;همزمان Push Notification برای PWA، Pusher Beams، Client Detail Widget/Tab configuration و Sharing Event از Client Portal اضافه شدند. Task ID نیز در Ticket Detail کنار Title نمایش داده شد و Task Checklist logic بهبود یافت.

### &rlm;Version 3.9.4 — Contract permission و scoped file sharing

&rlm;3.9.4 Role Permission مستقل برای Contract و Share کردن File Manager Folder با همه Clientها یا Client Groupهای مشخص را اضافه کرد. Announcement نیز می‌تواند برای Team یا Team Memberهای خاص Share شود. Gmail API برای IMAP/SMTP و Mobile Ticket UI اضافه شد.

### &rlm;Version 3.9.5 — Compact view، Subtask ordering و Private Task evidence

&rlm;3.9.5 Compact View را در چند Domain مهم اضافه کرد و Subtask Sort در Task Detail را فراهم کرد. Client Ticket می‌تواند به Existing Client Contact Link شود.

&rlm;یک Fix مهم می‌گوید Private Task نباید برای سایر Team Memberها نمایش داده شود؛ بنابراین تا این نسخه Private Task Visibility یک قابلیت واقعی محصول بوده است. همچنین هنگام Lead→Client Conversion، Taskهای مرتبط نیز به Client منتقل می‌شوند؛ این رفتار نشان می‌دهد Task Relationship می‌تواند همراه Entity Conversion migrate شود.

&rlm;Multi-level Subtask در Gantt نیز در Fixها صریحاً دیده می‌شود.

### &rlm;Version 3.9.6 — Global Task as established subsystem

&rlm;3.9.6 عمدتاً Bug Fix است، اما Fixهای `create global tasks` و Populate شدن Project-related fields در Global Task Modal تأیید می‌کنند Global Task تا پایان 3.x یک Subsystem تثبیت‌شده بوده است.

## &rlm;Phase 5 — 4.0: AI subsystem و Work Log خارج از Project Task

### &rlm;Version 4.0 — AI becomes a first-class subsystem

&rlm;RISE 4.0 AI Assistant، ChatGPT/Gemini Integration، AI Agent با Custom Training، AI Chatbox، Quick Assistant و AI-powered Ticket Reply را اضافه کرد. این مجموعه نشان می‌دهد AI از یک Provider Integration ساده فراتر رفته و دارای Agent، Training Context و چند UI Surface شده است.

### &rlm;General Task / Ticket Time Logs

&rlm;4.0 دو Module جدید برای Time Log روی General Task و Ticket اضافه کرد. این تکامل مسیر 3.5→3.7→3.8 را کامل می‌کند: Work Item ابتدا از Project جدا شد، سپس در Contextهای مختلف حضور یافت، و نهایتاً Time Tracking نیز از Project Task فراتر رفت.

### &rlm;Client Contact و Human-readable IDs

&rlm;4.0 امکان انتخاب Existing Contact هنگام Add Contact به Client را اضافه کرد. برای Estimate/Proposal/Contract/Order/Subscription/Ticket نیز Display ID قابل تنظیم اضافه شد. این موارد Reuse هویت و جدایی Internal PK از Human-facing Reference را تقویت می‌کنند.

### &rlm;Collaboration / Support / File UX

&rlm;Task Comment Owner می‌تواند Comment خودش را Edit کند؛ Ticket Reply Confirmation، Ticket Bulk Delete، Knowledge Base login-only access، File Thumbnail، File Manager Keyboard Shortcut و بهبود IMAP Thread نیز اضافه شدند.

## &rlm;تحلیل Cross-version — Project و Task

&rlm;مسیر تکامل Task در RISE را می‌توان این‌گونه خلاصه کرد:

`Project-scoped Task` → `Configurable Status/Priority` → `Subtask/Checklist/Recurring` → `General Task in business contexts` → `Context-free Task` → `Client Contact mention` → `Move between Projects` → `Private Task / Multi-level Subtask` → `General Task Time Log`.

&rlm;این مسیر نشان می‌دهد Full RISE عمداً Task را به یک Generic Work Item تبدیل کرده است. برای محصول MVP ما این یک Reference Pattern است، نه الزام؛ PRD ما همچنان Task را Project-bound نگه می‌دارد.

## &rlm;تحلیل Cross-version — Client و Contact

&rlm;در 3.2 Contact می‌تواند با چند Client رابطه داشته باشد. در 3.6 Permission Contact-level بالغ شد. در 3.8 Contact وارد Task Comment Mention شد. در 3.9.x Client Management و Client Manager گسترده‌تر شدند و در 4.0 Existing Contact reuse هنگام Add to Client اضافه شد.

&rlm;در نتیجه RISE بین Account (`Client`) و Person/Login Context (`Contact`) مرز مفهومی قوی دارد. این مرز یکی از مهم‌ترین Patternهای قابل انتقال به طراحی‌های آینده است، حتی اگر Contact Directory در MVP فعلی خارج از Scope بماند.

## &rlm;تحلیل Cross-version — Permission و Visibility

&rlm;Permission در این بازه از Role/Project/Client Group به سطوح ریزتر حرکت کرده است: Project permission، Client-group scope، Contact permission، Project file/comment access، Private Task visibility، Contract permission، Invoice permission، Proposal own-only access و Folder sharing با Client Group.

&rlm;Engineering Inference: در محصولی با این سطح از گسترش، Authorization باید Policy/Scope-driven باشد و نباید فقط با مخفی‌کردن UI پیاده‌سازی شود.

## &rlm;تحلیل Cross-version — Collaboration

&rlm;Collaboration از Task Comment/File به Mention، Announcement targeting، Event sharing، Chat/Pusher/Push Notification و Ticket Context توسعه یافته است. این روند Surfaceهای عمومی و Contextual را از هم متمایز می‌کند.

&rlm;برای MVP ما همچنان Contextual Task Comment/File ارزش اصلی را دارد و Chat عمومی/Post/Announcement وارد Scope نمی‌شود.

## &rlm;تحلیل Cross-version — File و Knowledge

&rlm;Project File از Folder ساده به File Manager مرکزی با Client Folder، Permission، Group Sharing، Preview، Thumbnail و Shortcut توسعه یافته است. Knowledge Base نیز Label، Related Article، Login-only access و UX پیشرفته‌تر گرفته است.

&rlm;این تکامل نشان می‌دهد File Manager و Knowledge Base دو Subsystem مستقل‌اند و نباید صرف Attachment ساده Task را معادل آن‌ها دانست.

## &rlm;تحلیل Cross-version — Platform / Security / Extension

&rlm;در کل بازه، CodeIgniter مرتب Upgrade شده و Security Hardening شامل XSS، SQL Injection، CSP و CORS دیده می‌شود. Plugin Hookها از 3.0 و 3.1 گسترش پیدا کردند و Installation/Manual Plugin flow در 3.5.x بهبود یافت.

&rlm;در 4.0 AI نیز به Subsystem مستقل تبدیل شد. این تاریخچه مرز `Core Domain` در برابر `Extension/Integration/AI` را تقویت می‌کند.

## &rlm;Requirement Addendum — Evolution-derived

&rlm;این Requirementها برای Research هستند و به‌صورت خودکار Requirement MVP محسوب نمی‌شوند.

- &rlm;&lrm;EV-RISE-001&lrm; اگر Task Status بر اساس Project محدود شود، Valid Status باید در Context Project validate شود و صرفاً UI Filter نباشد.
- &rlm;&lrm;EV-RISE-002&lrm; اگر Contact بتواند در چند Client حضور داشته باشد، Person Identity و Client Membership/Relationship باید از هم جدا مدل شوند.
- &rlm;&lrm;EV-RISE-003&lrm; اگر General Task پشتیبانی شود، Task Context باید polymorphic/optional طراحی شود و Project اجباری نباشد.
- &rlm;&lrm;EV-RISE-004&lrm; اگر Task بین Projectها Move شود، Authorization، Assignee validity، Activity History و dependent context باید دوباره validate شوند.
- &rlm;&lrm;EV-RISE-005&lrm; اگر Private Task وجود داشته باشد، Visibility باید مستقل از Assignee و Project Membership قابل enforce باشد.
- &rlm;&lrm;EV-RISE-006&lrm; Multi-level Subtask نیازمند Parent relation و cycle prevention صریح است.
- &rlm;&lrm;EV-RISE-007&lrm; Contact-level Permission باید از Client Account Permission و Project Membership تفکیک شود.
- &rlm;&lrm;EV-RISE-008&lrm; Entity conversion مانند Lead→Client باید تکلیف Task/Activity relationshipهای وابسته را صریحاً تعیین کند.
- &rlm;&lrm;EV-RISE-009&lrm; Human-readable Display ID باید در صورت استفاده از Internal Primary Key جدا باشد.
- &rlm;&lrm;EV-RISE-010&lrm; Plugin/AI/Integration capability باید از Core business lifecycle جدا نگه داشته شود.

## &rlm;اثر روی MVP پروژه ما

&rlm;این History دلیل اضافه‌کردن قابلیت‌های RISE به MVP نیست. در واقع Evolution محصول نشان می‌دهد چرا باید Scope نسخه اول محدود بماند.

&rlm;Patternهای مفید برای MVP فعلی:

- Client Account و Login User/Person یک مفهوم نیستند.
- Project Membership یک Boundary دسترسی قابل اتکاست.
- Assignment و Visibility نباید یکی فرض شوند.
- Task Reference انسانی از Internal PK جدا باشد.
- تغییرات مهم Assignment/Status/Relationship باید Audit شوند.
- File/Comment باید در Context Task امن بمانند.

&rlm;قابلیت‌های زیر با وجود حضور در RISE همچنان خارج از MVP هستند: General/Context-free Task، Move Task between Projects، Private Task، Subtask/Dependency، Time Tracking، Kanban/Gantt، Ticket Automation، File Manager، Knowledge Base، Chat عمومی، Custom Permission Builder، AI و Plugin System.

## &rlm;Source limitations

&rlm;این سند بر Release Note رسمی تکیه می‌کند. Change Log وجود Versionها را کامل نشان می‌دهد، اما برای بعضی Patchهای کوچک صفحه مستقل در زمان Research قابل Fetch نبود. در این موارد فقط وجود Release ثبت شده و هیچ Feature از آن حدس زده نشده است.

&rlm;Bug Fix تنها زمانی به‌عنوان evidence یک capability استفاده شده که متن Fix وجود همان capability را صریحاً نشان می‌دهد؛ مثال: Private Task visibility، Global Task creation و Multi-level Subtask in Gantt.
