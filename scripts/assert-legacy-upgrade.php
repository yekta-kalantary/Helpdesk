<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'helpdesk_testing';
$username = getenv('DB_USERNAME') ?: 'helpdesk';
$password = getenv('DB_PASSWORD') ?: 'helpdesk';

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$fail = static function (string $message): never {
    fwrite(STDERR, "Legacy upgrade assertion failed: {$message}\n");
    exit(1);
};

$tableExists = static function (string $table) use ($pdo): bool {
    $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $statement->execute([$table]);

    return (int) $statement->fetchColumn() === 1;
};

$columnExists = static function (string $table, string $column) use ($pdo): bool {
    $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $statement->execute([$table, $column]);

    return (int) $statement->fetchColumn() === 1;
};

foreach (['contacts', 'users', 'projects', 'tasks', 'media'] as $table) {
    $tableExists($table) || $fail("expected {$table} table to exist");
}

foreach (['people', 'customers', 'tickets', 'ticket_messages', 'settings', 'notifications'] as $table) {
    ! $tableExists($table) || $fail("legacy {$table} table still exists");
}

$columnExists('users', 'contact_id') || $fail('users.contact_id is missing');
! $columnExists('users', 'person_id') || $fail('users.person_id still exists');
$columnExists('projects', 'contact_id') || $fail('projects.contact_id is missing');
! $columnExists('projects', 'customer_id') || $fail('projects.customer_id still exists');
! $columnExists('tasks', 'is_customer_visible') || $fail('tasks.is_customer_visible still exists');

$contact = $pdo->query("SELECT id, email FROM contacts WHERE id = 2")->fetch(PDO::FETCH_ASSOC);
if (! $contact || $contact['email'] !== 'legacy-customer@example.test') {
    $fail('legacy customer person was not preserved as contact id 2');
}

$user = $pdo->query('SELECT contact_id FROM users WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (! $user || (int) $user['contact_id'] !== 1) {
    $fail('legacy user was not mapped to contact id 1');
}

$project = $pdo->query('SELECT contact_id, category FROM projects WHERE id = 20')->fetch(PDO::FETCH_ASSOC);
if (! $project || (int) $project['contact_id'] !== 2 || $project['category'] !== 'contact') {
    $fail('legacy project was not mapped to contact id 2/category contact');
}

$roleMorph = $pdo->query('SELECT model_type FROM model_has_roles WHERE role_id = 1 AND model_id = 1')->fetchColumn();
if ($roleMorph !== 'Modules\\Identity\\Infrastructure\\Models\\User') {
    $fail('Spatie role morph was not migrated to the module-owned User model');
}

$customerRoleCount = (int) $pdo->query("SELECT COUNT(*) FROM roles WHERE name = 'customer' AND guard_name = 'web'")->fetchColumn();
$customerRoleCount === 0 || $fail('legacy customer role still exists');

$legacyPermissionCount = (int) $pdo->query("SELECT COUNT(*) FROM permissions WHERE name LIKE 'customers.%' OR name LIKE 'tickets.%' OR name LIKE 'reports.%' OR name LIKE 'settings.%' OR name = 'notifications.view'")->fetchColumn();
$legacyPermissionCount === 0 || $fail('legacy removed-module permissions still exist');

$taskMedia = (int) $pdo->query("SELECT COUNT(*) FROM media WHERE id = 50 AND model_type = 'Modules\\\\Tasks\\\\Infrastructure\\\\Models\\\\Task'")->fetchColumn();
$taskMedia === 1 || $fail('task media was not preserved');

$ticketMedia = (int) $pdo->query('SELECT COUNT(*) FROM media WHERE id = 51')->fetchColumn();
$ticketMedia === 0 || $fail('removed ticket media still exists');

echo "Legacy database upgrade assertions passed.\n";
