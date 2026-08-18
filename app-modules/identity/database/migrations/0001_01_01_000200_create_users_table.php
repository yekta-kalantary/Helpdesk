<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->restrictOnDelete();
            $table->string('role', 20)->default('customer')->index();
            $table->string('name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('mobile', 32)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
