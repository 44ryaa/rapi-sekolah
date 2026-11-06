<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
