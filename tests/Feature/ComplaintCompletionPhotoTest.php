<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintCompletionPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pic_cannot_mark_done_without_completion_photo(): void
    {
        Storage::fake('public');

        $pic = User::where('role_id', Role::PIC)->first();
        $room = Room::where('pic_id', $pic->id)->first();
        $guru = User::where('role_id', Role::GURU)->first();

        $complaint = Complaint::create([
            'room_id' => $room->id,
            'reporter_id' => $guru->id,
            'description' => 'Monitor LCD blank tidak menampilkan sinyal.',
            'status' => 'Diterima',
            'status_repair' => 'Belum Diperbaiki',
        ]);

        $response = $this->actingAs($pic)->post('/complaints/status', [
            'id' => $complaint->id,
            'status_repair' => 'Sudah Diperbaiki',
            'repair_note' => 'Kabel VGA diganti baru.',
        ]);

        $response->assertSessionHasErrors('completion_photo');

        $complaint->refresh();
        $this->assertEquals('Belum Diperbaiki', $complaint->status_repair);
        $this->assertNull($complaint->completion_photo_path);
    }

    public function test_pic_can_mark_done_with_completion_photo(): void
    {
        Storage::fake('public');

        $pic = User::where('role_id', Role::PIC)->first();
        $room = Room::where('pic_id', $pic->id)->first();
        $guru = User::where('role_id', Role::GURU)->first();

        $complaint = Complaint::create([
            'room_id' => $room->id,
            'reporter_id' => $guru->id,
            'description' => 'Monitor LCD blank tidak menampilkan sinyal.',
            'status' => 'Diterima',
            'status_repair' => 'Belum Diperbaiki',
        ]);

        $fakePhoto = UploadedFile::fake()->create('bukti_selesai.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($pic)->post('/complaints/status', [
            'id' => $complaint->id,
            'status_repair' => 'Sudah Diperbaiki',
            'repair_note' => 'Kabel VGA diganti baru dan layar sudah berfungsi normal.',
            'completion_photo' => $fakePhoto,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $complaint->refresh();
        $this->assertEquals('Sudah Diperbaiki', $complaint->status_repair);
        $this->assertNotNull($complaint->completion_photo_path);
        $this->assertNotNull($complaint->completed_at);
        Storage::disk('public')->assertExists($complaint->completion_photo_path);
    }

    public function test_api_requires_completion_photo_when_marking_done(): void
    {
        Storage::fake('public');

        $pic = User::where('role_id', Role::PIC)->first();
        $room = Room::where('pic_id', $pic->id)->first();
        $guru = User::where('role_id', Role::GURU)->first();

        $complaint = Complaint::create([
            'room_id' => $room->id,
            'reporter_id' => $guru->id,
            'description' => 'Saklar lampu ruang rapat rusak.',
            'status' => 'Diterima',
            'status_repair' => 'Belum Diperbaiki',
        ]);

        $response = $this->actingAs($pic, 'sanctum')->patchJson("/api/complaints/{$complaint->id}/status", [
            'status_repair' => 'Sudah Diperbaiki',
            'repair_note' => 'Saklar diganti baru.',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Wajib melampirkan foto bukti fisik perbaikan untuk status Sudah Diperbaiki.',
        ]);
    }

    public function test_complaint_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $guru = User::where('role_id', Role::GURU)->first();
        $room = Room::first();

        $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($guru)->post('/complaints', [
            'room_id' => $room->id,
            'description' => 'Kerusakan kabel proyektor di ruangan.',
            'photo' => $pdfFile,
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_room_delete_clears_events_and_complaints(): void
    {
        Storage::fake('public');

        $admin = User::where('role_id', Role::ADMIN)->first();
        $guru = User::where('role_id', Role::GURU)->first();
        $room = Room::create([
            'room_name' => 'Lab Demo Hapus',
            'capacity' => 30,
            'is_active' => true,
        ]);

        // Buat event sebelum & setelah
        Event::create([
            'room_id' => $room->id,
            'pic_id' => $guru->id,
            'event_name' => 'Acara Uji Coba Hapus',
            'date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'Disetujui',
        ]);

        // Buat pengaduan dengan file foto
        $photo = UploadedFile::fake()->create('rusak.jpg', 100, 'image/jpeg');
        $complaint = Complaint::create([
            'room_id' => $room->id,
            'reporter_id' => $guru->id,
            'description' => 'Mouse rusak di lab demo.',
            'status' => 'Diterima',
            'status_repair' => 'Belum Diperbaiki',
            'photo_path' => $photo->store('complaints', 'public'),
        ]);

        $roomId = $room->id;
        $complaintPhoto = $complaint->photo_path;

        $response = $this->actingAs($admin)->post("/admin/rooms/{$roomId}/delete");
        $response->assertSessionHas('success');

        // Pastikan ruangan, acara, dan pengaduan terhapus bersih (clear)
        $this->assertDatabaseMissing('rooms', ['id' => $roomId]);
        $this->assertDatabaseMissing('events', ['room_id' => $roomId]);
        $this->assertDatabaseMissing('complaints', ['room_id' => $roomId]);
        Storage::disk('public')->assertMissing($complaintPhoto);
    }
}
