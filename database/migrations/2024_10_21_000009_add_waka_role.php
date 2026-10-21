<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('roles')->where('id', Role::WAKA)->doesntExist()) {
            DB::table('roles')->insert([
                'id' => Role::WAKA,
                'role_name' => 'Waka',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('id', Role::WAKA)->delete();
    }
};
