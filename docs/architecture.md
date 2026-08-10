# معماری

پروژه فقط سه بخش کاربردی دارد:

```text
Users
Projects
Tasks
```

کد در سه ماژول نگهداری می‌شود:

```text
Identity   -> کاربران و ورود
Projects   -> پروژه‌ها و اعضای پروژه
Tasks      -> تسک‌های پروژه
```

## وابستگی‌ها

```text
Identity
   ↓
Projects
   ↓
Tasks
```

`Projects` برای انتخاب اعضا از User استفاده می‌کند و `Tasks` به Project وابسته است.

## دسترسی

Authorization عمداً ساده است:

- `users.is_admin = true`: دسترسی مدیریتی کامل
- کاربر عادی: فقط پروژه‌هایی که عضو آن‌هاست
- Task برای کاربر عادی فقط وقتی قابل مشاهده است که Task متعلق به یکی از پروژه‌های عضو شدهٔ او باشد

عضویت پروژه فقط در جدول `project_user` نگهداری می‌شود.

## مدل داده

```text
User N <-> N Project
Project 1 -> N Task
```

دسترسی Task مستقیماً از عضویت در Project به دست می‌آید.
