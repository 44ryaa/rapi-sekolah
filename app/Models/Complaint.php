<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'reported_at';

    protected $fillable = [
        'room_id', 'reporter_id', 'description', 'photo_path',
        'status', 'reject_reason',
        'status_repair', 'handler_id', 'repair_note',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }
}
