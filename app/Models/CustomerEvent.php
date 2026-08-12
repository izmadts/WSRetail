<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'venue',
        'status',
        'attendees'
    ];

    protected $casts = [
        'event_date' => 'date',
        'attendees' => 'array'
    ];

    public function getStatusLabelAttribute()
    {
        $labels = [
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'upcoming' => 'bg-blue-100 text-blue-800',
            'ongoing' => 'bg-green-100 text-green-800',
            'completed' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
