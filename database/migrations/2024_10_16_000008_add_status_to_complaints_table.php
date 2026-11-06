<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->enum('status', ['Diajukan', 'Diterima', 'Ditolak'])
                ->default('Diajukan')
                ->after('photo_path');
            $table->text('reject_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['status', 'reject_reason']);
        });
    }
};
