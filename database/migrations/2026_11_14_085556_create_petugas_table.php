<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('petugas')) {
            return;
        }

        Schema::create('petugas', function (Blueprint $table): void {
            $table->string('idpetugas', 20)->primary();
            $table->string('namapetugas');
            $table->string('notelp', 20);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role', 50)->default('admin_sales');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // The table may be provisioned manually, so a rollback must not delete it.
    }
};
