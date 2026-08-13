<?php

return [
    'tasks' => 'تسک‌ها',
    'new_task' => 'تسک جدید',
    'edit_task' => 'ویرایش تسک',
    'project' => 'پروژه',
    'search_placeholder' => 'جستجو در عنوان یا Reference تسک',
    'comment_or_attachment_required' => 'برای ثبت نظر، متن یا حداقل یک فایل لازم است.',
    'too_many_uploads' => 'تعداد آپلودها بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
    'statuses' => [
        'todo' => 'برای انجام',
        'in_progress' => 'در حال انجام',
        'waiting_admin' => 'منتظر ادمین',
        'waiting_customer' => 'منتظر مشتری',
        'completed' => 'تکمیل‌شده',
        'cancelled' => 'لغوشده',
    ],
    'priorities' => [
        'low' => 'کم',
        'normal' => 'عادی',
        'high' => 'زیاد',
    ],
    'roles' => [
        'admin' => 'ادمین',
        'customer' => 'مشتری',
    ],
    'assignee' => [
        'admin_queue' => 'صف ادمین',
        'none' => 'بدون مسئول',
    ],
    'activity_actions' => [
        'task' => [
            'created' => 'تسک ایجاد شد',
            'status_changed' => 'وضعیت تسک تغییر کرد',
            'assignee_changed' => 'مسئول تسک تغییر کرد',
            'completed' => 'تسک تکمیل شد',
            'cancelled' => 'تسک لغو شد',
            'reopened' => 'تسک بازگشایی شد',
            'priority_changed' => 'اولویت تسک تغییر کرد',
            'due_date_changed' => 'موعد تسک تغییر کرد',
        ],
        'comment' => [
            'added' => 'نظر ثبت شد',
            'hidden' => 'نظر مخفی شد',
        ],
        'attachment' => [
            'added' => 'فایل پیوست شد',
            'hidden' => 'فایل مخفی شد',
        ],
        'project' => ['status_changed' => 'وضعیت پروژه تغییر کرد'],
        'membership' => [
            'added' => 'عضو پروژه اضافه شد',
            'removed' => 'عضو پروژه حذف شد',
        ],
    ],
];
