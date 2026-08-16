<?php

return [
    'tasks' => 'Tasks',
    'new_task' => 'New task',
    'edit_task' => 'Edit task',
    'project' => 'Project',
    'search_placeholder' => 'Search task title or reference',
    'comment_or_attachment_required' => 'A comment needs text or at least one attachment.',
    'too_many_uploads' => 'Too many uploads. Please try again shortly.',
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
        'none' => 'Unassigned',
    ],
    'activity_actions' => [
        'task' => [
            'created' => 'Task created',
            'status_changed' => 'Task status changed',
            'assignee_changed' => 'Task assignee changed',
            'work_group_changed' => 'Task Work Group changed',
            'completed' => 'Task completed',
            'reopened' => 'Task reopened',
            'priority_changed' => 'Task priority changed',
            'due_date_changed' => 'Task due date changed',
        ],
        'subtask' => [
            'added' => 'Subtask added',
            'renamed' => 'Subtask renamed',
            'completed' => 'Subtask completed',
            'uncompleted' => 'Subtask reopened',
            'removed' => 'Subtask removed from checklist',
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
        'project_status' => [
            'created' => 'Project task status created',
            'renamed' => 'Project task status renamed',
            'reordered' => 'Project task statuses reordered',
            'done_changed' => 'Project Done status changed',
            'inactivated' => 'Project task status inactivated',
        ],
        'work_group' => [
            'created' => 'Work Group created',
            'renamed' => 'Work Group renamed',
            'updated' => 'Work Group updated',
            'moved' => 'Work Group moved',
            'reordered' => 'Work Groups reordered',
            'inactivated' => 'Work Group inactivated',
        ],
        'membership' => [
            'added' => 'Project member added',
            'removed' => 'Project member removed',
        ],
    ],
];
