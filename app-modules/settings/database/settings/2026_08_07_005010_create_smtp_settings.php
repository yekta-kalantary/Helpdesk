<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('smtp.enabled', false);
        $this->migrator->add('smtp.host', null);
        $this->migrator->add('smtp.port', 587);
        $this->migrator->add('smtp.username', null);
        $this->migrator->addEncrypted('smtp.password', null);
        $this->migrator->add('smtp.scheme', null);
        $this->migrator->add('smtp.from_address', 'helpdesk@example.com');
        $this->migrator->add('smtp.from_name', 'Helpdesk');
    }
}
