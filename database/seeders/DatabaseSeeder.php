<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────
        Role::upsert([
            ['id' => 1, 'role_name' => 'Admin'],
            ['id' => 2, 'role_name' => 'PIC Ruang'],
            ['id' => 3, 'role_name' => 'Guru'],
            ['id' => 4, 'role_name' => 'Waka'],
            ['id' => 5, 'role_name' => 'Organisasi'],
        ], ['id'], ['role_name']);

        // ── Users ─────────────────────────────────────────────────────────
        $admin = User::create([
            'username' => 'admin',
            'email' => 'aryasatriawicaksono33@gmail.com',
            'password' => Hash::make('admin123'),
            'fullname' => 'Administrator',
            'phone_number' => '0895611029000',
            'role_id' => Role::ADMIN,
        ]);

        $waka = User::create([
            'username' => 'waka',
            'email' => 'waka@rapi-sekolah.local',
            'password' => Hash::make('waka123'),
            'fullname' => 'Waka Sarpras (Drs. Hendra)',
            'phone_number' => '0895611029000',
            'role_id' => Role::WAKA,
        ]);

        $pic1 = User::create([
            'username' => 'pic_aula',
            'email' => 'pic_aula@rapi-sekolah.local',
            'password' => Hash::make('pic123'),
            'fullname' => 'Budi Santoso',
            'phone_number' => '0895611029000',
            'role_id' => Role::PIC,
        ]);

        $pic2 = User::create([
            'username' => 'pic_lab',
            'email' => 'pic_lab@rapi-sekolah.local',
            'password' => Hash::make('pic123'),
            'fullname' => 'Sari Dewi',
            'phone_number' => '0895611029000',
            'role_id' => Role::PIC,
        ]);

        $picLega = User::create([
            'username' => 'lega',
            'email' => 'lega@rapi-sekolah.local',
            'password' => Hash::make('pic123'),
            'fullname' => 'Lega Danu Kumara',
            'phone_number' => '0895611029000',
            'role_id' => Role::PIC,
        ]);

        $picWahid = User::create([
            'username' => 'wahid',
            'email' => 'wahid@rapi-sekolah.local',
            'password' => Hash::make('pic123'),
            'fullname' => 'Wahid Abduraman',
            'phone_number' => '0895611029000',
            'role_id' => Role::PIC,
        ]);

        $picHeri = User::create([
            'username' => 'heri',
            'email' => 'heri@rapi-sekolah.local',
            'password' => Hash::make('pic123'),
            'fullname' => 'Heri Prasetyo',
            'phone_number' => '0895611029000',
            'role_id' => Role::PIC,
        ]);

        $picArif = User::create([
            'username' => 'arif',
            'email' => 'arif@rapi-sekolah.local',
            'password' => Hash::make('pic123'),
            'fullname' => 'Arif Kurniawan',
            'phone_number' => '0895611029000',
            'role_id' => Role::PIC,
        ]);

        User::create([
            'username' => 'guru1',
            'email' => 'guru1@rapi-sekolah.local',
            'password' => Hash::make('guru123'),
            'fullname' => 'Ahmad Fauzi',
            'phone_number' => '0895611029000',
            'role_id' => Role::GURU,
        ]);

        User::create([
            'username' => 'guru2',
            'email' => 'guru2@rapi-sekolah.local',
            'password' => Hash::make('guru123'),
            'fullname' => 'Rina Marlina',
            'phone_number' => '0895611029000',
            'role_id' => Role::GURU,
        ]);

        User::create([
            'username' => 'osis',
            'email' => 'osis@rapi-sekolah.local',
            'password' => Hash::make('password'),
            'fullname' => 'Pengurus OSIS',
            'phone_number' => '0895611029000',
            'role_id' => Role::ORGANISASI,
        ]);

        User::create([
            'username' => 'pramuka',
            'email' => 'pramuka@rapi-sekolah.local',
            'password' => Hash::make('password'),
            'fullname' => 'Gerakan Pramuka',
            'phone_number' => '0895611029000',
            'role_id' => Role::ORGANISASI,
        ]);

        // ── Rooms ─────────────────────────────────────────────────────────
        $rooms = [
            [
                'room_name' => 'Aula Utama',
                'location' => 'Gedung A, Lantai 1',
                'capacity' => 300,
                'pic_id' => $pic1->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name' => 'Ruang Rapat',
                'location' => 'Gedung A, Lantai 2',
                'capacity' => 30,
                'pic_id' => $pic1->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name' => 'Lab Bahasa',
                'location' => 'Gedung B, Lantai 2',
                'capacity' => 35,
                'pic_id' => $pic2->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name' => 'Ruang Multimedia',
                'location' => 'Gedung C, Lantai 1',
                'capacity' => 50,
                'pic_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'room_name' => 'Lapangan Olahraga',
                'location' => 'Area Outdoor',
                'capacity' => 200,
                'pic_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Seed Lab 1 through Lab 22
        for ($i = 1; $i <= 22; $i++) {
            $labName = $i === 19 ? 'Lab 19 (Laboratorium Rekayasa Perangkat Lunak)' : "Lab {$i}";
            $location = match (true) {
                $i === 19 => 'Gedung Barat, Lantai 1 (Lab RPL)',
                $i <= 6 => 'Gedung Lab Komputer, Lantai 1',
                $i <= 14 => 'Gedung Lab Komputer, Lantai 2',
                default => 'Gedung Lab Komputer, Lantai 3',
            };

            $rooms[] = [
                'room_name' => $labName,
                'location' => $location,
                'capacity' => 36,
                'pic_id' => $pic2->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_name' => $room['room_name']],
                $room
            );
        }
    }
}
