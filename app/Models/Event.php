<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'room_id', 'pic_id', 'event_name', 'date', 'start_time', 'end_time',
        'status', 'approved_by', 'move_reason', 'reject_reason',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Check if this event's time conflicts with another in the same room/date. */
    public static function hasConflict(int $roomId, string $date, string $start, string $end, int $ignoreId = 0): ?self
    {
        return self::where('room_id', $roomId)
            ->where('date', $date)
            ->whereIn('status', ['Diajukan', 'Disetujui'])
            ->where('id', '<>', $ignoreId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->first();
    }
}
