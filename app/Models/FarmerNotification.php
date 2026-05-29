<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'type',
        'title',
        'message',
        'icon',
        'icon_color',
        'link',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Notification types
     */
    const TYPE_CROP_PLAN = 'crop_plan';
    const TYPE_PLANTING_REMINDER = 'planting_reminder';
    const TYPE_HARVEST_REMINDER = 'harvest_reminder';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_LGU_VALIDATION = 'lgu_validation';

    /**
     * Get the farmer that owns this notification
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for crop plan related notifications only
     */
    public function scopeCropPlanRelated($query)
    {
        return $query->whereIn('type', [
            self::TYPE_CROP_PLAN,
            self::TYPE_PLANTING_REMINDER,
            self::TYPE_HARVEST_REMINDER,
            self::TYPE_LGU_VALIDATION,
        ]);
    }

    /**
     * Scope for all notification types (including announcements)
     */
    public function scopeAllTypes($query)
    {
        return $query;
    }

    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Mark the notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Get human-readable time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the icon SVG path based on type
     */
    public function getIconSvgAttribute(): string
    {
        return match($this->icon ?? $this->type) {
            'clock', self::TYPE_HARVEST_REMINDER => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z',
            'plant', self::TYPE_PLANTING_REMINDER, self::TYPE_CROP_PLAN => 'M12 19V6M12 6c-2 0-4-1-5-3M12 6c2 0 4-1 5-3M7 14c-2 1-3 3-3 5M17 14c2 1 3 3 3 5',
            self::TYPE_LGU_VALIDATION, 'shield' => 'M10 2l6 3v4c0 3.866-2.239 7.36-6 9-3.761-1.64-6-5.134-6-9V5l6-3zm2.707 6.293a1 1 0 00-1.414 0L9 10.586 8.207 9.793a1 1 0 00-1.414 1.414l1.5 1.5a1 1 0 001.414 0l3-3a1 1 0 000-1.414z',
            self::TYPE_ANNOUNCEMENT, 'announcement', 'megaphone' => 'M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z',
            default => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        };
    }

    /**
     * Get icon background color class
     */
    public function getIconBgClassAttribute(): string
    {
        return match($this->icon_color) {
            'green' => 'bg-green-100',
            'amber', 'yellow' => 'bg-amber-100',
            'blue' => 'bg-blue-100',
            'red' => 'bg-red-100',
            'orange' => 'bg-orange-100',
            default => 'bg-gray-100',
        };
    }

    /**
     * Get icon text color class
     */
    public function getIconTextClassAttribute(): string
    {
        return match($this->icon_color) {
            'green' => 'text-green-600',
            'amber', 'yellow' => 'text-amber-600',
            'blue' => 'text-blue-600',
            'red' => 'text-red-600',
            'orange' => 'text-orange-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Create a crop plan notification with harvest reminder info
     */
    public static function createCropPlanNotification(Farmer $farmer, CropPlan $cropPlan, int $daysToHarvest = null): self
    {
        $days = $daysToHarvest ?? $cropPlan->planting_date->diffInDays($cropPlan->expected_harvest_date);
        
        return self::create([
            'farmer_id' => $farmer->id,
            'type' => self::TYPE_CROP_PLAN,
            'title' => 'Crop Plan Created',
            'message' => "You've planned to plant {$cropPlan->crop_name} on {$cropPlan->planting_date->format('M d, Y')}. Growth period: {$days} days. Expected harvest date: {$cropPlan->expected_harvest_date->format('M d, Y')}. Predicted production: {$cropPlan->formatted_production}",
            'icon' => 'plant',
            'icon_color' => 'green',
            'link' => route('farmers.calendar'),
            'data' => [
                'crop_plan_id' => $cropPlan->id,
                'crop_name' => $cropPlan->crop_name,
                'planting_date' => $cropPlan->planting_date->format('Y-m-d'),
                'harvest_date' => $cropPlan->expected_harvest_date->format('Y-m-d'),
                'days_to_harvest' => $days,
                'predicted_production' => $cropPlan->predicted_production,
            ],
        ]);
    }

    /**
     * Create a scheduled harvest reminder (to be shown on harvest date)
     */
    public static function createScheduledHarvestReminder(Farmer $farmer, CropPlan $cropPlan): self
    {
        return self::create([
            'farmer_id' => $farmer->id,
            'type' => self::TYPE_HARVEST_REMINDER,
            'title' => 'Harvest Day!',
            'message' => "🎉 Today is harvest day for your {$cropPlan->crop_name}! You planted on {$cropPlan->planting_date->format('M d, Y')} ({$cropPlan->planting_date->diffInDays($cropPlan->expected_harvest_date)} days growth). Predicted production: {$cropPlan->formatted_production}",
            'icon' => 'clock',
            'icon_color' => 'amber',
            'link' => route('farmers.calendar'),
            'data' => [
                'crop_plan_id' => $cropPlan->id,
                'crop_name' => $cropPlan->crop_name,
                'planting_date' => $cropPlan->planting_date->format('Y-m-d'),
                'harvest_date' => $cropPlan->expected_harvest_date->format('Y-m-d'),
                'predicted_production' => $cropPlan->predicted_production,
                'is_harvest_day' => true,
            ],
        ]);
    }

    /**
     * Create a planting reminder notification
     */
    public static function createPlantingReminder(Farmer $farmer, CropPlan $cropPlan): self
    {
        return self::create([
            'farmer_id' => $farmer->id,
            'type' => self::TYPE_PLANTING_REMINDER,
            'title' => 'Planting Reminder',
            'message' => "Time to plant {$cropPlan->crop_name}! Your scheduled planting date is today.",
            'icon' => 'plant',
            'icon_color' => 'green',
            'link' => route('farmers.calendar'),
            'data' => [
                'crop_plan_id' => $cropPlan->id,
                'crop_name' => $cropPlan->crop_name,
            ],
        ]);
    }

    /**
     * Create a harvest reminder notification
     */
    public static function createHarvestReminder(Farmer $farmer, CropPlan $cropPlan): self
    {
        return self::create([
            'farmer_id' => $farmer->id,
            'type' => self::TYPE_HARVEST_REMINDER,
            'title' => 'Harvest Reminder',
            'message' => "Your {$cropPlan->crop_name} is ready for harvest! Predicted production: {$cropPlan->formatted_production}",
            'icon' => 'clock',
            'icon_color' => 'amber',
            'link' => route('farmers.calendar'),
            'data' => [
                'crop_plan_id' => $cropPlan->id,
                'crop_name' => $cropPlan->crop_name,
                'predicted_production' => $cropPlan->predicted_production,
            ],
        ]);
    }
}
