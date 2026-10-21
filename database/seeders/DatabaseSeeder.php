<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Room;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────
        Role::insert([
            ['id' => 1, 'role_name' => 'Admin'],
            ['id' => 2, 'role_name' => 'PIC Ruang'],
            ['id' => 3, 'role_name' => 'Guru'],
            ['id' => 4, 'role_name' => 'Waka'],
        ]);

        // ── Users ─────────────────────────────────────────────────────────
        $admin = User::create([
            'username'     => 'admin',
            'email'        => 'admin@rapi-sekolah.local',
            'password'     => Hash::make('admin123'),
            'fullname'     => 'Administrator',
            'phone_number' => '08111000001',
            'role_id'      => Role::ADMIN,
        ]);

        $waka = User::create([
            'username'     => 'waka',
            'email'        => 'waka@rapi-sekolah.local',
            'password'     => Hash::make('waka123'),
            'fullname'     => 'Waka Sarpras (Drs. Hendra)',
            'phone_number' => '08111000009',
            'role_id'      => Role::WAKA,
        ]);

        $pic1 = User::create([
            'username'     => 'pic_aula',
            'email'        => 'pic_aula@rapi-sekolah.local',
            'password'     => Hash::make('pic123'),
            'fullname'     => 'Budi Santoso',
            'phone_number' => '08111000002',
            'role_id'      => Role::PIC,
        ]);

        $pic2 = User::create([
            'username'     => 'pic_lab',
            'email'        => 'pic_lab@rapi-sekolah.local',
            'password'     => Hash::make('pic123'),
            'fullname'     => 'Sari Dewi',
            'phone_number' => '08111000003',
            'role_id'      => Role::PIC,
        ]);

        User::create([
            'username'     => 'guru1',
            'email'        => 'guru1@rapi-sekolah.local',
            'password'     => Hash::make('guru123'),
            'fullname'     => 'Ahmad Fauzi',
            'phone_number' => '08111000004',
            'role_id'      => Role::GURU,
        ]);

        User::create([
            'username'     => 'guru2',
            'email'        => 'guru2@rapi-sekolah.local',
            'password'     => Hash::make('guru123'),
            'fullname'     => 'Rina Marlina',
            'phone_number' => '08111000005',
            'role_id'      => Role::GURU,
        ]);

        // ── Rooms ─────────────────────────────────────────────────────────
        Room::insert([
            [
                'room_name'  => 'Aula Utama',
                'location'   => 'Gedung A, Lantai 1',
                'capacity'   => 300,
                'pic_id'     => $pic1->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name'  => 'Ruang Rapat',
                'location'   => 'Gedung A, Lantai 2',
                'capacity'   => 30,
                'pic_id'     => $pic1->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name'  => 'Lab Komputer 1',
                'location'   => 'Gedung B, Lantai 1',
                'capacity'   => 40,
                'pic_id'     => $pic2->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name'  => 'Lab Komputer 2',
                'location'   => 'Gedung B, Lantai 1',
                'capacity'   => 40,
                'pic_id'     => $pic2->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name'  => 'Lab Bahasa',
                'location'   => 'Gedung B, Lantai 2',
                'capacity'   => 35,
                'pic_id'     => $pic2->id,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name'  => 'Ruang Multimedia',
                'location'   => 'Gedung C, Lantai 1',
                'capacity'   => 50,
                'pic_id'     => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name'  => 'Lapangan Olahraga',
                'location'   => 'Area Outdoor',
                'capacity'   => 200,
                'pic_id'     => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
