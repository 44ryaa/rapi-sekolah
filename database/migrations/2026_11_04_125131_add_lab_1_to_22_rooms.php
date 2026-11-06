<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultPic = User::where('role_id', 2)->first()?->id ?? null;

        // 1. Rename existing rooms to match the clean Lab 1 - 22 standard
        Room::where('room_name', 'Lab Komputer 1')->update(['room_name' => 'Lab 1']);
        Room::where('room_name', 'Lab Komputer 2')->update(['room_name' => 'Lab 2']);
        Room::where('id', 8)->update(['room_name' => 'Lab 19 (Laboratorium Rekayasa Perangkat Lunak)']);

        // 2. Ensure all Lab 1 to Lab 22 exist
        for ($i = 1; $i <= 22; $i++) {
            $labName = $i === 19 ? 'Lab 19 (Laboratorium Rekayasa Perangkat Lunak)' : "Lab {$i}";

            $exists = Room::where('room_name', $labName)
                ->orWhere('room_name', "Lab {$i}")
                ->exists();

            if (! $exists) {
                // Determine realistic floor and building in Indonesian vocational school
                $location = match (true) {
                    $i === 19 => 'Gedung Barat, Lantai 1 (Lab RPL)',
                    $i <= 6 => 'Gedung Lab Komputer, Lantai 1',
                    $i <= 14 => 'Gedung Lab Komputer, Lantai 2',
                    default => 'Gedung Lab Komputer, Lantai 3',
                };

                Room::create([
                    'room_name' => $labName,
                    'location' => $location,
                    'capacity' => 36,
                    'pic_id' => $defaultPic,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep core rooms, only delete additional labs if rolled back
        for ($i = 3; $i <= 22; $i++) {
            if ($i !== 19) {
                Room::where('room_name', "Lab {$i}")->delete();
            }
        }
    }
};
