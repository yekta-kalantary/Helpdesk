<!--
Canonical documentation source: Git.
Encoding: UTF-8. This file intentionally contains HTML direction entities (`&rlm;` / `&lrm;`) for mixed Persian/English rendering.
Do not strip direction marks automatically.
-->

# Worksuite — Live Re-validation — 2026-08-11

## &rlm;هدف

&rlm;این سند Delta بازبینی مجدد Worksuite نسبت به [Base Product Analysis](worksuite-product-analysis.md) است. Base Analysis نقشه کامل Domainها را نگه می‌دارد؛ این فایل وضعیت فعلی CodeCanyon، Documentation و Version Log رسمی را دوباره اعتبارسنجی می‌کند.

## &rlm;منابع بازبینی‌شده

- [Worksuite — CodeCanyon item](https://codecanyon.net/item/worksuite-project-management-system/20052522)
- [Worksuite — CodeCanyon support page](https://codecanyon.net/item/worksuite-project-management-system/20052522/support)
- [New Worksuite Documentation — Froiden/Freshdesk](https://froiden.freshdesk.com/support/solutions/43000370147)
- [Worksuite Version History & Release Notes](https://envato.froid.works/version-log/worksuite-new)

## &rlm;وضعیت فعلی محصول

&rlm;صفحه CodeCanyon در زمان این بازبینی `Last Update = 6 August 2026` را نشان می‌دهد و محصول را با Laravel 12+ معرفی می‌کند. Metadata رسمی CodeCanyon نیز Laravel و PHP 8.x را ثبت کرده است.

&rlm;Version Log رسمی لینک‌شده از CodeCanyon در زمان بازبینی، `v6.0.13 — 17 July 2026` را با برچسب Current / latest stable release نمایش می‌دهد.

> &rlm;نکته منبع: تاریخ `Last Update` در CodeCanyon از تاریخ Latest Stable Version در Version Log جدیدتر است. بنابراین این سند آن دو را یکی فرض نمی‌کند. ممکن است Item Package/Metadata بعداً تغییر کرده باشد بدون اینکه Release Log نسخه جدیدتری ثبت کرده باشد.

## &rlm;تأیید مجدد Core Product

### &rlm;Actor Model

&rlm;Demo رسمی CodeCanyon سه Login مستقل Admin، Employee و Client نشان می‌دهد. این موضوع Base Analysis را تقویت می‌کند که Employee و Client دو Actor سطح‌اول و متفاوت‌اند و Role/Permission باید روی این Actor Model سوار شود.

### &rlm;Module Map

&rlm;New Worksuite Documentation همچنان Domainهای جدا برای این موارد دارد:

- Customers
- HR
- Work
- Finance
- Products
- Orders
- Tickets
- Events
- Messages
- Notice Board
- Reports
- Application Settings
- Integrations
- Release Notes

&rlm;این Module Map نشان می‌دهد Worksuite یک Project Manager ساده نیست؛ یک Business Management Suite چنددامنه‌ای است.

### &rlm;Project و Task

&rlm;CodeCanyon فعلی این قابلیت‌ها را در Core marketing list صریحاً نشان می‌دهد:

- Client Project Tracking
- Project Progress Tracking
- Kanban Taskboard
- Gantt Chart
- Comprehensive Project Management

&rlm;Version History نیز چند قابلیت/اصلاح مهم در Work Domain را ثبت کرده است:

- Task Dependency اضافه شده است.
- Project Tags اضافه شده‌اند.
- Gantt دارای Day/Week Filter شده است.
- Project Template دارای Milestone Tab شده است.
- Private Task Notification پشتیبانی شده است.
- Task Assignment UI، Task Timer، Recurring Task و Project Status در Releaseهای متعدد اصلاح شده‌اند.
- Project Progress calculation بر اساس Project Date در Releaseهای جدید اصلاح شده است.

&rlm;نتیجه تحلیلی: Project/Task در Worksuite یک Aggregate ساده Title/Description نیست؛ Dependency، Status، Membership، Timeline، Progress، Time Log و Collaboration در آن نقش دارند.

### &rlm;Client Collaboration

&rlm;Release History ثبت کرده است که Client Contactها قابلیت Login دارند و در نسخه‌های مختلف Permission/Visibility مربوط به Project Memberهای Client، Client Ticketها و Message از Project اصلاح شده است.

&rlm;این موضوع Base Analysis را تقویت می‌کند که `Client` با `Client Contact/Login` یکی نیست و Visibility مشتری باید به Relation و Permission وابسته باشد.

### &rlm;Ticket، Chat و Notification

&rlm;CodeCanyon فعلی Ticket Management و Internal Chat را در Key Features قرار می‌دهد. همچنین Slack، Pusher و OneSignal به‌عنوان Notification/Realtime channel معرفی شده‌اند.

&rlm;در نتیجه، Worksuite سه Surface متفاوت دارد: Contextual Project/Task Collaboration، Ticket Support و General Messaging. این سه نباید در تحلیل معماری یک Entity واحد فرض شوند.

### &rlm;Role و Permission

&rlm;Custom Role and Permission Management همچنان جزو Key Features رسمی است. Base Analysis درباره تفکیک System Role، Custom Role، Module Visibility و Action Permission همچنان معتبر است.

## &rlm;Core در برابر Add-on

&rlm;صفحه رسمی CodeCanyon صریحاً هشدار می‌دهد که بعضی Moduleهای نمایش‌داده‌شده در Demo باید جداگانه خریداری شوند. بنابراین مشاهده Feature در Demo به‌تنهایی اثبات Core بودن آن نیست.

&rlm;Version History نیز این مرز را تأیید می‌کند:

- `v6.0.10` سازگاری با Employee Monitoring Module را اضافه کرده است؛ توضیح آن Module شامل Screenshot، App/Website Usage، Keyboard/Mouse Activity، Idle Detection و Task-level Time Tracking است.
- `v5.5.24` Compatibility برای AI Tools Module ثبت کرده است.
- `v5.5.22` Compatibility برای Group Message Module و Onboarding Module ثبت کرده است.

&rlm;پس Base Research باید بین `Core Message` و `Group Message Add-on`، و بین Core HR/Task Tracking و `Employee Monitoring Add-on` تفاوت بگذارد.

## &rlm;Deltaهای مهم برای Base Analysis

### &rlm;Technical Baseline

&rlm;Baseline فنی باید Worksuite را در وضعیت فعلی `Laravel 12+ / PHP 8.x` در نظر بگیرد. Version History نشان می‌دهد Major Update نسخه 6.0 در 25 March 2026 برای Laravel 12 انجام شده و چند Release بعدی Bug Fixهای Migration را ادامه داده‌اند.

### &rlm;Task Dependency

&rlm;Task Dependency در Version History به‌صورت Feature اضافه‌شده ثبت شده است. اگر در آینده محصول ما Dependency داشته باشد، این قابلیت باید به‌عنوان Graph/Constraint بین Taskها طراحی شود، نه صرفاً یک Label متنی.

### &rlm;Client Contact Login

&rlm;قابلیت Login برای Client Contact در Release History تأیید شده است. این مورد الگوی `Account/Client` در برابر `Person/Login` را برای پروژه ما تقویت می‌کند.

### &rlm;Add-on Boundary

&rlm;Employee Monitoring، AI Tools، Group Message و Onboarding نباید به‌دلیل حضور در اکوسیستم Worksuite به Core Requirementهای پروژه ما منتقل شوند.

### &rlm;Maintenance Model

&rlm;Support page رسمی می‌گوید One-click Update فقط برای کاربران دارای Active Support است و نسخه‌های قدیمی‌تر از 6 ماه یا 4 Version پشتیبانی نمی‌شوند. این مورد Product Feature نیست، اما نشان می‌دهد Upgrade/Maintenance یک Concern عملیاتی واقعی در استقرار Self-hosted Worksuite است.

## &rlm;Requirement Addendum

&rlm;این Requirementها Delta هستند و IDهای Base Analysis را تغییر نمی‌دهند.

- &rlm;&lrm;RV-WS-001&lrm; Project/Task model در صورت پشتیبانی از Dependency باید Relation صریح و Validation برای Cycle/Invalid Dependency داشته باشد.
- &rlm;&lrm;RV-WS-002&lrm; Client Contact Login باید از Client Account تفکیک شود و Access آن از Permission/Project relation تبعیت کند.
- &rlm;&lrm;RV-WS-003&lrm; Demo-visible Feature نباید بدون تأیید Documentation/License به‌عنوان Core Product طبقه‌بندی شود.
- &rlm;&lrm;RV-WS-004&lrm; Add-on باید Compatibility Version و Dependency on Core Version مشخص داشته باشد.
- &rlm;&lrm;RV-WS-005&lrm; Core Messaging و Group Messaging Add-on باید در Requirement analysis از هم جدا بمانند.
- &rlm;&lrm;RV-WS-006&lrm; Employee Monitoring data در صورت استفاده باید از Task/Time Log Core جدا و با Privacy/Retention Policy مستقل طراحی شود.
- &rlm;&lrm;RV-WS-007&lrm; Technical baseline فعلی Worksuite برای مقایسه فناوری Laravel 12+ و PHP 8.x است.
- &rlm;&lrm;RV-WS-008&lrm; Self-hosted update strategy باید Version Compatibility، Backup و Upgrade Support Window را لحاظ کند.

## &rlm;اثر روی MVP پروژه ما

&rlm;بازبینی مجدد Worksuite دلیل اضافه‌کردن HR، Finance، Ticket، Chat، Gantt، Kanban، Time Tracking یا Add-onها به MVP نیست.

&rlm;Patternهایی که همچنان برای MVP ما ارزش انتقال دارند:

- Actorهای Admin/Customer از هم جدا و Permission-aware باشند.
- Client Account از Client Contact/Login جدا باشد.
- Project Membership مرز Visibility و Assignment باشد.
- Task داخل Project Context بماند.
- Status/Assignment/Activity قابل Audit باشند.
- Add-on و Integration از Core Domain جدا بمانند.

&rlm;قابلیت‌هایی مثل Task Dependency، Gantt، Project Tags، Employee Monitoring و AI Tools فقط Future Reference هستند مگر PRD آن‌ها را وارد Scope کند.

## &rlm;Source confidence

&rlm;Module Map از Documentation رسمی Froiden/Freshdesk، Technical/Key Feature snapshot از CodeCanyon رسمی و Version Deltaها از Version History رسمی لینک‌شده توسط خود CodeCanyon گرفته شده‌اند. بخش «اثر روی MVP» Engineering Inference است و ادعای مستقیم درباره معماری داخلی Worksuite محسوب نمی‌شود.
