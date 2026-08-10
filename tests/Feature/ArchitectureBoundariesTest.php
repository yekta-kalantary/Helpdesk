<?php

use Modules\Media\Domain\Contracts\MediaManager;
use Modules\Media\Infrastructure\SpatieMediaManager;

function phpFilesIn(string $path): array
{
    if (! is_dir($path)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

it('keeps contact and user models inside their owning modules', function (): void {
    expect(file_exists(app_path('Models/Contact.php')))->toBeFalse()
        ->and(file_exists(app_path('Models/User.php')))->toBeFalse()
        ->and(file_exists(base_path('app-modules/contacts/src/Infrastructure/Models/Contact.php')))->toBeTrue()
        ->and(file_exists(base_path('app-modules/identity/src/Infrastructure/Models/User.php')))->toBeTrue();

    $forbidden = [
        'App'.'\\Models\\Contact',
        'App'.'\\Models\\User',
    ];

    $paths = [
        app_path(),
        config_path(),
        database_path('factories'),
        database_path('seeders'),
        ...glob(base_path('app-modules/*/src')),
    ];

    foreach ($paths as $path) {
        foreach (phpFilesIn($path) as $file) {
            $contents = file_get_contents($file);

            foreach ($forbidden as $class) {
                expect($contents, $file)->not->toContain($class);
            }
        }
    }
});

it('keeps Spatie media implementation inside the Media module', function (): void {
    foreach (phpFilesIn(base_path('app-modules/tasks/src')) as $file) {
        expect(file_get_contents($file), $file)->not->toContain('Spatie'.'\\MediaLibrary');
    }

    expect(file_get_contents(base_path('app-modules/tasks/composer.json')))->not->toContain('spatie/laravel-medialibrary')
        ->and(file_get_contents(base_path('app-modules/media/composer.json')))->toContain('spatie/laravel-medialibrary')
        ->and(app(MediaManager::class))->toBeInstanceOf(SpatieMediaManager::class);
});

it('assigns schema creation to owning modules', function (): void {
    expect(file_exists(database_path('migrations/0001_01_01_000000_create_users_table.php')))->toBeFalse()
        ->and(file_exists(base_path('app-modules/contacts/database/migrations/0001_01_01_000000_create_contacts_table.php')))->toBeTrue()
        ->and(file_exists(base_path('app-modules/identity/database/migrations/0001_01_01_000100_create_users_table.php')))->toBeTrue()
        ->and(file_exists(base_path('app-modules/tasks/database/migrations/2026_08_07_003010_create_media_table.php')))->toBeFalse()
        ->and(file_exists(base_path('app-modules/media/database/migrations/2026_08_07_003010_create_media_table.php')))->toBeTrue();
});
