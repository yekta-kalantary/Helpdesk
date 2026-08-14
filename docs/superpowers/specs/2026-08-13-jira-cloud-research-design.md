# Jira Cloud Research — Design

## هدف

Jira Cloud / Jira Software به‌عنوان سومین Product Reference در کنار RISE CRM و Worksuite به `docs/research/` اضافه شود تا در طراحی نسخه‌های بعدی Helpdesk بتوان از Patternهای بالغ Jira برای Work Management، Workflow، Planning، Search، Permission، Automation و Reporting استفاده کرد.

## Scope تأییدشده

این Research فقط **Jira Cloud / Jira Software** را پوشش می‌دهد. Jira Service Management، Customer Portal، Request/Queue/SLA، Incident/Problem/Change، Confluence Knowledge Base و Jira Product Discovery خارج از Scope هستند.

## مرز با MVP

این تغییر هیچ Requirement جدیدی به `docs/product/client-task-management-mvp.md` اضافه نمی‌کند و هیچ Runtime code، Migration، Route، UI یا Test را تغییر نمی‌دهد. Research فقط مرجع تصمیم برای نسخه‌های بعدی است. اگر Research با PRD تعارض داشته باشد، PRD مرجع نهایی Scope است.

## خروجی‌ها

1. `docs/research/jira-cloud-product-analysis.md` — Base Product Analysis پایدار.
2. `docs/research/jira-cloud-revalidation-2026-08-13.md` — Current-state snapshot و Terminology/edition caveatها.
3. `docs/research/README.md` — افزودن Jira Cloud به Source Index.

## Source policy

فقط منابع رسمی Atlassian Support برای Jira Cloud، Jira administration و Cloud Automation به‌عنوان منبع پایه استفاده می‌شوند. Community post، Marketplace listing، review site و third-party blog منبع Requirement پایه نیستند.

## Terminology policy

در سند از Terminology فعلی Jira Cloud استفاده می‌شود: `Space`، `Work item` و `Work type`. هرجا API، JQL یا documentation rollout هنوز `Project`، `Issue` یا `Issue type` دارد، Legacy alias صریحاً ذکر می‌شود.

## Domain coverage

- Space model و Team-managed / Company-managed configuration
- Work item model و hierarchy
- Workflow / Status / Transition
- Boards / Backlog / Ranking / Quick Filters
- Scrum / Sprint / Estimation
- Kanban / continuous flow
- Fields / Custom Fields / Work Types / Schemes
- JQL / Search / Saved Filters
- Roles / Permission Schemes / Work-item Security
- Collaboration و linking
- Epic / Parent hierarchy / Timeline / Dependencies / Plans
- Components / Versions / Releases
- Automation Rules
- Forms / Work Intake
- Reports / Dashboards
- Development metadata and release visibility

## Extraction rule

هر Domain بین سه سطح تفاوت می‌گذارد: **Observed capability**، **Extracted requirement** و **Product implication**. وجود Feature در Jira مجوز ورود آن به MVP یا Roadmap نیست.

## Non-goals

- Reproducing Jira feature-for-feature
- ساخت Jira clone
- وارد کردن Agile ceremonyها بدون نیاز واقعی Product
- طراحی Permission/Workflow engine عمومی صرفاً به دلیل وجود آن در Jira
- وابسته‌کردن Domain model محصول به Atlassian terminology

## Success criteria

کار زمانی کامل است که Jira در Research Index هم‌سطح RISE/Worksuite دیده شود، Base Analysis برای تصمیم‌های بعدی Domain/Feature قابل استناد باشد، Current-state caveatها در Re-validation ثبت شوند، و هیچ تغییری در PRD یا Runtime ایجاد نشده باشد.
