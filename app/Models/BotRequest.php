<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date',
    ];

    const TYPE_LATE_30M = 0;
    const TYPE_LATE_1h = 1;
    const TYPE_OFF = 2;
    const TYPE_MANUAL = 3;
}
