<?php

it('maps every legacy task status deterministically into the project-owned workflow', function (): void {
    $migration = require base_path('app-modules/tasks/database/migrations/2026_08_16_130400_backfill_project_task_workflows.php');
    $statusMethod = new ReflectionMethod($migration, 'legacyStatusKey');
    $completionMethod = new ReflectionMethod($migration, 'completionTimestamp');
    $statusMethod->setAccessible(true);
    $completionMethod->setAccessible(true);

    $updatedAt = '2026-08-15 10:00:00';
    $completedAt = '2026-08-15 11:00:00';

    $cases = [
        ['status' => 'in_progress', 'completed_at' => null, 'expected' => 'in_progress'],
        ['status' => 'completed', 'completed_at' => null, 'expected' => 'done'],
        ['status' => 'todo', 'completed_at' => $completedAt, 'expected' => 'done'],
        ['status' => 'waiting_admin', 'completed_at' => null, 'expected' => 'open'],
        ['status' => 'waiting_customer', 'completed_at' => null, 'expected' => 'open'],
        ['status' => 'cancelled', 'completed_at' => null, 'expected' => 'open'],
    ];

    foreach ($cases as $case) {
        $task = (object) [
            'status' => $case['status'],
            'completed_at' => $case['completed_at'],
            'updated_at' => $updatedAt,
        ];

        expect($statusMethod->invoke($migration, $task))->toBe($case['expected']);
    }

    expect($completionMethod->invoke($migration, (object) [
        'status' => 'completed',
        'completed_at' => null,
        'updated_at' => $updatedAt,
    ]))->toBe($updatedAt)
        ->and($completionMethod->invoke($migration, (object) [
            'status' => 'todo',
            'completed_at' => $completedAt,
            'updated_at' => $updatedAt,
        ]))->toBe($completedAt)
        ->and($completionMethod->invoke($migration, (object) [
            'status' => 'cancelled',
            'completed_at' => null,
            'updated_at' => $updatedAt,
        ]))->toBeNull();
});
