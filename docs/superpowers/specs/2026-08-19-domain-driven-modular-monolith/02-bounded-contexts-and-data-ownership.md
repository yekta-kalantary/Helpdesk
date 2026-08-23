# Bounded Contexts and Data Ownership

## Identity and Access

Identity owns authentication and account access.

Owned data:

- Account identity
- Credentials and password reset state
- Account role and access status
- Authentication-related profile data
- Permission and capability decisions

Other contexts store only the account identifier or a local read model such as `AccountSummary`. They do not import or query the Identity `User` model directly.

## Clients

Clients owns customer organizations and customer contacts.

Owned data:

- Client
- Contact
- Client lifecycle and status

Projects may store a `client_id` reference, but Client data is resolved through a contract or local projection. Projects must not define an Eloquent `belongsTo(Client::class)` relationship.

## Projects

Projects owns the project workspace and project membership.

Owned data:

- Project
- Project lifecycle
- Project membership
- Work group
- Project-owned task status definitions

Projects may store external account and client identifiers. It owns membership decisions, but it does not own accounts or clients.

## Tasks and Collaboration

Tasks and Collaboration may remain one module while their data ownership stays explicit.

Owned data:

- Task
- Task assignment references
- Task priority and due date
- Checklist item
- Comment
- Attachment metadata and storage ownership
- Task workflow decisions

Task data may contain opaque `project_id` and `assigned_to` identifiers. It must not import Project or Identity infrastructure models.

## Ownership Matrix

| Data | Owner | Other contexts may keep |
| --- | --- | --- |
| Account | Identity | AccountSummary projection |
| Role and account status | Identity | Read-only capability projection |
| Client | Clients | ClientSummary projection |
| Contact | Clients | ContactSummary projection |
| Project | Projects | ProjectSummary projection |
| Project membership | Projects | Membership read model |
| Work group | Projects | Work group summary |
| Project task status definition | Projects | Status label projection |
| Task | Tasks | Task summary |
| Checklist item | Tasks | Checklist read model |
| Comment | Collaboration | Comment count or summary |
| Attachment metadata | Collaboration | Attachment summary |

## Forbidden Patterns

```php
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
```

The following are architectural violations when used across contexts:

- Importing another context's infrastructure model
- Cross-context `belongsTo`, `hasMany`, or `belongsToMany` relationships
- Cross-context foreign keys
- Cross-context database joins
- Reading another context's table through `DB::table()` or raw SQL
- Shared business models in `app/Models`
- Shared business policies in the root application when ownership belongs to a module
- Updating another context's tables inside the current context's transaction
- Events that expose mutable Eloquent models instead of stable payloads
- Treating a read projection as authoritative data
