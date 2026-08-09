# دسترسی‌ها

`admin` تنها Role سیستمی و immutable است.

Permissionها در `Modules\Identity\Domain\Access\PermissionCatalog` تعریف می‌شوند و فقط چهار گروه دارند:

## contacts

```text
contacts.view
contacts.create
contacts.update
```

## projects

```text
projects.view
projects.create
projects.update
projects.delete
```

## tasks

```text
tasks.view
tasks.create
tasks.update
tasks.delete
tasks.comment
tasks.manage_all
```

## identity

```text
users.view
users.create
users.update
roles.view
roles.create
roles.update
roles.delete
```

Permissionهای Customer Portal، Ticket، Report، Notification و Settings وجود ندارند.
