# Roadmap

این سند محدوده فعلی را از توسعه‌های بعدی جدا می‌کند.

## نسخه فعلی

- احراز هویت وب
- Client Portal
- Dynamic Role/Permission
- Customer Management
- Project Management و اعضای تیم
- Task Kanban
- Task assignment/deadline/time tracking fields
- Task comments داخلی
- Task/Ticket attachments محلی
- Customer-visible task boundary
- Ticket thread و assignment/status
- Database Notifications
- Operational Reports
- SMTP Settings
- فارسی/RTL

## خارج از محدوده فعلی

موارد زیر عمداً پیاده‌سازی نشده‌اند:

- Invoice
- Payment Gateway
- Accounting
- Service Subscription
- Website entity
- API / Mobile API

## پیشنهاد توسعه بعدی

### مرحله 1: Audit & Activity

- activity log استاندارد برای تغییر status/assignment
- ثبت actor، before/after و timestamp
- گزارش history برای Project/Task/Ticket

### مرحله 2: SLA

- policy پاسخ اولیه و زمان حل Ticket
- business hours
- SLA breach indicators
- escalation notification

### مرحله 3: Planning

- Project template
- Task dependency
- recurring task
- milestone
- calendar view

### مرحله 4: Communication

- email notification اختیاری روی notificationهای مهم
- templateهای ایمیل
- notification preference برای هر user

### مرحله 5: Finance

در صورت تصمیم کسب‌وکار، Finance باید bounded context مستقل باشد و به هسته Task/Ticket تزریق نشود:

```text
Billing
├── Invoice
├── InvoiceItem
├── Payment
└── PaymentGateway
```

### مرحله 6: Services / Subscriptions

Subscription نیز bounded context مستقل باشد و Project فقط reference/contract مناسب دریافت کند.

## قواعد Roadmap

- قبل از اضافه کردن infrastructure جدید، نیاز واقعی عملیاتی اثبات شود.
- Redis/queue worker فقط وقتی async workload واقعی وجود داشت اضافه شود.
- API فقط وقتی consumer مشخص وجود داشت ایجاد شود.
- هر قابلیت جدید باید permission، row-scope، translation و test داشته باشد.
