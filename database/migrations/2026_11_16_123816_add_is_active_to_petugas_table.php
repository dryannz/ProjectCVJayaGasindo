<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('petugas') || Schema::hasColumn('petugas', 'is_active')) {
            return;
        }

        Schema::table('petugas', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('petugas') || ! Schema::hasColumn('petugas', 'is_active')) {
            return;
        }

        Schema::table('petugas', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
