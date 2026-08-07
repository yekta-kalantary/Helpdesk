<?php

namespace Modules\Settings\Infrastructure\Settings;

use Spatie\LaravelSettings\Settings;

class SmtpSettings extends Settings
{
    public bool $enabled;
    public ?string $host;
    public int $port;
    public ?string $username;
    public ?string $password;
    public ?string $scheme;
    public string $from_address;
    public string $from_name;

    public static function group(): string
    {
        return 'smtp';
    }
}
