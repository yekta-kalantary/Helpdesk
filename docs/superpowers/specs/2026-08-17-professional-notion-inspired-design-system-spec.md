# Professional Notion-Inspired Design System

## 1. Status و تصمیم فعلی

**Status:** Approved

این سند spec رسمی و مرجع طراحی سیستم UI پنل Helpdesk است. تصمیم فعلی، حرکت تدریجی از styleهای پراکنده به یک design system سبک، semantic و قابل استفاده در تمام viewها است؛ بدون تغییر در محصول، domain behavior یا معماری backend.

این design system از وضوح و آرامش Notion الهام می‌گیرد، اما هدف آن ساخت clone نیست. هویت نهایی باید متعلق به Helpdesk باشد: یک workspace حرفه‌ای، فارسی، RTL، content-first و مناسب مدیریت پروژه و task.

## 2. Problem Statement

UI فعلی چند مسئله‌ی سیستماتیک دارد:

- رنگ‌ها بین viewها یک قرارداد واحد ندارند و بخشی از معنا با utilityهای پراکنده منتقل می‌شود.
- استفاده از `raw slate utilities` مانند `bg-slate-50`، `divide-slate-200` و `text-slate-600` در viewها و componentها، رنگ را به implementation گره می‌زند و با warm Notion-inspired direction ناسازگار است.
- `typography`، `spacing` و `radius` در نقاط مختلف به‌صورت موردی انتخاب شده‌اند؛ در نتیجه عنوان‌ها، metadata، فرم‌ها و جدول‌ها hierarchy یکسانی ندارند.
- مرز بین inspiration و clone روشن نیست؛ بعضی تصمیم‌ها از ظاهر Notion تقلید می‌کنند اما بدون یک زبان بصری کامل برای workflowهای پروژه، task و مدیریت.
- stateهای مشترک مانند `hover`، `focus-visible`، `loading`، `error` و `readonly` قرارداد یکنواختی ندارند.
- page patternهای shell، list، workspace، detail و form به component contract مشترک متکی نیستند و migration آن‌ها می‌تواند به تغییر ناخواسته‌ی behavior منجر شود.

این spec یک زبان بصری و قراردادی تعریف می‌کند تا تغییرات بعدی کوچک، قابل پیش‌بینی و قابل ارزیابی باشند.

## 3. Goal / Non-goals

### Goals

- ساخت یک visual language آرام، حرفه‌ای و content-first برای پنل RTL.
- جایگزین کردن raw color usage با سه لایه‌ی `primitive -> semantic -> component tokens`.
- ایجاد hierarchy قابل تشخیص با typography، whitespace، grouping و contrast، نه با card و shadowهای سنگین.
- تعریف component inventory و state contract مشترک برای تمام featureها.
- تضمین رفتار مناسب در `375px`، `768px`، `1024px` و `1440px`.
- حفظ خوانایی فارسی و محتوای mixed RTL/LTR شامل نام، URL، email، reference و code.
- فراهم کردن migration مرحله‌ای که Livewire، routeها، backend behavior و authorization را حفظ کند.

### Non-goals

- تغییر route name، URL، controller، model، policy، permission یا domain workflow.
- مهاجرت از Livewire یا Blade به frontend framework/API جدید.
- افزودن dependency جدید برای component library، icon library یا animation library.
- ساخت dark mode در این scope؛ semantic tokens باید امکان آن را فراهم کنند اما theme تاریک بخشی از این migration نیست.
- بازطراحی business logic، information architecture محصول یا نقش‌های Admin و Customer.
- تقلید pixel-perfect از Notion یا استفاده از trademark، asset یا component اختصاصی آن.

## 4. Visual Principles

### Content-first

هر page باید یک purpose غالب و یک visual focus داشته باشد. محتوا، task، project و action اصلی باید قبل از decoration دیده شوند. جدول، card، badge و statistic فقط زمانی استفاده می‌شوند که به فهم یا اقدام کمک کنند.

### Calm professional workspace

صفحه از warm white، charcoal text، borderهای کم‌کنتراست و spacing تنفس‌دار ساخته می‌شود. surfaceها برای جداسازی معنایی هستند، نه برای ساختن مجموعه‌ای از cardهای هم‌وزن.

### Notion DNA without cloning

از DNAهایی مانند editor-like whitespace، hierarchy نرم، low-chrome navigation و inline content الهام گرفته می‌شود؛ اما accent coral، مدل پروژه/task، RTL و الگوهای authorization محصول، هویت مستقل Helpdesk را تعیین می‌کنند.

### Hierarchy by typography and space

عنوان، section heading، body، metadata و helper text باید با type scale، weight و line-height متمایز شوند. فاصله و grouping مقدم بر border و shadow است.

### Limited accent

accent فقط برای primary action، current focus، deadline یا توجه ضروری استفاده می‌شود. statusهای semantic رنگ مخصوص خود را دارند، اما معنا هیچ‌وقت فقط با رنگ منتقل نمی‌شود و باید با text، icon یا ساختار نیز قابل فهم باشد.

## 5. Token Architecture

Tokenها سه لایه دارند و هر لایه فقط به لایه‌ی پایین‌تر خود وابسته است:

```text
Primitive tokens -> Semantic tokens -> Component tokens -> Views
```

### Primitive tokens

مقادیر پایه‌ی بدون معنا هستند: palette، اندازه‌ها، font size، weight، radius، shadow و duration. این لایه به‌ندرت تغییر می‌کند و نباید مستقیماً در view مصرف شود.

نمونه‌ی قرارداد:

```css
--color-warm-50: #FBFBFA;
--color-charcoal-700: #37352F;
--space-4: 1rem;
--radius-sm: 0.25rem;
--duration-fast: 120ms;
```

### Semantic tokens

مقدار primitive را به purpose محصول متصل می‌کنند؛ مانند `--color-page`، `--color-text` یا `--color-danger`. این لایه محل theme mapping و تصمیم‌های accessibility است.

### Component tokens

برای property و state یک component تعریف می‌شوند و فقط به semantic token ارجاع می‌دهند؛ مانند `--button-primary-bg` یا `--input-focus-ring`. component token نباید palette مستقل یا hex جدید معرفی کند.

### Naming contract

- فرمت عمومی: `--{category}-{name}-{variant}-{state}`؛ بخش‌های غیرضروری حذف می‌شوند.
- categoryهای مجاز: `color`، `font`، `text`، `space`، `radius`، `border`، `elevation`، `motion` و نام component.
- semantic name باید purpose داشته باشد، نه hue؛ `--color-primary` مجاز است و `--color-coral-button` برای semantic layer مجاز نیست.
- stateها با suffix ثابت نوشته می‌شوند: `-hover`، `-active`، `-focus`، `-selected`، `-disabled`، `-loading` و `-error`.
- primitive palette می‌تواند hue داشته باشد؛ semantic و component باید alias باشند.
- viewها فقط semantic و component token مصرف می‌کنند. استفاده از primitive در view، inline style یا arbitrary value ممنوع است.
- هر token جدید باید یک مصرف مشخص، یک purpose روشن و یک جایگاه در یکی از سه layer داشته باشد.

## 6. Color System

رنگ‌ها محدود، warm و semantic هستند. palette پایه از direction فعلی می‌آید: `page` گرم نزدیک به `#FBFBFA`، `surface` سفید، text زغالی نزدیک به `#37352F`، muted warm gray، border warm light، accent coral و success earthy green.

### Semantic color contract

| Token | کاربرد | رفتار مورد انتظار |
|---|---|---|
| `--color-page` | background اصلی workspace و body | warm white و کم‌تنش |
| `--color-surface` | section، panel، input و surfaceهای جداکننده | سفید یا surface خنثی semantic |
| `--color-text` | متن اصلی، heading و action مهم | بیشترین contrast متن |
| `--color-muted` | metadata، helper text و متن راهنمای خالی | secondary، اما قابل خواندن |
| `--color-border` | border کنترل‌ها و separatorهای ضروری | thin و کم‌کنتراست |
| `--color-primary` | primary action و link/action اصلی | accent محدود با متن قابل خواندن |
| `--color-accent` | توجه، deadline، current emphasis و highlight | همان family هویتی primary، نه decoration عمومی |
| `--color-success` | completed، موفقیت و وضعیت مثبت | همراه label یا icon |
| `--color-warning` | نیازمند توجه، نزدیک شدن deadline یا caution | همراه label یا توضیح |
| `--color-danger` | destructive، error و وضعیت خطر | همراه متن صریح و confirmation در اقدام مخرب |
| `--color-info` | توضیح، راهنمایی و context غیرمسدودکننده | قابل تشخیص بدون اتکا به hue |
| `--color-focus` | focus-visible ring | مستقل از background و همیشه قابل مشاهده |

برای stateهای semantic، surface variantها با قرارداد `{semantic}-surface` و foregroundهای لازم با `{semantic}-text` تعریف می‌شوند؛ مانند `--color-danger-surface` و `--color-danger-text`. رنگ neutral برای داده‌ی بدون status از `--color-muted` یا `--color-surface-muted` استفاده می‌کند و palette تازه‌ای ایجاد نمی‌شود.

### ممنوعیت raw colors در views

- استفاده از `text-slate-*`، `bg-slate-*`، `border-slate-*`، hex، `rgb()`، `hsl()` یا arbitrary color در view و component ممنوع است.
- `raw slate utilities` موجود باید در migration به semantic utility یا CSS variable تبدیل شوند؛ نگه‌داشتن آن‌ها به‌عنوان shortcut مجاز نیست.
- primitive token فقط در فایل token definition یا theme mapping قرار می‌گیرد.
- status badge باید text داشته باشد؛ رنگ به‌تنهایی status را اعلام نمی‌کند.
- contrast متن عادی حداقل `4.5:1` و contrast متن بزرگ حداقل `3:1` است. focus indicator باید نسبت به زمینه و اطراف خود قابل تشخیص بماند.

## 7. Typography

font اصلی تمام UI، `IRANYekanXVF` با variable weight و `font-display: swap` است. وزن‌ها برای hierarchy استفاده می‌شوند، نه برای تزئین.

### Roles

| Role | کاربرد | اندازه/line-height هدف |
|---|---|---|
| `display` | title صفحه‌ی اصلی یا workspace مهم | `28-32px`، حدود `1.3` |
| `heading-xl` | heading سطح اول page | `24px`، حدود `1.35` |
| `heading-lg` | section و panel heading | `20px`، حدود `1.4` |
| `heading-md` | subsection و group label | `16-18px`، حدود `1.45` |
| `body` | متن اصلی و description | `15-16px`، حدود `1.75` در RTL |
| `body-sm` | متن فشرده، row detail و table cell | `13-14px`، حدود `1.65` |
| `label` | label فرم، button و control | `13-14px`، weight متوسط تا semibold |
| `metadata` | project، date، assignee و اطلاعات فرعی | `12-13px`، رنگ muted |
| `caption` | توضیح کوتاه یا helper کم‌اهمیت | `11-12px`، فقط برای محتوای non-critical |
| `numeric` | count، progress و reference | همان font با `font-variant-numeric: tabular-nums` در صورت نیاز |

### RTL/LTR mixed content

- متن فارسی در document flow با `direction: rtl` و `text-align: start` نمایش داده می‌شود.
- مقدارهای ذاتاً LTR مانند email، URL، slug، code، version و ticket reference با wrapper دارای `dir="ltr"` و alignment مناسب نمایش داده می‌شوند.
- برای رشته‌ی mixed از `unicode-bidi: plaintext` یا wrapper semantic استفاده می‌شود؛ `direction` به‌صورت تصادفی روی کل page override نمی‌شود.
- شماره‌ها، تاریخ‌ها و currency باید format قابل پیش‌بینی داشته باشند و در کنار label معنای خود را از دست ندهند.
- truncation نباید ابتدای reference یا identifier را حذف کند؛ برای مقدارهای مهم از wrap، `title` یا copy action استفاده می‌شود.

## 8. Spacing, Radius, Border, Elevation and Motion Scales

### Spacing

مقیاس بر پایه‌ی `4px` است و فاصله‌ها semantic مصرف می‌شوند:

| Token | مقدار پایه | کاربرد |
|---|---:|---|
| `--space-1` | `4px` | فاصله‌ی icon و label، micro gap |
| `--space-2` | `8px` | gap داخلی کوچک و chip |
| `--space-3` | `12px` | control padding و row gap |
| `--space-4` | `16px` | component gap و form field |
| `--space-6` | `24px` | section داخلی |
| `--space-8` | `32px` | separation بین گروه‌ها |
| `--space-10` | `40px` | page rhythm متوسط |
| `--space-12` | `48px` | separation اصلی page |
| `--space-16` | `64px` | فضای باز workspace و hero content |

Semantic aliasها شامل `--space-control`، `--space-component`، `--space-section` و `--space-page` هستند. فاصله‌ی جدید خارج از scale فقط با دلیل documented و مصرف مشخص مجاز است.

### Radius

- `--radius-none`: edgeهای کاملاً flat در موارد خاص.
- `--radius-sm`: `4px` برای input، badge و compact control.
- `--radius-md`: `6px` برای button، row highlight و section surface.
- `--radius-lg`: `8px` برای panel و dialog.
- `--radius-full`: برای pill، avatar و indicatorهای کاملاً گرد.

radius پیش‌فرض سیستم `6-8px` است. rounded بزرگ، pill عمومی و شکل‌های تزئینی در scope این system نیستند.

### Border and elevation

- border پیش‌فرض `1px solid var(--color-border)` است.
- separator فقط برای group boundary یا scanability استفاده می‌شود؛ هر card نباید border اجباری داشته باشد.
- elevation به سه سطح محدود است: `none`، `subtle` برای surface شناور، و `overlay` برای menu/drawer/dialog.
- shadow باید warm/neutral، کوتاه و کم‌کنتراست باشد؛ gradient، glassmorphism و heavy shadow ممنوع است.

### Motion

- `--motion-fast`: حدود `120ms` برای hover و feedback فوری.
- `--motion-standard`: حدود `180ms` برای expand، drawer و state transition.
- `--motion-slow`: حدود `240ms` فقط برای overlay یا continuity ضروری.
- فقط `opacity` و `transform` animate می‌شوند؛ animation نباید layout را جابه‌جا کند.
- `prefers-reduced-motion: reduce` باید transition و animation غیرضروری را تقریباً حذف کند.

## 9. Interaction / State Contract

هر interactive component باید stateهای زیر را با semantics، visual و accessibility مشخص پشتیبانی کند:

| State | قرارداد |
|---|---|
| `hover` | تغییر محدود در surface یا text؛ بدون پرش layout و بدون وابستگی صرف به رنگ |
| `focus-visible` | outline/ring واضح با `--color-focus`، offset کافی و بدون مخفی شدن زیر sticky UI |
| `selected` | background یا border semantic به‌همراه `aria-selected`، `aria-current` یا state مناسب |
| `disabled` | غیرقابل تعامل، contrast قابل تشخیص برای context و `disabled`/`aria-disabled` صحیح |
| `loading` | indicator و label قابل فهم، حفظ فضای کنترل و محدود کردن loading به action فعال Livewire |
| `error` | inline error نزدیک field/control، ارتباط با `aria-describedby` و رنگ همراه متن/icon |
| `readonly` | نمایش واضح غیرقابل ویرایش، حفظ امکان select/copy در صورت نیاز و عدم نمایش control فعال جعلی |
| `completed` | text/icon completion و کاهش emphasis؛ اطلاعات تاریخی حذف یا مبهم نمی‌شود |
| `empty` | توضیح وضعیت، علت قابل فهم و next action مفید در صورت وجود |

- icon-only control باید accessible name داشته باشد؛ icon تزئینی `aria-hidden="true"` است.
- drag and drop همیشه alternative keyboard و single-pointer دارد.
- toast فقط برای success کوتاه و non-blocking مناسب است؛ error مهم در alert یا inline context باقی می‌ماند.

## 10. Component Inventory و مسئولیت هر گروه

### Foundations

`Typography`، `Icon`، `Link`، `Divider`، `VisuallyHidden` و `FocusRing` پایه‌ی دسترسی و visual language هستند و نباید business logic داشته باشند.

### Shell and navigation

`AppShell`، `Sidebar`، `MobileDrawer`، `TopBar`، `Breadcrumbs`، `PageHeader` و `SectionTabs` مسئول context، navigation، responsive shell و current location هستند. authorization فقط تعیین می‌کند چه navigationی render شود؛ shell نباید permission را دوباره پیاده‌سازی کند.

### Content and layout

`Section`، `Surface/Card`، `Stack`، `Grid`، `ContextRail`، `ListRow` و `Disclosure` مسئول grouping، spacing و progressive disclosure هستند. card primitive نباید dashboard را به grid از boxهای هم‌وزن تبدیل کند.

### Data and status

`Table`، `DataList`، `StatRow`، `Badge`، `Progress`، `Avatar`، `ActivityItem` و `EmptyState` مسئول نمایش محتوا و status هستند. table فقط برای comparison واقعی است و mobile alternative دارد.

### Form and action

`Button`، `ButtonGroup`، `Input`، `Textarea`، `Select`، `Checkbox`، `Radio`، `DateField`، `FilterBar`، `FormSection` و `ActionBar` مسئول input، validation، loading و destructive intent هستند.

### Feedback and overlay

`Alert`، `Toast`، `Dialog`، `Popover`، `Tooltip`، `ConfirmDialog`، `Skeleton` و `LoadingIndicator` مسئول feedback و overlay هستند. overlayها focus management، Escape behavior و بازگشت focus را رعایت می‌کنند.

### Domain composition

`ProjectSummary`، `TaskRow`، `KanbanColumn`، `TaskCard`، `Checklist`، `CommentThread`، `AttachmentList`، `ActivityHistory` و `NotificationItem` componentهای domain هستند؛ token و state contract می‌گیرند اما authorization و mutation را از backend/Livewire دریافت می‌کنند.

## 11. Page Patterns

### Shell

ساختار پایه `AppShell -> Sidebar/Drawer + TopBar + Main`. sidebar سلسله‌مراتب ساده و active state واضح دارد؛ Admin-only management پایین navigation جدا می‌شود. top bar context، search access و notification را نگه می‌دارد. در عمق `Client -> Project -> Task` از breadcrumb استفاده می‌شود.

### Dashboard

با page title و یک focus block شروع می‌شود. priority work به‌صورت row خوانا، projectها به‌صورت inline summary و activity در secondary section یا rail قرار می‌گیرد. equal-weight stat card پیش‌فرض نیست و empty state next action دارد.

### Lists

لیست Client، Project و Task یک command row برای search و frequent filters دارد. filter کامل در disclosure یا drawer است. row موبایل full-width و touch-safe است؛ table فقط در جایی استفاده می‌شود که مقایسه‌ی ستون‌ها ارزش واقعی دارد.

### Project workspace

صفحه با breadcrumb، project title، description کوتاه و یک primary action شروع می‌شود. tabs کم‌chrome برای overview، tasks، activity، members و management مجاز هستند. Kanban مرکز task workspace است؛ status، progress، due date و metadata در contextual rail قرار می‌گیرند. Workflow و Work Group configuration در management section می‌ماند.

### Task detail

بالای صفحه Reference، title، status و primary action دیده می‌شود. description، checklist و conversation در main column هستند و assignee، priority، due date، project و Work Group در rail. attachment کنار conversation می‌ماند و activity history در disclosure ثانویه قرار می‌گیرد. task یا project completed به‌صورت semantic و visible readonly است؛ reopen و moderation action صریح و جدا هستند.

### Forms

form یک page واحد با sectionهای sequential برای identity، context، ownership و scheduling دارد. label همیشه visible، helper text کوتاه و error کنار field است. desktop action row inline و mobile action bar sticky است. Livewire binding، validation، loading و unsaved-change protection بدون تغییر باقی می‌ماند.

### Notifications

notification list با unread state معنایی، timestamp، source/context و link به مقصد نمایش داده می‌شود. read/unread فقط با رنگ تشخیص داده نمی‌شود. bulk یا destructive action confirmation و loading state مستقل دارد؛ empty state توضیح می‌دهد که notification جدیدی وجود ندارد.

## 12. RTL, Accessibility and Responsive Rules

### RTL و layout

- document root به‌صورت RTL است؛ layout از logical properties مانند `margin-inline`، `padding-inline` و `inset-inline` استفاده می‌کند.
- alignment پیش‌فرض `start` است، نه hard-coded `right`؛ exception فقط برای data ذاتاً LTR یا numeric است.
- ترتیب DOM باید با reading order سازگار باشد و visual reordering با CSS نباید keyboard order را خراب کند.
- breadcrumb، tab، table header، drawer و form error باید در RTL و mixed content بازبینی شوند.

### Accessibility

- متن عادی حداقل contrast `4.5:1` دارد.
- همه‌ی controlها keyboard accessible و focus-visible هستند؛ target تعاملی حداقل `44px` است.
- landmark، heading hierarchy، label، helper/error relationship و input type/autocomplete معتبر هستند.
- status با text یا icon تکمیل می‌شود و icon تزئینی hidden است.
- drawer و dialog هنگام باز بودن focus را مدیریت می‌کنند، Escape و backdrop dismissal درست دارند و focus را به opener برمی‌گردانند.
- loading space حفظ می‌شود، focus obscured نمی‌شود و reduced motion رعایت می‌شود.
- long label، long URL، text wrap، zoom، keyboard-only، screen reader و high contrast باید در verification پوشش داده شوند.

### Responsive breakpoints

| Viewport | Rule |
|---:|---|
| `375px` | single column، mobile drawer، action bar sticky، row به‌جای table و بدون page-level horizontal scroll |
| `768px` | content width بازتر، filter disclosure قابل استفاده، دو ستون فقط برای contentهایی که واقعاً نیاز دارند |
| `1024px` | shell کامل‌تر، contextual rail در صورت ظرفیت، table فقط با mobile fallback |
| `1440px` | max-width خوانا، whitespace بیشتر و rail مستقل؛ استفاده نکردن از عرض اضافی برای cardهای بیشتر |

Kanban تنها exception مجاز برای horizontal scrolling scoped است و باید alternative movement غیر drag نیز داشته باشد.

## 13. Migration Strategy

Migration به ترتیب زیر انجام می‌شود و در هیچ مرحله‌ای route، Livewire contract، backend behavior یا authorization تغییر نمی‌کند:

1. **Inventory و baseline:** تمام raw colors، typography، spacing، radius، shadow، componentهای موجود و page patternها ثبت و با این قرارداد تطبیق داده می‌شوند.
2. **Token foundation:** primitive، semantic و component tokenها در محل stylesheet موجود تعریف می‌شوند؛ aliasهای semantic با مقادیر فعلی map می‌شوند تا تغییر behavior رخ ندهد.
3. **Global foundation:** `IRANYekanXVF`، base direction، focus-visible، motion، logical layout و minimum targetها یکدست می‌شوند.
4. **Shared components:** shell، page header، buttons، inputs، badges، card/section، table، filter bar، tabs، alert و empty state به tokenها متصل می‌شوند.
5. **Raw utility removal:** `raw slate utilities` و raw colorهای تکراری از componentها و views به‌ترتیب مصرف حذف و با semantic/component token جایگزین می‌شوند.
6. **Page patterns:** shell، dashboard، lists، project workspace، task detail، forms و notifications با ترتیب کم‌ریسک و حفظ Livewire bindings migration می‌شوند.
7. **Responsive و accessibility hardening:** چهار viewport، RTL/LTR mixed content، keyboard، focus، reduced motion، loading، error، readonly و empty state بررسی می‌شوند.
8. **Cleanup و guardrail:** token naming و component contract مرور می‌شود و rule یا lint/check مناسب برای جلوگیری از بازگشت raw colors مستند و در صورت وجود tooling پروژه فعال می‌شود.

هر phase باید مستقل قابل review باشد. تغییر visual نباید selector یا `wire:model`، action name، route parameter، authorization check یا backend mutation را تغییر دهد.

## 14. Verification and Acceptance Criteria

این spec زمانی پذیرفته و migration زمانی complete تلقی می‌شود که:

- تمام صفحه‌های in-scope از semantic/component tokens استفاده کنند و raw color در views نداشته باشند.
- token naming مطابق contract باشد و هیچ component palette اختصاصی خارج از token architecture نسازد.
- `IRANYekanXVF` در فارسی و mixed RTL/LTR بدون clipping، bidi corruption یا truncation مخرب نمایش داده شود.
- shell، dashboard، lists، project workspace، task detail، forms و notifications در چهار viewport تعریف‌شده قابل استفاده باشند.
- page-level horizontal overflow وجود نداشته باشد؛ horizontal scroll فقط در Kanban و به‌صورت scoped باشد.
- keyboard navigation، `focus-visible`، focus return drawer/dialog، Escape، reduced motion و target حداقل `44px` verified باشند.
- contrast متن، focus و stateها با معیارهای accessibility منطبق باشند و status فقط به رنگ وابسته نباشد.
- loading فضای layout را حفظ کند و error، readonly، completed و empty state هم visual و هم semantic contract داشته باشند.
- feature testهای مرتبط، authorization و deep linkها بدون تغییر در behavior pass شوند.
- routeها، Livewire action/bindingها، backend validation و permission boundary همان رفتار قبل را حفظ کنند.
- build و asset pipeline بدون خطای token یا stylesheet تولید شود و review بصری در viewportهای `375px`، `768px`، `1024px` و `1440px` انجام شده باشد.

## 15. Risks and Explicit Decisions

### Risks

- حذف ناگهانی raw utilities ممکن است contrast یا state ظاهری برخی viewهای قدیمی را تغییر دهد؛ migration باید incremental و با screenshot/visual review انجام شود.
- map کردن رنگ‌های قدیمی به semantic token ممکن است statusهایی را که قبلاً فقط با hue فهمیده می‌شدند آشکار کند؛ label و icon باید هم‌زمان اصلاح شوند.
- shared component migration می‌تواند چند page را هم‌زمان تحت تأثیر قرار دهد؛ هر تغییر component باید consumerهای اصلی خود را verify کند.
- mixed RTL/LTR و identifierهای طولانی ممکن است در row و table باعث overflow شوند؛ wrap و scoped overflow باید بخشی از acceptance باشد.
- sticky action bar، drawer و overlay می‌توانند focus را obscure کنند؛ accessibility review جداگانه لازم است.

### Explicit decisions

- هویت بصری: warm white، charcoal text، warm border، coral accent و earthy semantic colors؛ gradient و glassmorphism استفاده نمی‌شود.
- font اصلی: `IRANYekanXVF`؛ font replacement یا افزودن font جدید در این scope مجاز نیست.
- معماری token: فقط `primitive -> semantic -> component`؛ view مستقیماً primitive مصرف نمی‌کند.
- naming: semantic token بر اساس purpose و component token بر اساس property/state نام‌گذاری می‌شود.
- color policy: raw color و raw slate utility در view ممنوع است؛ semantic text/icon باید کنار color status وجود داشته باشد.
- layout policy: content-first و low-chrome؛ card grid و heavy elevation پیش‌فرض نیست.
- interaction policy: حداقل target `44px`، focus-visible روشن، reduced motion و keyboard alternative برای drag/drop الزامی است.
- scope policy: Livewire، Blade، routeها، backend، authorization، domain workflow و dependencyهای فعلی حفظ می‌شوند.
- responsive policy: چهار viewport مرجع ثابت‌اند و فقط Kanban اجازه‌ی horizontal scrolling scoped دارد.
- delivery policy: migration مرحله‌ای است و هر phase باید behavior موجود را حفظ کند؛ این سند design contract است، نه مجوز تغییر domain یا backend.
