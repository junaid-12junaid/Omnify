<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon; // Import Carbon from the correct namespace

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'start_time', 'end_time', 'max_capacity'];

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }
}