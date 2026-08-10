<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contacts')) {
            return;
        }

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender', 20)->nullable()->index();
            $table->string('email')->unique();
            $table->string('mobile', 32)->index();
            $table->string('province')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('postal_code', 20)->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
