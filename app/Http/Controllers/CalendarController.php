<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use App\Services\IndonesianHolidayService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');

        // Ruang yang ada jadwal hari ini
        $bookedRoomIds = Event::where('date', $today)
            ->whereIn('status', ['Diajukan', 'Disetujui'])
            ->pluck('room_id')
            ->unique();

        // Ruang yang kosong / bebas jadwal hari ini
        $availableRooms = Room::where('is_active', 1)
            ->whereNotIn('id', $bookedRoomIds)
            ->orderByRaw('LENGTH(room_name), room_name')
            ->get();

        $occupiedRoomsCount = $bookedRoomIds->count();
        $totalRoomsCount = Room::where('is_active', 1)->count();

        return view('calendar', compact('availableRooms', 'occupiedRoomsCount', 'totalRoomsCount'));
    }

    public function api(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return response()->json(['error' => 'Format bulan tidak valid'], 400);
        }

        $events = Event::with('room')
            ->whereIn('status', ['Disetujui', 'Diajukan'])
            ->where('date', 'like', "{$month}%")
            ->orderBy('date')->orderBy('start_time')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->event_name,
                'date' => $e->date->format('Y-m-d'),
                'start' => substr($e->start_time, 0, 5),
                'end' => substr($e->end_time, 0, 5),
                'status' => $e->status,
                'room_id' => $e->room_id,
                'room' => $e->room->room_name,
            ]);

        $holidays = IndonesianHolidayService::getHolidaysForMonth($month);

        return response()->json([
            'month' => $month,
            'events' => $events,
            'holidays' => $holidays,
        ]);
    }
}
