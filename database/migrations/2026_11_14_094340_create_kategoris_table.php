<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kategori')) {
            return;
        }

        Schema::create('kategori', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // The table may be provisioned manually, so a rollback must not delete it.
    }
};
