<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class RoomApiController extends Controller
{
    /**
     * GET /api/rooms
     * Query: ?active=1 (default hanya ruangan aktif)
     */
    public function index(Request $request)
    {
        $query = Room::with('pic:id,fullname,username');

        if ($request->boolean('active', true)) {
            $query->where('is_active', 1);
        }

        $rooms = $query->orderBy('room_name')->get()->map(fn ($r) => $this->roomResource($r));

        return response()->json(['data' => $rooms]);
    }

    /**
     * GET /api/rooms/{id}
     */
    public function show(int $id)
    {
        $room = Room::with('pic:id,fullname,username')->findOrFail($id);

        return response()->json(['data' => $this->roomResource($room)]);
    }

    /**
     * POST /api/rooms  [Admin only]
     */
    public function store(Request $request)
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'room_name' => 'required|string|max:100|unique:rooms,room_name',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:100',
            'pic_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        if (isset($data['pic_id']) && ! User::where('id', $data['pic_id'])->where('role_id', Role::PIC)->exists()) {
            return response()->json(['message' => 'PIC harus pengguna ber-role PIC Ruang.'], 422);
        }

        $room = Room::create($data);

        return response()->json(['data' => $this->roomResource($room->load('pic')), 'message' => 'Ruangan berhasil dibuat.'], 201);
    }

    /**
     * PUT /api/rooms/{id}  [Admin only]
     */
    public function update(Request $request, int $id)
    {
        $this->requireAdmin($request);

        $room = Room::findOrFail($id);

        $data = $request->validate([
            'room_name' => "sometimes|string|max:100|unique:rooms,room_name,{$id}",
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:100',
            'pic_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $room->update($data);

        return response()->json(['data' => $this->roomResource($room->fresh('pic')), 'message' => 'Ruangan diperbarui.']);
    }

    private function requireAdmin(Request $request): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengakses endpoint ini.');
        }
    }

    private function roomResource(Room $room): array
    {
        return [
            'id' => $room->id,
            'room_name' => $room->room_name,
            'capacity' => $room->capacity,
            'location' => $room->location,
            'is_active' => (bool) $room->is_active,
            'pic' => $room->pic ? [
                'id' => $room->pic->id,
                'fullname' => $room->pic->fullname,
                'username' => $room->pic->username,
            ] : null,
        ];
    }
}
