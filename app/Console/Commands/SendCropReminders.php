<?php

namespace App\Console\Commands;

use App\Models\CropPlan;
use App\Models\FarmerNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendCropReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'crops:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send planting and harvest reminders to farmers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $sentCount = 0;

        // Send planting reminders for today
        $plantingToday = CropPlan::with('farmer')
            ->whereDate('planting_date', $today)
            ->where('status', 'planned')
            ->get();

        foreach ($plantingToday as $plan) {
            // Check if reminder already sent today
            $existingReminder = FarmerNotification::where('farmer_id', $plan->farmer_id)
                ->where('type', FarmerNotification::TYPE_PLANTING_REMINDER)
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->crop_plan_id', $plan->id)
                ->exists();

            if (!$existingReminder) {
                FarmerNotification::createPlantingReminder($plan->farmer, $plan);
                $sentCount++;
                $this->info("Sent planting reminder for {$plan->crop_name} to farmer {$plan->farmer->full_name}");
            }
        }

        // Send harvest reminders for today
        $harvestToday = CropPlan::with('farmer')
            ->whereDate('expected_harvest_date', $today)
            ->whereIn('status', ['planted', 'growing'])
            ->get();

        foreach ($harvestToday as $plan) {
            // Check if reminder already sent today
            $existingReminder = FarmerNotification::where('farmer_id', $plan->farmer_id)
                ->where('type', FarmerNotification::TYPE_HARVEST_REMINDER)
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->crop_plan_id', $plan->id)
                ->exists();

            if (!$existingReminder) {
                FarmerNotification::createScheduledHarvestReminder($plan->farmer, $plan);
                $sentCount++;
                $this->info("Sent harvest reminder for {$plan->crop_name} to farmer {$plan->farmer->full_name}");
            }
        }

        // Also send reminders for tomorrow (advance notice)
        $tomorrow = $today->copy()->addDay();
        
        $plantingTomorrow = CropPlan::with('farmer')
            ->whereDate('planting_date', $tomorrow)
            ->where('status', 'planned')
            ->get();

        foreach ($plantingTomorrow as $plan) {
            $existingReminder = FarmerNotification::where('farmer_id', $plan->farmer_id)
                ->where('type', FarmerNotification::TYPE_PLANTING_REMINDER)
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->crop_plan_id', $plan->id)
                ->exists();

            if (!$existingReminder) {
                FarmerNotification::create([
                    'farmer_id' => $plan->farmer_id,
                    'type' => FarmerNotification::TYPE_PLANTING_REMINDER,
                    'title' => 'Planting Tomorrow',
                    'message' => "Reminder: You're scheduled to plant {$plan->crop_name} tomorrow ({$plan->planting_date->format('M d, Y')}).",
                    'icon' => 'plant',
                    'icon_color' => 'blue',
                    'link' => route('farmers.calendar'),
                    'data' => [
                        'crop_plan_id' => $plan->id,
                        'crop_name' => $plan->crop_name,
                        'is_advance_notice' => true,
                    ],
                ]);
                $sentCount++;
                $this->info("Sent advance planting reminder for {$plan->crop_name} to farmer {$plan->farmer->full_name}");
            }
        }

        $harvestTomorrow = CropPlan::with('farmer')
            ->whereDate('expected_harvest_date', $tomorrow)
            ->whereIn('status', ['planted', 'growing'])
            ->get();

        foreach ($harvestTomorrow as $plan) {
            $existingReminder = FarmerNotification::where('farmer_id', $plan->farmer_id)
                ->where('type', FarmerNotification::TYPE_HARVEST_REMINDER)
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->crop_plan_id', $plan->id)
                ->exists();

            if (!$existingReminder) {
                FarmerNotification::create([
                    'farmer_id' => $plan->farmer_id,
                    'type' => FarmerNotification::TYPE_HARVEST_REMINDER,
                    'title' => 'Harvest Tomorrow',
                    'message' => "Reminder: Your {$plan->crop_name} is expected to be ready for harvest tomorrow ({$plan->expected_harvest_date->format('M d, Y')}). Predicted production: {$plan->formatted_production}",
                    'icon' => 'clock',
                    'icon_color' => 'orange',
                    'link' => route('farmers.calendar'),
                    'data' => [
                        'crop_plan_id' => $plan->id,
                        'crop_name' => $plan->crop_name,
                        'predicted_production' => $plan->predicted_production,
                        'is_advance_notice' => true,
                    ],
                ]);
                $sentCount++;
                $this->info("Sent advance harvest reminder for {$plan->crop_name} to farmer {$plan->farmer->full_name}");
            }
        }

        $this->info("Done! Sent {$sentCount} reminders.");

        return Command::SUCCESS;
    }
}
