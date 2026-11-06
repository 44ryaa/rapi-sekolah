<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $table = 'roles';

    protected $fillable = ['role_name'];

    const ADMIN = 1;

    const PIC = 2;

    const GURU = 3;

    const WAKA = 4;

    const ORGANISASI = 5;
}
