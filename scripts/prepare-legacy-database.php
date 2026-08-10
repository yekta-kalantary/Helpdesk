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

$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $pdo->exec('DROP TABLE IF EXISTS `'.str_replace('`', '``', (string) $table).'`');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

$statements = [
    <<<'SQL'
CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE people (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mobile VARCHAR(32) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX people_type_index (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX users_is_active_index (is_active),
    CONSTRAINT users_person_id_foreign FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL UNIQUE,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT customers_person_id_foreign FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY permissions_name_guard_name_unique (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    INDEX model_has_permissions_model_id_model_type_index (model_id, model_type),
    CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    INDEX model_has_roles_model_id_model_type_index (model_id, model_type),
    CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NULL,
    category VARCHAR(20) NOT NULL DEFAULT 'customer',
    title VARCHAR(255) NOT NULL,
    type VARCHAR(40) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'planning',
    description TEXT NULL,
    starts_at DATE NULL,
    ends_at DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX projects_category_index (category),
    INDEX projects_type_index (type),
    INDEX projects_status_index (status),
    CONSTRAINT projects_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE project_user (
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (project_id, user_id),
    CONSTRAINT project_user_project_id_foreign FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT project_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    priority VARCHAR(20) NOT NULL DEFAULT 'medium',
    status VARCHAR(20) NOT NULL DEFAULT 'todo',
    is_customer_visible TINYINT(1) NOT NULL DEFAULT 0,
    due_at TIMESTAMP NULL,
    estimated_minutes INT UNSIGNED NULL,
    spent_minutes INT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX tasks_is_customer_visible_index (is_customer_visible),
    INDEX tasks_priority_index (priority),
    INDEX tasks_status_index (status),
    INDEX tasks_due_at_index (due_at),
    INDEX tasks_project_id_status_index (project_id, status),
    INDEX tasks_assigned_to_status_index (assigned_to, status),
    CONSTRAINT tasks_project_id_foreign FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT tasks_assigned_to_foreign FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT tasks_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE task_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT task_comments_task_id_foreign FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT task_comments_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    uuid CHAR(36) NULL UNIQUE,
    collection_name VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NULL,
    disk VARCHAR(255) NOT NULL,
    conversions_disk VARCHAR(255) NULL,
    size BIGINT UNSIGNED NOT NULL,
    manipulations JSON NOT NULL,
    custom_properties JSON NOT NULL,
    generated_conversions JSON NOT NULL,
    responsive_images JSON NOT NULL,
    order_column INT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX media_model_type_model_id_index (model_type, model_id),
    INDEX media_order_column_index (order_column)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    subject VARCHAR(255) NOT NULL,
    category VARCHAR(30) NOT NULL DEFAULT 'general',
    priority VARCHAR(20) NOT NULL DEFAULT 'medium',
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT tickets_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    CONSTRAINT tickets_project_id_foreign FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT tickets_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT tickets_assigned_to_foreign FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE ticket_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT ticket_messages_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT ticket_messages_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX notifications_notifiable_type_notifiable_id_index (notifiable_type, notifiable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    locked TINYINT(1) NOT NULL DEFAULT 0,
    payload JSON NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY settings_group_name_unique (`group`, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
];

foreach ($statements as $statement) {
    $pdo->exec($statement);
}

$now = date('Y-m-d H:i:s');
$hash = password_hash('password', PASSWORD_BCRYPT);

$pdo->prepare('INSERT INTO people (id, type, first_name, last_name, email, mobile, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, ?), (2, ?, ?, ?, ?, ?, ?, ?)')->execute([
    'employee', 'Legacy', 'Admin', 'legacy-admin@example.test', '09120000001', $now, $now,
    'customer', 'Legacy', 'Customer', 'legacy-customer@example.test', '09120000002', $now, $now,
]);
$pdo->prepare('INSERT INTO users (id, person_id, password, is_active, created_at, updated_at) VALUES (1, 1, ?, 1, ?, ?)')->execute([$hash, $now, $now]);
$pdo->prepare('INSERT INTO customers (id, person_id, status, created_at, updated_at) VALUES (10, 2, ?, ?, ?)')->execute(['active', $now, $now]);

$pdo->prepare('INSERT INTO roles (id, name, guard_name, created_at, updated_at) VALUES (1, ?, ?, ?, ?), (2, ?, ?, ?, ?)')->execute([
    'admin', 'web', $now, $now,
    'customer', 'web', $now, $now,
]);
$pdo->prepare('INSERT INTO permissions (id, name, guard_name, created_at, updated_at) VALUES (1, ?, ?, ?, ?), (2, ?, ?, ?, ?)')->execute([
    'customers.view', 'web', $now, $now,
    'tickets.view', 'web', $now, $now,
]);
$pdo->exec("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (1, 'App\\\\Models\\\\User', 1)");
$pdo->exec('INSERT INTO role_has_permissions (permission_id, role_id) VALUES (1, 1), (2, 2)');

$pdo->prepare('INSERT INTO projects (id, customer_id, category, title, type, status, created_at, updated_at) VALUES (20, 10, ?, ?, ?, ?, ?, ?)')->execute([
    'customer', 'Legacy project', 'other', 'planning', $now, $now,
]);
$pdo->exec('INSERT INTO project_user (project_id, user_id, created_at, updated_at) VALUES (20, 1, NOW(), NOW())');
$pdo->prepare('INSERT INTO tasks (id, project_id, title, assigned_to, created_by, priority, status, is_customer_visible, created_at, updated_at) VALUES (30, 20, ?, 1, 1, ?, ?, 1, ?, ?)')->execute([
    'Legacy task', 'medium', 'todo', $now, $now,
]);
$pdo->exec("INSERT INTO tickets (id, customer_id, project_id, created_by, subject, created_at, updated_at) VALUES (40, 10, 20, 1, 'Legacy ticket', NOW(), NOW())");
$pdo->exec("INSERT INTO ticket_messages (id, ticket_id, user_id, body, created_at, updated_at) VALUES (41, 40, 1, 'Legacy reply', NOW(), NOW())");
$pdo->exec("INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES ('00000000-0000-0000-0000-000000000001', 'legacy', 'App\\\\Models\\\\User', 1, '{}', NOW(), NOW())");
$pdo->exec("INSERT INTO settings (`group`, name, locked, payload, created_at, updated_at) VALUES ('smtp', 'host', 0, '{}', NOW(), NOW())");
$pdo->exec("INSERT INTO media (id, model_type, model_id, collection_name, name, file_name, disk, size, manipulations, custom_properties, generated_conversions, responsive_images, created_at, updated_at) VALUES (50, 'Modules\\\\Tasks\\\\Infrastructure\\\\Models\\\\Task', 30, 'attachments', 'task-file', 'task-file.txt', 'local', 1, '{}', '{}', '{}', '{}', NOW(), NOW()), (51, 'Modules\\\\Tickets\\\\Infrastructure\\\\Models\\\\Ticket', 40, 'attachments', 'ticket-file', 'ticket-file.txt', 'local', 1, '{}', '{}', '{}', '{}', NOW(), NOW())");

$migrations = [
    '0001_01_01_000000_create_users_table',
    '2026_08_07_000100_create_permission_tables',
    '2026_08_07_000110_create_notifications_table',
    '2026_08_07_001000_create_customers_table',
    '2026_08_07_002000_create_projects_tables',
    '2026_08_07_003000_create_tasks_tables',
    '2026_08_07_003010_create_media_table',
    '2026_08_07_004000_create_tickets_tables',
    '2026_08_07_005000_create_settings_table',
    '2026_08_07_006000_move_profiles_to_people_table',
    '2026_08_07_205500_add_category_to_projects_table',
];

$insertMigration = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, 1)');
foreach ($migrations as $migration) {
    $insertMigration->execute([$migration]);
}

echo "Legacy database fixture prepared.\n";
