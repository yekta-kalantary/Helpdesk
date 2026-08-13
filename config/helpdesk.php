<?php

return [
    'admin' => [
        'first_name' => env('HELPDESK_ADMIN_NAME', 'Administrator'),
        'last_name' => env('HELPDESK_ADMIN_LAST_NAME', 'User'),
        'email' => env('HELPDESK_ADMIN_EMAIL', 'admin@example.com'),
        'mobile' => env('HELPDESK_ADMIN_MOBILE', '09120000000'),
        'password' => env('HELPDESK_ADMIN_PASSWORD', 'password'),
    ],

    'attachments' => [
        'max_kilobytes' => (int) env('HELPDESK_ATTACHMENT_MAX_KB', 20 * 1024),
        'extensions' => [
            'jpg', 'jpeg', 'png', 'webp', 'gif',
            'pdf', 'txt', 'csv',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'zip', 'rar', '7z',
        ],
        'mime_types' => [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf', 'text/plain', 'text/csv',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip', 'application/x-zip-compressed',
            'application/vnd.rar', 'application/x-rar-compressed',
            'application/x-7z-compressed',
        ],
        'preview_mime_types' => [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf', 'text/plain', 'text/csv',
        ],
    ],
];
