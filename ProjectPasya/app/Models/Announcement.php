<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'priority',
        'target_audience',
        'municipality',
        'created_by',
        'published_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created the announcement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active announcements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for farmer-visible announcements
     */
    public function scopeForFarmers($query)
    {
        return $query->whereIn('target_audience', ['all', 'farmers']);
    }

    /**
     * Scope for specific municipality or all
     */
    public function scopeForMunicipality($query, $municipality = null)
    {
        return $query->where(function ($q) use ($municipality) {
            $q->whereNull('municipality')
                ->orWhere('municipality', $municipality);
        });
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute()
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'gray',
        };
    }
}
