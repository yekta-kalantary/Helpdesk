# معماری

پروژه یک Modular Monolith با چهار bounded context است:

```text
Identity
Contacts
Projects
Tasks
```

## وابستگی دامنه

```text
Contact 1 ─── 0..1 User
Contact 1 ─── N Project
Project 1 ─── N Task
User N ─── N Project
User 1 ─── N Task (creator / assignee)
```

- `Contact` منبع واحد اطلاعات شخصی است.
- `User` فقط Authentication و Authorization را نگه می‌دارد.
- Project نوع `contact` مستقیماً `contact_id` دارد.
- Project نوع `internal` بدون Contact است.
- Task همیشه متعلق به Project است.

ماژول‌های Customers، Tickets، Reports، Settings، Notification Center و Client Portal بخشی از معماری فعلی نیستند.
