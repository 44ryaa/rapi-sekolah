<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar');
    }

    public function api(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return response()->json(['error' => 'Format bulan tidak valid'], 400);
        }

        $events = Event::with('room')
            ->whereIn('status', ['Disetujui', 'Diajukan'])
            ->where('date', 'like', "{$month}%")
            ->orderBy('date')->orderBy('start_time')
            ->get()
            ->map(fn($e) => [
                'id'      => $e->id,
                'name'    => $e->event_name,
                'date'    => $e->date->format('Y-m-d'),
                'start'   => substr($e->start_time, 0, 5),
                'end'     => substr($e->end_time, 0, 5),
                'status'  => $e->status,
                'room_id' => $e->room_id,
                'room'    => $e->room->room_name,
            ]);

        return response()->json(['month' => $month, 'events' => $events]);
    }
}
