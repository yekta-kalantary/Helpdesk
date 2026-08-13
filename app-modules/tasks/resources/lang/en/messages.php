<?php

return [
    'tasks' => 'Tasks',
    'new_task' => 'New task',
    'edit_task' => 'Edit task',
    'project' => 'Project',
    'search_placeholder' => 'Search task title',
    'statuses' => [
        'todo' => 'To do',
        'in_progress' => 'In progress',
        'waiting_admin' => 'Waiting for admin',
        'waiting_customer' => 'Waiting for customer',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'priorities' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
    ],
    'roles' => [
        'admin' => 'Admin',
        'customer' => 'Customer',
    ],
    'assignee' => [
        'admin_queue' => 'Admin queue',
        'none' => 'Unassigned',
    ],
    'activity_actions' => [
        'task' => [
            'created' => 'Task created',
            'status_changed' => 'Task status changed',
            'assignee_changed' => 'Task assignee changed',
            'completed' => 'Task completed',
            'cancelled' => 'Task cancelled',
            'reopened' => 'Task reopened',
            'priority_changed' => 'Task priority changed',
            'due_date_changed' => 'Task due date changed',
        ],
        'comment' => [
            'added' => 'Comment added',
            'hidden' => 'Comment hidden',
        ],
        'attachment' => [
            'added' => 'Attachment added',
            'hidden' => 'Attachment hidden',
        ],
        'project' => ['status_changed' => 'Project status changed'],
        'membership' => [
            'added' => 'Project member added',
            'removed' => 'Project member removed',
        ],
    ],
];
